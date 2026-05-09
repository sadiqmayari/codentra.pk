<?php
/**
 * CLI test for Core\Request::clientIp().
 * Run: php tools/test-request.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../src/Core/Request.php';

$pass = 0;
$fail = 0;

function scenario(string $label, array $server, string $expected): void {
    global $pass, $fail;
    $_SERVER = array_merge(['REMOTE_ADDR' => '', 'HTTP_USER_AGENT' => 't'], $server);
    $actual  = \Core\Request::clientIp();
    $ok      = $actual === $expected;
    $mark    = $ok ? '✓' : '✗';
    if ($ok) $pass++; else $fail++;
    printf("  %s  %-60s  expected=%-20s got=%s\n", $mark, $label, $expected, $actual);
}

echo "=== Request::clientIp() scenarios ===\n";

scenario(
    'No headers, valid REMOTE_ADDR -> REMOTE_ADDR',
    ['REMOTE_ADDR' => '203.0.113.10'],
    '203.0.113.10'
);

scenario(
    'CF header + REMOTE_ADDR is CF edge -> CF-Connecting-IP',
    ['REMOTE_ADDR' => '173.245.48.5', 'HTTP_CF_CONNECTING_IP' => '8.8.8.8'],
    '8.8.8.8'
);

scenario(
    'CF header + REMOTE_ADDR is NOT CF -> ignore CF header (anti-spoof)',
    ['REMOTE_ADDR' => '203.0.113.10', 'HTTP_CF_CONNECTING_IP' => '8.8.8.8'],
    '203.0.113.10'
);

scenario(
    'CF header + CF IPv6 edge -> CF-Connecting-IP',
    ['REMOTE_ADDR' => '2606:4700:0000:0000:0000:0000:0000:0001', 'HTTP_CF_CONNECTING_IP' => '1.2.3.4'],
    '1.2.3.4'
);

scenario(
    'CF header invalid IP -> fall through to REMOTE_ADDR',
    ['REMOTE_ADDR' => '173.245.48.5', 'HTTP_CF_CONNECTING_IP' => 'not-an-ip'],
    '173.245.48.5'
);

scenario(
    'X-Forwarded-For (no CF) -> first hop',
    ['REMOTE_ADDR' => '10.0.0.1', 'HTTP_X_FORWARDED_FOR' => '203.0.113.45, 10.0.0.1'],
    '203.0.113.45'
);

scenario(
    'X-Forwarded-For + REMOTE_ADDR is CF -> ignore XFF, use CF-Connecting-IP path absent -> REMOTE_ADDR',
    ['REMOTE_ADDR' => '173.245.48.5', 'HTTP_X_FORWARDED_FOR' => '8.8.8.8'],
    '173.245.48.5'
);

scenario(
    'X-Forwarded-For invalid -> fall through to REMOTE_ADDR',
    ['REMOTE_ADDR' => '203.0.113.10', 'HTTP_X_FORWARDED_FOR' => 'garbage'],
    '203.0.113.10'
);

scenario(
    'Everything missing/invalid -> 0.0.0.0',
    ['REMOTE_ADDR' => 'not-an-ip'],
    '0.0.0.0'
);

scenario(
    'Empty $_SERVER -> 0.0.0.0',
    [],
    '0.0.0.0'
);

scenario(
    'Cloudflare IPv4 edge boundary (104.16.0.0/13 lower)',
    ['REMOTE_ADDR' => '104.16.0.1', 'HTTP_CF_CONNECTING_IP' => '198.51.100.7'],
    '198.51.100.7'
);

scenario(
    'Cloudflare IPv4 edge boundary (104.23.255.255 upper of /13)',
    ['REMOTE_ADDR' => '104.23.255.255', 'HTTP_CF_CONNECTING_IP' => '198.51.100.7'],
    '198.51.100.7'
);

scenario(
    'Just outside CF range (104.32.0.0) -> not trusted',
    ['REMOTE_ADDR' => '104.32.0.1', 'HTTP_CF_CONNECTING_IP' => '8.8.8.8'],
    '104.32.0.1'
);

echo "\n=== Cloudflare CIDR membership ===\n";
$cidr = function (string $ip, bool $expect) use (&$pass, &$fail) {
    $actual = \Core\Request::isCloudflareIp($ip);
    $ok     = $actual === $expect;
    if ($ok) $pass++; else $fail++;
    printf("  %s  %-40s  expected=%s got=%s\n",
        $ok ? '✓' : '✗', $ip,
        $expect ? 'true' : 'false',
        $actual ? 'true' : 'false'
    );
};
$cidr('173.245.48.0',  true);
$cidr('173.245.63.255',true);
$cidr('173.245.64.0',  false);
$cidr('104.16.0.0',    true);
$cidr('104.23.255.255',true);
$cidr('104.24.0.0',    true);
$cidr('104.27.255.255',true);
$cidr('104.28.0.0',    false); // 104.24.0.0/14 ends at 104.27.255.255
$cidr('2606:4700::1',  true);
$cidr('2400:cb00::1',  true);
$cidr('2001:db8::1',   false);
$cidr('8.8.8.8',       false);
$cidr('not-an-ip',     false);

echo "\n=== Result: {$pass} passed, {$fail} failed ===\n";
exit($fail === 0 ? 0 : 1);

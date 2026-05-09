<?php
/**
 * Verifies that LeadController::sendNotificationEmail() emits the four
 * required [LEAD] mail-* log lines on every invocation, regardless of
 * whether the underlying mail() call succeeds (which it won't locally
 * — Windows has no MTA — but the LOG LINES must still appear).
 *
 * Run: php tools/test-mail-block.php
 */
declare(strict_types=1);

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../src/Core/Request.php';
require_once __DIR__ . '/../src/Core/Controller.php';
require_once __DIR__ . '/../config/seo.php';
require_once __DIR__ . '/../src/Controllers/LeadController.php';

// Capture every error_log call into a buffer so the test can assert against it.
$logBuffer = [];
ini_set('error_log', $tmpLog = tempnam(sys_get_temp_dir(), 'leadlog_'));

$controller = new \Controllers\LeadController();
$ref = new ReflectionMethod($controller, 'sendNotificationEmail');
$ref->setAccessible(true);

$input = [
    'name'    => 'Test User',
    'email'   => 'test@example.com',
    'phone'   => '+92 300 0000000',
    'company' => 'Test Co',
    'service' => 'web-dev',
    'budget'  => '$15k–$50k',
    'message' => 'This is a synthetic message for the mail-block log-emission test.',
    'source'  => 'cli-test',
];

echo "=== Calling sendNotificationEmail(42, ...) ===\n";
$result = $ref->invoke($controller, 42, $input);
echo "returned: " . var_export($result, true) . "\n\n";

echo "=== Captured error_log output ===\n";
$contents = file_get_contents($tmpLog);
echo $contents;
echo "\n=== Required log lines present? ===\n";

$required = [
    'mail-block-entered id=42',
    'mail-headers-built id=42',
    'mail-call-result id=42 returned=',
    'mail-block-exited id=42 reason=',
];
$pass = 0;
$fail = 0;
foreach ($required as $needle) {
    $found = str_contains($contents, $needle);
    printf("  %s  %s\n", $found ? '✓' : '✗', $needle);
    $found ? $pass++ : $fail++;
}

@unlink($tmpLog);

echo "\n=== Result: {$pass} passed, {$fail} failed ===\n";
exit($fail === 0 ? 0 : 1);

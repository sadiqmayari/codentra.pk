<?php
declare(strict_types=1);

namespace Core;

/**
 * Request helpers — currently focused on resolving the real visitor IP
 * when Cloudflare (or another trusted proxy) sits in front of the site.
 *
 * Trust model: we trust forwarded headers ONLY when the immediate peer
 * (REMOTE_ADDR) is one of Cloudflare's published edge ranges. If anyone
 * else sends `CF-Connecting-IP` or `X-Forwarded-For` directly to the
 * origin, those headers are ignored — preventing IP spoofing for
 * rate-limit evasion or audit-log forgery.
 *
 * Falls back gracefully if Cloudflare is removed: REMOTE_ADDR is used.
 */
class Request
{
    /**
     * Cloudflare edge IP ranges (published at https://www.cloudflare.com/ips/).
     *
     * REFRESH ANNUALLY — Cloudflare adds ranges over time. If a range is
     * missing here, real visitor IPs from that range will fall through to
     * REMOTE_ADDR (the CF edge IP itself) — not catastrophic, but rate
     * limits will then apply per-edge instead of per-visitor.
     *
     * Last refreshed: 2026-05-10
     */
    private const CLOUDFLARE_RANGES = [
        // IPv4
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
        // IPv6
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ];

    /**
     * Returns the real visitor IP, or '0.0.0.0' if none can be determined.
     *
     * Resolution order:
     *   1. CF-Connecting-IP — only if REMOTE_ADDR is a Cloudflare edge IP
     *   2. X-Forwarded-For (first hop, validated) — only if NOT from Cloudflare
     *      (CF strips client-supplied XFF and rebuilds it; we don't double-trust)
     *   3. REMOTE_ADDR — the standard fallback
     *   4. '0.0.0.0' — defensive default if all of the above fail validation
     */
    public static function clientIp(): string
    {
        $remote = $_SERVER['REMOTE_ADDR'] ?? '';

        // 1. Cloudflare passes the real client IP in CF-Connecting-IP.
        //    Trust it only when the request actually came from Cloudflare.
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']) && self::isCloudflareIp($remote)) {
            $cf = trim($_SERVER['HTTP_CF_CONNECTING_IP']);
            if (filter_var($cf, FILTER_VALIDATE_IP)) {
                return $cf;
            }
        }

        // 2. Generic X-Forwarded-For — only when we're NOT behind Cloudflare.
        //    First entry is the original client; trim and validate.
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && !self::isCloudflareIp($remote)) {
            $first = trim(explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
            if (filter_var($first, FILTER_VALIDATE_IP)) {
                return $first;
            }
        }

        // 3. Standard fallback.
        if (filter_var($remote, FILTER_VALIDATE_IP)) {
            return $remote;
        }

        // 4. Defensive default.
        return '0.0.0.0';
    }

    /** True if the given IP is in any published Cloudflare range. */
    public static function isCloudflareIp(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        foreach (self::CLOUDFLARE_RANGES as $cidr) {
            if (self::ipInCidr($ip, $cidr)) {
                return true;
            }
        }
        return false;
    }

    /** Pure CIDR membership test that handles both IPv4 and IPv6. */
    private static function ipInCidr(string $ip, string $cidr): bool
    {
        if (!str_contains($cidr, '/')) {
            return false;
        }
        [$subnet, $bitsStr] = explode('/', $cidr, 2);
        $bits = (int) $bitsStr;

        // ── IPv4 ──────────────────────────────────────────────────────────
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
            && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $ipLong     = ip2long($ip);
            $subnetLong = ip2long($subnet);
            if ($ipLong === false || $subnetLong === false) return false;
            $mask = $bits === 0 ? 0 : (-1 << (32 - $bits)) & 0xFFFFFFFF;
            return ($ipLong & $mask) === ($subnetLong & $mask);
        }

        // ── IPv6 ──────────────────────────────────────────────────────────
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
            && filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $ipBin     = inet_pton($ip);
            $subnetBin = inet_pton($subnet);
            if ($ipBin === false || $subnetBin === false) return false;

            $fullBytes  = intdiv($bits, 8);
            $remainder  = $bits % 8;

            if ($fullBytes > 0 && substr($ipBin, 0, $fullBytes) !== substr($subnetBin, 0, $fullBytes)) {
                return false;
            }
            if ($remainder > 0) {
                $maskByte = (0xFF << (8 - $remainder)) & 0xFF;
                if ((ord($ipBin[$fullBytes]) & $maskByte) !== (ord($subnetBin[$fullBytes]) & $maskByte)) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }
}

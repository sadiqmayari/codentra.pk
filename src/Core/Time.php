<?php
declare(strict_types=1);

namespace Core;

class Time
{
    /**
     * Human-friendly relative time string.
     *
     *   < 60s      → "just now"
     *   < 60m      → "<N>m ago"
     *   < 24h      → "<N>h ago"
     *   < 30d      → "<N>d ago"
     *   < 1y       → "MMM D"
     *   otherwise  → "MMM D, YYYY"
     *
     * Accepts either a unix timestamp or a parseable datetime string.
     */
    public static function timeAgo(string|int|null $timestamp): string
    {
        if ($timestamp === null || $timestamp === '') return '';

        $ts = is_int($timestamp) ? $timestamp : strtotime($timestamp);
        if ($ts === false) return '';

        $now   = time();
        $delta = $now - $ts;

        if ($delta < 0) {
            // Future date — fall back to absolute formatting.
            return date('M j, Y', $ts);
        }
        if ($delta < 60)            return 'just now';
        if ($delta < 3600)          return ((int) floor($delta / 60))   . 'm ago';
        if ($delta < 86400)         return ((int) floor($delta / 3600)) . 'h ago';
        if ($delta < 86400 * 30)    return ((int) floor($delta / 86400)) . 'd ago';

        // Within the last year → no year in the label
        $oneYearAgo = strtotime('-1 year', $now);
        if ($ts >= $oneYearAgo) {
            return date('M j', $ts);
        }
        return date('M j, Y', $ts);
    }

    /** ISO-8601 string suitable for <time datetime="…"> attributes. */
    public static function iso(string|int|null $timestamp): string
    {
        if ($timestamp === null || $timestamp === '') return '';
        $ts = is_int($timestamp) ? $timestamp : strtotime($timestamp);
        return $ts === false ? '' : date('c', $ts);
    }
}

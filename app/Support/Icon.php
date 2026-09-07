<?php

namespace App\Support;

/**
 * Inline monochrome SVG icons, replacing the emoji the UI used to lean on.
 *
 * Everything is stroked in `currentColor` so an icon takes the colour of the
 * text around it; the two card icons are the exception — a yellow and a red
 * card only read as such when they keep their colour.
 */
final class Icon
{
    private const BODY = [
        'shield' => '<path d="M12 3l7 2.4v5.4c0 4.5-3 8.2-7 9.5-4-1.3-7-5-7-9.5V5.4L12 3z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
        'ball' => '<circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 6.5l3.5 2.6-1.3 4.1h-4.4L8 9.1 12 6.5z" stroke="currentColor" stroke-width="1.4" stroke-linejoin="round"/>',
        'user' => '<circle cx="12" cy="8.5" r="3.6" stroke="currentColor" stroke-width="1.6"/><path d="M5 19.5c0-3.5 3.1-5.4 7-5.4s7 1.9 7 5.4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
        'users' => '<circle cx="9" cy="9" r="3.2" stroke="currentColor" stroke-width="1.6"/><path d="M3.5 19c0-3.1 2.6-4.8 5.5-4.8s5.5 1.7 5.5 4.8" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M16 6.4a3.2 3.2 0 010 5.2M17.5 14.5c2 .6 3 2.2 3 4.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
        'boot' => '<path d="M4 7h5.5l2.5 4.5 6 1.5a3 3 0 013 3V18H4V7z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
        'trophy' => '<path d="M8 20h8M12 16.5V20M7.5 4h9v4.5a4.5 4.5 0 01-9 0V4zM7.5 6H4.5v1a3 3 0 003 3M16.5 6h3v1a3 3 0 01-3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
        'crown' => '<path d="M4 17h16M4 17l-1-8 5 3.5L12 6l4 6.5L21 9l-1 8" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
        'chart' => '<path d="M5 19V10M12 19V5M19 19v-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
        'calendar' => '<rect x="4" y="5.5" width="16" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/><path d="M4 9.5h16M8 3.5v4M16 3.5v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
        'clock' => '<circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
        'location' => '<path d="M12 21s6-5.3 6-10a6 6 0 10-12 0c0 4.7 6 10 6 10z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><circle cx="12" cy="11" r="2.2" stroke="currentColor" stroke-width="1.6"/>',
        'pin' => '<path d="M9 3h6M12 3v7M8 10h8l1.5 5H6.5L8 10zM12 15v6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
        'transfer' => '<path d="M4 8h13m0 0l-3-3m3 3l-3 3M20 16H7m0 0l3-3m-3 3l3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
        'swap' => '<path d="M4 12a8 8 0 0113.7-5.6M20 12a8 8 0 01-13.7 5.6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M17 3v3.5h-3.5M7 21v-3.5h3.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>',
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
        'arrow-left' => '<path d="M19 12H5M11 18l-6-6 6-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>',
        'check' => '<path d="M5 12.5l4.5 4.5L19 7.5" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"/>',
        'cross' => '<path d="M6.5 6.5l11 11M17.5 6.5l-11 11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>',
        'in' => '<path d="M11 8l4 4-4 4M15 12H4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M14 4h4a2 2 0 012 2v12a2 2 0 01-2 2h-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
        'out' => '<path d="M13 8l-4 4 4 4M9 12h11" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M10 4H6a2 2 0 00-2 2v12a2 2 0 002 2h4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/>',
        'gloves' => '<path d="M7 21V9a2 2 0 014 0V4.5a1.5 1.5 0 013 0V9a2 2 0 014 0v7a5 5 0 01-5 5H7z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
        'gear' => '<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.6"/><path d="M12 3v2.5M12 18.5V21M21 12h-2.5M5.5 12H3M18.4 5.6l-1.8 1.8M7.4 16.6l-1.8 1.8M18.4 18.4l-1.8-1.8M7.4 7.4L5.6 5.6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>',
        'bolt' => '<path d="M13 3L5 13h6l-1 8 8-10h-6l1-8z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>',
        'globe' => '<circle cx="12" cy="12" r="8.5" stroke="currentColor" stroke-width="1.6"/><path d="M3.5 12h17M12 3.5c2.2 2.4 3.3 5.4 3.3 8.5s-1.1 6.1-3.3 8.5c-2.2-2.4-3.3-5.4-3.3-8.5S9.8 5.9 12 3.5z" stroke="currentColor" stroke-width="1.4"/>',
        'coach' => '<circle cx="12" cy="7.5" r="3.2" stroke="currentColor" stroke-width="1.6"/><path d="M5.5 20c0-3.4 2.9-5.4 6.5-5.4s6.5 2 6.5 5.4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/><path d="M9.5 14.8L12 18l2.5-3.2" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>',
        'card-yellow' => '<rect x="7" y="3.5" width="10" height="17" rx="1.6" fill="#F4D35E"/>',
        'card-red' => '<rect x="7" y="3.5" width="10" height="17" rx="1.6" fill="#E63946"/>',
    ];

    /** Raw <svg> markup for one icon, or an empty string for an unknown name. */
    public static function svg(string $name, string $class = 'h-4 w-4'): string
    {
        $body = self::BODY[$name] ?? null;

        if ($body === null) {
            return '';
        }

        return '<svg viewBox="0 0 24 24" fill="none" class="'.e($class).'" aria-hidden="true">'.$body.'</svg>';
    }
}

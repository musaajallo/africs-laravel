<?php

namespace App\Support;

final class ProjectMeta
{
    /** @var array<string, string> */
    public const SERVICE_LINES = [
        'business' => 'Business',
        'technology' => 'Technology',
        'design' => 'Design',
    ];

    /** @var array<string, string> */
    public const STATUSES = [
        'proposed' => 'Proposed',
        'active' => 'Active',
        'on_hold' => 'On hold',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /** Statuses considered "open" — shown by default in the list. */
    public const OPEN_STATUSES = ['proposed', 'active', 'on_hold'];

    /** @return list<string> */
    public static function serviceLineKeys(): array
    {
        return array_keys(self::SERVICE_LINES);
    }

    /** @return list<string> */
    public static function statusKeys(): array
    {
        return array_keys(self::STATUSES);
    }
}

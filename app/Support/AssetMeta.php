<?php

namespace App\Support;

final class AssetMeta
{
    /** @var array<string, string> */
    public const CATEGORIES = [
        'laptop' => 'Laptop',
        'desktop' => 'Desktop',
        'monitor' => 'Monitor',
        'printer' => 'Printer',
        'phone' => 'Phone',
        'network' => 'Network equipment',
        'peripheral' => 'Peripheral',
        'other' => 'Other',
    ];

    /** @var array<string, string> */
    public const STATUSES = [
        'in_use' => 'In use',
        'spare' => 'Spare',
        'repair' => 'In repair',
        'retired' => 'Retired',
        'lost' => 'Lost / stolen',
    ];

    /** @var array<string, string> */
    public const CONDITIONS = [
        'new' => 'New',
        'good' => 'Good',
        'fair' => 'Fair',
        'poor' => 'Poor',
    ];

    /** Shown in the list by default. */
    public const ACTIVE_STATUSES = ['in_use', 'spare', 'repair'];

    /** @return list<string> */
    public static function categoryKeys(): array
    {
        return array_keys(self::CATEGORIES);
    }

    /** @return list<string> */
    public static function statusKeys(): array
    {
        return array_keys(self::STATUSES);
    }

    /** @return list<string> */
    public static function conditionKeys(): array
    {
        return array_keys(self::CONDITIONS);
    }
}

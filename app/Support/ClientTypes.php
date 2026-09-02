<?php

namespace App\Support;

/**
 * Client types and the categories available within each. Curated here for
 * now; a Settings screen may make these editable later (Phase 0d).
 */
final class ClientTypes
{
    public const TYPES = ['individual', 'organisation', 'government'];

    /** @var array<string, list<string>> */
    public const CATEGORIES = [
        'individual' => [],
        'organisation' => [
            'Private company',
            'NGO',
            'CBO',
            'Association / cooperative',
            'Academic / research',
            'Faith-based',
            'Other',
        ],
        'government' => [
            'Ministry',
            'Department / agency',
            'Local council',
            'Parastatal / SOE',
            'Project / programme',
            'Other',
        ],
    ];

    /** @return list<string> */
    public static function categoriesFor(?string $type): array
    {
        return self::CATEGORIES[$type] ?? [];
    }

    /** Every category value across all types. */
    public static function allCategories(): array
    {
        return array_values(array_unique(array_merge(...array_values(self::CATEGORIES))));
    }
}

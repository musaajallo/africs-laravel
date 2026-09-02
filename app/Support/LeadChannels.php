<?php

namespace App\Support;

/**
 * How a lead reached Africs. Stored in the `source` column.
 */
final class LeadChannels
{
    /** @var array<string, string> */
    public const LABELS = [
        'website' => 'Website enquiry',
        'referral' => 'Referral',
        'outbound' => 'We reached out',
        'event' => 'Event / networking',
        'social' => 'Social media',
        'other' => 'Other',
    ];

    public const DEFAULT = 'outbound';

    /** @return list<string> */
    public static function keys(): array
    {
        return array_keys(self::LABELS);
    }
}

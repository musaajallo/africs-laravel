<?php

namespace App\Support;

final class ProformaMeta
{
    /** @var array<string, string> */
    public const STATUSES = [
        'draft' => 'Draft',
        'sent' => 'Sent',
        'accepted' => 'Accepted',
        'declined' => 'Declined',
        'expired' => 'Expired',
        'converted' => 'Converted',
    ];

    /** Only a draft can have its details and lines edited. */
    public const EDITABLE_STATUSES = ['draft'];

    /** Shown by default in the list. */
    public const OPEN_STATUSES = ['draft', 'sent', 'accepted'];

    /** Statuses a user can set by hand from the detail page. */
    public const MANUAL_STATUSES = ['sent', 'accepted', 'declined', 'expired'];

    /** @return list<string> */
    public static function statusKeys(): array
    {
        return array_keys(self::STATUSES);
    }
}

<?php

namespace App\Support;

final class InvoiceMeta
{
    /** @var array<string, string> */
    public const STATUSES = [
        'draft' => 'Draft',
        'sent' => 'Sent',
        'partially_paid' => 'Partly paid',
        'paid' => 'Paid',
        'overdue' => 'Overdue',
        'void' => 'Void',
    ];

    /** Only a draft can have its details and lines edited. */
    public const EDITABLE_STATUSES = ['draft'];

    /** Shown by default in the list. */
    public const OPEN_STATUSES = ['draft', 'sent', 'partially_paid', 'overdue'];

    /**
     * Statuses a user can set by hand. Payment-driven values (partially_paid,
     * paid) become automatic once Phase 4 lands but are settable now.
     */
    public const MANUAL_STATUSES = ['sent', 'partially_paid', 'paid', 'overdue', 'void'];

    /** Proforma statuses from which a conversion to an invoice is allowed. */
    public const CONVERTIBLE_PROFORMA_STATUSES = ['sent', 'accepted'];

    /** @return list<string> */
    public static function statusKeys(): array
    {
        return array_keys(self::STATUSES);
    }
}

<?php

namespace App\Policies;

use App\Models\Invoice;
use App\Models\User;
use App\Support\Rbac;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Rbac::PERM_INVOICES_VIEW);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        return $user->can(Rbac::PERM_INVOICES_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Rbac::PERM_INVOICES_MANAGE);
    }

    /** Only a draft can be edited. */
    public function update(User $user, Invoice $invoice): bool
    {
        return $user->can(Rbac::PERM_INVOICES_MANAGE) && $invoice->isEditable();
    }

    public function delete(User $user, Invoice $invoice): bool
    {
        return $user->can(Rbac::PERM_INVOICES_MANAGE);
    }

    public function restore(User $user, Invoice $invoice): bool
    {
        return $user->can(Rbac::PERM_INVOICES_MANAGE);
    }

    /** Change status or send. */
    public function manage(User $user, Invoice $invoice): bool
    {
        return $user->can(Rbac::PERM_INVOICES_MANAGE);
    }
}

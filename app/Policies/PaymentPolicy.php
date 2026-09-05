<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Support\Rbac;

class PaymentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Rbac::PERM_PAYMENTS_VIEW);
    }

    public function view(User $user, Payment $payment): bool
    {
        return $user->can(Rbac::PERM_PAYMENTS_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(Rbac::PERM_PAYMENTS_MANAGE);
    }

    public function update(User $user, Payment $payment): bool
    {
        return $user->can(Rbac::PERM_PAYMENTS_MANAGE);
    }

    public function delete(User $user, Payment $payment): bool
    {
        return $user->can(Rbac::PERM_PAYMENTS_MANAGE);
    }

    public function restore(User $user, Payment $payment): bool
    {
        return $user->can(Rbac::PERM_PAYMENTS_MANAGE);
    }
}

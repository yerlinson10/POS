<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CustomerDebt;

class CustomerDebtPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('customer_debts:view');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CustomerDebt $customerDebt): bool
    {
        return $user->can('customer_debts:show');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CustomerDebt $customerDebt): bool
    {
        return $user->can('customer_debts:delete');
    }

    /**
     * Determine whether the user can add payment to the debt.
     */
    public function addPayment(User $user, CustomerDebt $customerDebt): bool
    {
        return $user->can('customer_debts:add_payment');
    }
}

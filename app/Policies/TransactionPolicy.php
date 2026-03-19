<?php

namespace App\Policies;

use App\Models\User;

class TransactionPolicy
{
    public function refund(User $user): bool
    {
        return $user->role === 'ADMIN' || $user->role === 'FINANCE';
    }
}

<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    /**
     * Find user by ID with their family member data.
     */
    public function findWithMember(int $userId): ?User
    {
        return User::with('familyMember')->find($userId);
    }
}

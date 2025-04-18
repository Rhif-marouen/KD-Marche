<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $authUser)
    {
        return $authUser->isAdmin();
    }
    public function update(User $authUser, User $targetUser)
    {
        // Autoriser uniquement la modification de son propre profil (sauf is_active)
        return $authUser->id === $targetUser->id && !request()->has('is_active');
    }

    public function delete(User $authUser, User $user)
    {
        return $authUser->isAdmin() && $authUser->id !== $user->id;
    }

}
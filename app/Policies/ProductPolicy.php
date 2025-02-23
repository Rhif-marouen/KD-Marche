<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    public function viewAny(User $user)
    {
        return $user->isAdmin() || $user->isVendor();
    }

    public function delete(User $user, Product $product)
    {
        return $user->isAdmin() || $product->user_id === $user->id;
    }
}

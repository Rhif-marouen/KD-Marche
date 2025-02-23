<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use App\Models\Order;
use App\Models\Product;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'admin']);
    }

    /**
     * Tableau de bord admin
     */
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'stats' => [
                'users' => User::count(),
                'products' => Product::count(),
                'orders' => Order::count()
            ]
        ]);
    }

    /**
     * Liste des utilisateurs avec pagination
     */
    public function getUsers(): JsonResponse
    {
        $users = User::with('subscriptions')
            ->latest()
            ->paginate(10);

        return UserResource::collection($users)->response();
    }
}
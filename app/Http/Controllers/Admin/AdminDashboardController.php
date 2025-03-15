<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function stats(): JsonResponse // Renommer index() en stats()
    {
        return response()->json([
            'users' => User::count(),
            'products' => Product::where('stock', '>', 0)->count(),
            'orders' => Order::count()
        ]);
    }

}
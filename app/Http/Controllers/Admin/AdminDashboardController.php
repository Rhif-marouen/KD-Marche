<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use App\Models\Payment;
class AdminDashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'orders' => Order::count(),
            'users' => User::count(),
            'products' => Product::count(),
            'revenue' => Payment::sum('amount')
        ]);
    }
}
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; 
class AdminDashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'orders' => Order::count(),
            'users' => User::count(),
            'products' => Product::count(),
           'revenue' => Order::where('status', 'paid')->sum('total')
        ]);
    }
 public function revenueStats()
    {
        try {
            // Revenus par mois
            $revenueMonthly = Order::where('status', 'paid')
                ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, SUM(total) as total')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get();

            // Revenus par produit (top 5) - Utilisation de order_items
            $revenueByProduct = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('orders.status', 'paid')
                ->select('products.name', DB::raw('SUM(order_items.quantity * products.price) as revenue'))
                ->groupBy('products.id', 'products.name')
                ->orderBy('revenue', 'desc')
                ->limit(5)
                ->get();

            return response()->json([
                'monthly' => $revenueMonthly,
                'by_product' => $revenueByProduct
            ]);
            
        } catch (\Exception $e) {
            // Utilisation correcte de Log via le facade
            Log::error('Error in revenueStats: ' . $e->getMessage());
            return response()->json(['error' => 'Server error'], 500);
        }
    }


public function orderStats()
{
    try {
        $total = Order::count();
        
        // Corriger l'alias et le groupBy
        $deliveryStats = Order::select(
                DB::raw('delivery_status as status'), 
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('delivery_status') // Group by original column name
            ->get()
            ->map(function ($item) use ($total) {
                $item->percentage = $total > 0 
                    ? round(($item->count / $total) * 100, 2) 
                    : 0;
                return $item;
            });

        // Définir les couleurs pour chaque statut
        $statusColors = [
            'delivered' => 'rgba(75, 192, 192, 0.7)', // Vert
            'overdue'   => 'rgba(255, 159, 64, 0.7)',  // Orange
            'pending'   => 'rgba(54, 162, 235, 0.7)',   // Bleu
            'canceled'  => 'rgba(255, 99, 132, 0.7)'    // Rouge
        ];

        // Ajouter les couleurs aux résultats
        $deliveryStats = $deliveryStats->map(function ($item) use ($statusColors) {
            $item->color = $statusColors[$item->delivery_status] ?? 'rgba(201, 203, 207, 0.7)';
            return $item;
        });

        // Évolution des commandes (30 derniers jours)
        $ordersTrend = Order::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json([
            'by_status' => $deliveryStats,
            'trend' => $ordersTrend
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error in orderStats: ' . $e->getMessage());
        return response()->json([
            'error' => 'Server error',
            'details' => $e->getMessage() // Pour le débogage
        ], 500);
    }
}
}
<?php

namespace App\Jobs;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Carbon\Carbon;

class UpdateOverdueOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $threshold = Carbon::now()->subHours(72);
        
        Order::where('delivery_status', 'pending')
            ->where('created_at', '<', $threshold)
            ->update(['delivery_status' => 'overdue']);
    }
}
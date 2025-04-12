<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderStatus;
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total', 
        'status', 
        'delivery_status', 
        'stripe_payment_id',
        'created_at',
    ];
    public function getIsOverdueAttribute()
{
    return $this->created_at->diffInHours(now()) > 72 
           && $this->delivery_status === 'pending';
}

    protected $casts = [
        'status' => OrderStatus::class // Cast correct
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
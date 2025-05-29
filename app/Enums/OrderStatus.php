<?php 

namespace App\Enums;

enum OrderStatus: string {
    case PENDING = 'pending';
    case PAID = 'paid';
    case DELIVERED = 'delivered';
    case CANCELED = 'canceled';
    case OVERDUE = 'overdue';
}
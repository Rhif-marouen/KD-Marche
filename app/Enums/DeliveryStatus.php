<?php

namespace App\Enums;

enum DeliveryStatus: string
{
   case Pending = 'pending';
    case Delivered = 'delivered';
    case Canceled = 'canceled';
    case Overdue = 'overdue';
   
    
    // Optionnel : méthode pour obtenir toutes les valeurs
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
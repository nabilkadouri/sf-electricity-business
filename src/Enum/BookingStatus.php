<?php

namespace App\Enum;

enum BookingStatus: string
{
    case PENDING = 'En attente'; 
    case CONFIRMED = 'Confirmée';
    case CANCELLED = 'Annulée';
}
<?php

namespace App\Enum;

enum ChargingStationStatus: string
{
    case PENDING = 'En attente'; 
    case CONFIRMED = 'Confirmée';
    case CANCELLED = 'Annulée';
}
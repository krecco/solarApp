<?php

namespace App\Modules\CarRentals\Enums;

enum VehicleCategory: string
{
    case ECONOMY = 'economy';
    case COMPACT = 'compact';
    case MIDSIZE = 'midsize';
    case LUXURY = 'luxury';
    case SUV = 'suv';
    case VAN = 'van';

    public function label(): string
    {
        return match($this) {
            self::ECONOMY => 'Economy',
            self::COMPACT => 'Compact',
            self::MIDSIZE => 'Midsize',
            self::LUXURY => 'Luxury',
            self::SUV => 'SUV',
            self::VAN => 'Van',
        };
    }
}

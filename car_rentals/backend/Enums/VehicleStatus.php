<?php

namespace App\Modules\CarRentals\Enums;

enum VehicleStatus: string
{
    case AVAILABLE = 'available';
    case RENTED = 'rented';
    case MAINTENANCE = 'maintenance';
    case RETIRED = 'retired';

    public function label(): string
    {
        return match($this) {
            self::AVAILABLE => 'Available',
            self::RENTED => 'Rented',
            self::MAINTENANCE => 'Under Maintenance',
            self::RETIRED => 'Retired',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::AVAILABLE => 'success',
            self::RENTED => 'warning',
            self::MAINTENANCE => 'info',
            self::RETIRED => 'error',
        };
    }
}

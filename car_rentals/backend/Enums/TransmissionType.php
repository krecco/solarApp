<?php

namespace App\Modules\CarRentals\Enums;

enum TransmissionType: string
{
    case MANUAL = 'manual';
    case AUTOMATIC = 'automatic';

    public function label(): string
    {
        return match($this) {
            self::MANUAL => 'Manual',
            self::AUTOMATIC => 'Automatic',
        };
    }
}

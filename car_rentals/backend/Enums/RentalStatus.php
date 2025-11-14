<?php

namespace App\Modules\CarRentals\Enums;

enum RentalStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case CONFIRMED = 'confirmed';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending Verification',
            self::VERIFIED => 'Verified',
            self::CONFIRMED => 'Confirmed',
            self::ACTIVE => 'Active Rental',
            self::COMPLETED => 'Completed',
            self::OVERDUE => 'Overdue',
            self::CANCELLED => 'Cancelled',
            self::REJECTED => 'Rejected',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'grey',
            self::PENDING => 'warning',
            self::VERIFIED => 'info',
            self::CONFIRMED => 'primary',
            self::ACTIVE => 'success',
            self::COMPLETED => 'success',
            self::OVERDUE => 'error',
            self::CANCELLED => 'grey',
            self::REJECTED => 'error',
        };
    }

    public function canTransitionTo(RentalStatus $newStatus): bool
    {
        $transitions = [
            self::DRAFT => [self::PENDING],
            self::PENDING => [self::VERIFIED, self::REJECTED],
            self::VERIFIED => [self::CONFIRMED, self::CANCELLED],
            self::CONFIRMED => [self::ACTIVE, self::CANCELLED],
            self::ACTIVE => [self::COMPLETED, self::OVERDUE],
            self::OVERDUE => [self::COMPLETED, self::CANCELLED],
            self::COMPLETED => [],
            self::CANCELLED => [],
            self::REJECTED => [],
        ];

        return in_array($newStatus, $transitions[$this] ?? []);
    }
}

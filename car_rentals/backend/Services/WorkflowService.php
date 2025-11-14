<?php

namespace App\Modules\CarRentals\Services;

use App\Modules\CarRentals\Enums\RentalStatus;
use App\Modules\CarRentals\Enums\VehicleStatus;
use App\Modules\CarRentals\Models\Rental;
use App\Modules\CarRentals\Notifications\RentalStatusChanged;
use Illuminate\Support\Facades\Log;

class WorkflowService
{
    /**
     * Transition rental to a new status
     */
    public function transitionRental(Rental $rental, RentalStatus $newStatus, ?int $userId = null): bool
    {
        if (!$rental->canTransitionTo($newStatus)) {
            Log::warning("Invalid status transition attempted", [
                'rental_id' => $rental->id,
                'current_status' => $rental->status->value,
                'new_status' => $newStatus->value,
            ]);
            return false;
        }

        $oldStatus = $rental->status;
        $rental->status = $newStatus;

        // Handle specific status transitions
        switch ($newStatus) {
            case RentalStatus::VERIFIED:
                $rental->verification_status = 'verified';
                $rental->verified_by = $userId;
                $rental->verified_at = now();
                break;

            case RentalStatus::REJECTED:
                $rental->verification_status = 'rejected';
                $rental->verified_by = $userId;
                $rental->verified_at = now();
                break;

            case RentalStatus::CONFIRMED:
                $rental->payment_status = 'paid';
                $rental->payment_date = now();
                break;

            case RentalStatus::ACTIVE:
                $rental->actual_pickup_date = now();
                // Update vehicle status to rented
                $rental->vehicle->status = VehicleStatus::RENTED;
                $rental->vehicle->save();
                break;

            case RentalStatus::COMPLETED:
                $rental->actual_return_date = now();
                // Update vehicle status back to available
                $rental->vehicle->status = VehicleStatus::AVAILABLE;
                $rental->vehicle->save();
                break;
        }

        $rental->save();

        // Send notifications
        $this->sendStatusNotifications($rental, $oldStatus, $newStatus);

        // Log the transition
        Log::info("Rental status transition", [
            'rental_id' => $rental->id,
            'old_status' => $oldStatus->value,
            'new_status' => $newStatus->value,
            'user_id' => $userId,
        ]);

        return true;
    }

    /**
     * Send notifications based on workflow configuration
     */
    private function sendStatusNotifications(Rental $rental, RentalStatus $oldStatus, RentalStatus $newStatus): void
    {
        // Load workflow config
        $workflowConfig = $this->getWorkflowConfig();
        $stateConfig = $workflowConfig['workflows']['rental_booking']['states'][$newStatus->value] ?? null;

        if (!$stateConfig || !isset($stateConfig['notifications']['on_enter'])) {
            return;
        }

        $notifications = $stateConfig['notifications']['on_enter'];

        // Send to customer
        if (isset($notifications['customer'])) {
            $rental->user->notify(new RentalStatusChanged($rental, $notifications['customer']));
        }

        // Send to manager
        if (isset($notifications['manager']) && $rental->vehicle->manager) {
            $rental->vehicle->manager->notify(new RentalStatusChanged($rental, $notifications['manager']));
        }
    }

    /**
     * Check for overdue rentals
     */
    public function checkOverdueRentals(): void
    {
        $overdueRentals = Rental::active()
            ->where('return_date', '<', now())
            ->get();

        foreach ($overdueRentals as $rental) {
            $this->transitionRental($rental, RentalStatus::OVERDUE);
        }
    }

    /**
     * Get workflow configuration
     */
    private function getWorkflowConfig(): array
    {
        $configPath = base_path('car_rentals/shared/config/workflow.config.json');
        if (!file_exists($configPath)) {
            return [];
        }

        return json_decode(file_get_contents($configPath), true);
    }

    /**
     * Get available extras with multilanguage support
     */
    public function getAvailableExtras(?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        return [
            [
                'id' => 'gps',
                'name' => trans('car_rentals.extras.gps.name', [], $locale),
                'description' => trans('car_rentals.extras.gps.description', [], $locale),
                'price_per_day' => 5.00,
            ],
            [
                'id' => 'child_seat',
                'name' => trans('car_rentals.extras.child_seat.name', [], $locale),
                'description' => trans('car_rentals.extras.child_seat.description', [], $locale),
                'price_per_day' => 8.00,
            ],
            [
                'id' => 'additional_driver',
                'name' => trans('car_rentals.extras.additional_driver.name', [], $locale),
                'description' => trans('car_rentals.extras.additional_driver.description', [], $locale),
                'price_per_day' => 10.00,
            ],
            [
                'id' => 'insurance_premium',
                'name' => trans('car_rentals.extras.insurance_premium.name', [], $locale),
                'description' => trans('car_rentals.extras.insurance_premium.description', [], $locale),
                'price_per_day' => 15.00,
            ],
        ];
    }
}

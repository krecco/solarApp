<?php

namespace App\Modules\CarRentals\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'vin' => $this->vin,
            'make' => $this->make,
            'model' => $this->model,
            'year' => $this->year,
            'color' => $this->color,
            'license_plate' => $this->license_plate,
            'full_name' => $this->getFullName(),

            'category' => [
                'value' => $this->category->value,
                'label' => $this->category->label(),
            ],
            'transmission' => [
                'value' => $this->transmission->value,
                'label' => $this->transmission->label(),
            ],
            'fuel_type' => [
                'value' => $this->fuel_type->value,
                'label' => $this->fuel_type->label(),
            ],

            'seats' => $this->seats,
            'doors' => $this->doors,
            'mileage' => $this->mileage,
            'features' => $this->features,

            'daily_rate' => $this->daily_rate,
            'weekly_rate' => $this->weekly_rate,
            'monthly_rate' => $this->monthly_rate,
            'security_deposit' => $this->security_deposit,

            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'location' => $this->location,
            'description' => $this->description,

            'average_rating' => $this->when(
                $this->relationLoaded('reviews'),
                fn() => round($this->getAverageRating(), 1)
            ),
            'total_reviews' => $this->when(
                $this->relationLoaded('reviews'),
                fn() => $this->getTotalReviews()
            ),

            'owner' => $this->when($this->relationLoaded('owner'), fn() => [
                'id' => $this->owner?->id,
                'name' => $this->owner?->name,
            ]),
            'manager' => $this->when($this->relationLoaded('manager'), fn() => [
                'id' => $this->manager?->id,
                'name' => $this->manager?->name,
            ]),

            'translations' => $this->multilang_data,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

<?php

namespace App\Modules\CarRentals\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rental_number' => $this->rental_number,

            'user' => $this->when($this->relationLoaded('user'), fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ]),

            'vehicle' => $this->when(
                $this->relationLoaded('vehicle'),
                fn() => new VehicleResource($this->vehicle)
            ),

            'pickup_date' => $this->pickup_date?->toISOString(),
            'return_date' => $this->return_date?->toISOString(),
            'actual_pickup_date' => $this->actual_pickup_date?->toISOString(),
            'actual_return_date' => $this->actual_return_date?->toISOString(),

            'daily_rate' => $this->daily_rate,
            'total_days' => $this->total_days,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax_amount,
            'insurance_fee' => $this->insurance_fee,
            'extras_total' => $this->extras_total,
            'total_amount' => $this->total_amount,
            'security_deposit' => $this->security_deposit,

            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'payment_date' => $this->payment_date?->toISOString(),

            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
                'color' => $this->status->color(),
            ],
            'verification_status' => $this->verification_status,

            'verified_by' => $this->when($this->relationLoaded('verifiedBy'), fn() => [
                'id' => $this->verifiedBy?->id,
                'name' => $this->verifiedBy?->name,
            ]),
            'verified_at' => $this->verified_at?->toISOString(),

            'pickup_mileage' => $this->pickup_mileage,
            'return_mileage' => $this->return_mileage,
            'mileage_limit' => $this->mileage_limit,
            'excess_mileage' => $this->excess_mileage,

            'pickup_condition' => $this->pickup_condition,
            'return_condition' => $this->return_condition,
            'damage_report' => $this->damage_report,
            'damage_cost' => $this->damage_cost,

            'document_language' => $this->document_language,
            'notes' => $this->notes,

            'extras' => $this->when($this->relationLoaded('extras'), fn() =>
                $this->extras->map(fn($extra) => [
                    'id' => $extra->id,
                    'name' => $extra->name,
                    'description' => $extra->description,
                    'quantity' => $extra->quantity,
                    'unit_price' => $extra->unit_price,
                    'total_price' => $extra->total_price,
                ])
            ),

            'is_overdue' => $this->isOverdue(),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

<?php

namespace App\Modules\CarRentals\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Customer can create rentals
    }

    public function rules(): array
    {
        return [
            'vehicle_id' => 'required|exists:vehicles,id',
            'pickup_date' => 'required|date|after:now',
            'return_date' => 'required|date|after:pickup_date',
            'daily_rate' => 'required|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'insurance_fee' => 'nullable|numeric|min:0',
            'mileage_limit' => 'nullable|numeric|min:0',
            'document_language' => 'nullable|string|in:en,de,fr,es,it',
            'notes' => 'nullable|string',
            'extras' => 'nullable|array',
            'extras.*.name' => 'required|string',
            'extras.*.quantity' => 'required|integer|min:1',
            'extras.*.unit_price' => 'required|numeric|min:0',
            'extras.*.total_price' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'pickup_date.after' => 'Pickup date must be in the future',
            'return_date.after' => 'Return date must be after pickup date',
        ];
    }
}

<?php

namespace App\Modules\CarRentals\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['manager', 'admin']);
    }

    public function rules(): array
    {
        return [
            'vin' => [
                'nullable',
                'string',
                'size:17',
                Rule::unique('vehicles', 'vin')->ignore($this->vehicle),
            ],
            'make' => 'sometimes|required|string|max:50',
            'model' => 'sometimes|required|string|max:50',
            'year' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:30',
            'license_plate' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('vehicles', 'license_plate')->ignore($this->vehicle),
            ],
            'category' => 'sometimes|required|in:economy,compact,midsize,luxury,suv,van',
            'transmission' => 'sometimes|required|in:manual,automatic',
            'fuel_type' => 'sometimes|required|in:gasoline,diesel,electric,hybrid',
            'seats' => 'sometimes|required|integer|min:2|max:12',
            'doors' => 'sometimes|required|integer|min:2|max:6',
            'mileage' => 'nullable|numeric|min:0',
            'features' => 'nullable|array',
            'daily_rate' => 'sometimes|required|numeric|min:0',
            'weekly_rate' => 'nullable|numeric|min:0',
            'monthly_rate' => 'nullable|numeric|min:0',
            'security_deposit' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:available,rented,maintenance,retired',
            'location' => 'sometimes|required|string|max:100',
            'owner_id' => 'nullable|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'translations' => 'nullable|array',
        ];
    }
}

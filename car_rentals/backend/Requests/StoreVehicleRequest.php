<?php

namespace App\Modules\CarRentals\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['manager', 'admin']);
    }

    public function rules(): array
    {
        return [
            'vin' => 'nullable|string|size:17|unique:vehicles,vin',
            'make' => 'required|string|max:50',
            'model' => 'required|string|max:50',
            'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'color' => 'nullable|string|max:30',
            'license_plate' => 'required|string|max:20|unique:vehicles,license_plate',
            'category' => 'required|in:economy,compact,midsize,luxury,suv,van',
            'transmission' => 'required|in:manual,automatic',
            'fuel_type' => 'required|in:gasoline,diesel,electric,hybrid',
            'seats' => 'required|integer|min:2|max:12',
            'doors' => 'required|integer|min:2|max:6',
            'mileage' => 'nullable|numeric|min:0',
            'features' => 'nullable|array',
            'daily_rate' => 'required|numeric|min:0',
            'weekly_rate' => 'nullable|numeric|min:0',
            'monthly_rate' => 'nullable|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
            'status' => 'nullable|in:available,rented,maintenance,retired',
            'location' => 'required|string|max:100',
            'owner_id' => 'nullable|exists:users,id',
            'manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'translations' => 'nullable|array',
        ];
    }
}

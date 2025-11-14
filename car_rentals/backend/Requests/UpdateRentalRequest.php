<?php

namespace App\Modules\CarRentals\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRentalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $rental = $this->route('rental');

        // Customer can only update their own rentals
        if ($user->hasRole('customer')) {
            return $rental->user_id === $user->id;
        }

        // Managers and admins can update any rental
        return $user->hasAnyRole(['manager', 'admin']);
    }

    public function rules(): array
    {
        return [
            'pickup_date' => 'sometimes|required|date',
            'return_date' => 'sometimes|required|date|after:pickup_date',
            'notes' => 'nullable|string',
            'payment_method' => 'nullable|string|max:50',
        ];
    }
}

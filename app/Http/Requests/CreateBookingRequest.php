<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'booking_type' => 'required|in:instant,scheduled',
            'service_id' => 'required|exists:services,id',
            'city_id' => 'required|exists:cities,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address' => 'required|string|max:500',
            'district' => 'nullable|string|max:100',
            'ward' => 'nullable|string|max:100',
            'street' => 'nullable|string|max:255',
            'scheduled_at' => 'nullable|date|after:now',
            'special_instructions' => 'nullable|string|max:1000',
        ];
    }
}
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CombatShiftFlightUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'drone_id' => 'required|exists:drones,id',
            'ammunition_id' => 'required|exists:ammunition,id',
            'coordinates' => 'required|string|max:255',
            'flight_time' => 'required|date',
            'result' => 'required|string|max:255',
            'note' => 'nullable|string',
        ];
    }
}

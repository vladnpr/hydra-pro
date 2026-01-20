<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CombatShiftFlightStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'combat_shift_id' => 'required|exists:combat_shifts,id',
            'drone_id' => 'required|exists:drones,id',
            'ammunition_id' => 'required|exists:ammunition,id',
            'coordinates' => 'required|string|max:255',
            'flight_time' => 'required|date',
            'result' => 'required|string|max:255',
            'stream' => 'nullable|string|max:255',
            'note' => 'nullable|string',
        ];
    }
}

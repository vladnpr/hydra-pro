<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AirDefenceCombatShiftUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'position_id' => 'required|exists:positions,id',
            'status' => 'required|in:opened,closed',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after_or_equal:started_at',
            'crew' => 'nullable|array',
            'crew.*.callsign' => 'required_with:crew|string|max:255',
            'crew.*.role' => 'required_with:crew|string|max:255',
            'damaged_drones' => 'nullable|array',
            'damaged_drones.*.name' => 'required_with:damaged_drones|string|max:255',
            'damaged_drones.*.quantity' => 'required_with:damaged_drones|integer|min:1',
            'damaged_coils' => 'nullable|array',
            'damaged_coils.*.name' => 'required_with:damaged_coils|string|max:255',
            'damaged_coils.*.quantity' => 'required_with:damaged_coils|integer|min:1',
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReconCombatShiftStoreRequest extends FormRequest
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
            'ammunition' => 'nullable|array',
            'ammunition.*' => 'integer|min:0',
            'crew' => 'nullable|array',
            'crew.*.callsign' => 'required_with:crew|string|max:255',
            'crew.*.role' => 'required_with:crew|string|max:255',
            'flights' => 'nullable|array',
            'flights.*.ammunition_id' => 'required_with:flights|exists:ammunition,id',
            'flights.*.coordinates' => 'required_with:flights|string|max:255',
            'flights.*.flight_time' => 'required_with:flights|date',
            'flights.*.result' => 'required_with:flights|string|max:255',
            'flights.*.detonation' => 'required_with:flights|in:так,ні,інше',
            'flights.*.stream' => 'nullable|string|max:255',
            'flights.*.note' => 'nullable|string',
            'damaged_drones' => 'nullable|array',
            'damaged_drones.*.name' => 'required_with:damaged_drones|string|max:255',
            'damaged_drones.*.quantity' => 'required_with:damaged_drones|integer|min:1',
            'damaged_coils' => 'nullable|array',
            'damaged_coils.*.name' => 'required_with:damaged_coils|string|max:255',
            'damaged_coils.*.quantity' => 'required_with:damaged_coils|integer|min:1',
            'new_recon_drones' => 'nullable|array',
            'new_recon_drones.*.name' => 'required_with:new_recon_drones|string|max:255',
            'new_recon_drones.*.serial_number' => 'nullable|string|max:255|unique:recon_drones,serial_number',
            'new_recon_drones.*.status' => 'required_with:new_recon_drones|in:active,lost,repair,non_operational',
            'new_recon_drones.*.shift_type' => 'required_with:new_recon_drones|in:day,night,both',
            'existing_recon_drones' => 'nullable|array',
            'existing_recon_drones.*.id' => 'required_with:existing_recon_drones|exists:recon_drones,id',
            'existing_recon_drones.*.status' => 'required_with:existing_recon_drones|in:active,lost,repair,non_operational',
            'existing_recon_drones.*.shift_type' => 'required_with:existing_recon_drones|in:day,night,both',
        ];
    }
}

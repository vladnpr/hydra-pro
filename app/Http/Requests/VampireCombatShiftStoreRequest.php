<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VampireCombatShiftStoreRequest extends FormRequest
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
            'crew.*.shift_type' => 'required_with:crew|in:day,night,both',
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
            'new_drones' => 'nullable|array',
            'new_drones.*.name' => 'required_with:new_drones|string|max:255',
            'new_drones.*.serial_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('vampire_drones', 'serial_number')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) {
                    if (empty($value)) return;
                    $serialNumbers = collect($this->input('new_drones'))->pluck('serial_number')->filter()->toArray();
                    $counts = array_count_values($serialNumbers);
                    if (($counts[$value] ?? 0) > 1) {
                        $fail("Серійний номер {$value} повторюється у вашому запиті.");
                    }
                }
            ],
            'new_drones.*.status' => 'required_with:new_drones|in:active,lost,repair,non_operational',
            'new_drones.*.lost_at' => 'nullable|required_if:new_drones.*.status,lost|date',
            'new_drones.*.shift_type' => 'required_with:new_drones|in:day,night,both',
            'existing_drones' => 'nullable|array',
            'existing_drones.*.id' => 'required_with:existing_drones|exists:vampire_drones,id',
            'existing_drones.*.status' => 'required_with:existing_drones|in:active,lost,repair,non_operational',
            'existing_drones.*.lost_at' => 'nullable|required_if:existing_drones.*.status,lost|date',
            'existing_drones.*.shift_type' => 'required_with:existing_drones|in:day,night,both',
        ];
    }

    public function messages(): array
    {
        return [
            'new_drones.*.serial_number.unique' => 'Дрон з серійним номером :input вже існує в базі.',
        ];
    }
}

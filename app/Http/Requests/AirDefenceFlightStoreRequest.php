<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AirDefenceFlightStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position_id' => 'required|exists:positions,id',
            'air_defence_drone_id' => 'required|exists:air_defence_drones,id',
            'air_defence_ammunition_id' => 'required|exists:air_defence_ammunition,id',
            'coordinates' => 'nullable|string',
            'start_time' => 'nullable|date',
            'end_time' => 'nullable|date',
            'stream' => 'nullable|string',
            'result' => 'nullable|string',
            'detonation' => 'boolean',
            'comment' => 'nullable|string',
        ];
    }
}

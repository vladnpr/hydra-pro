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
            'coordinates' => 'nullable|string|max:255',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after_or_equal:start_time',
            'stream' => 'nullable|string|max:255',
            'stream_switch' => 'nullable|boolean',
            'result' => 'required|string|in:влучання,в районі цілі,втрата борта,борт повернувся',
            'detonation' => 'required',
            'comment' => 'nullable|string',
            'video' => 'nullable|file|mimes:mp4,mov,avi,wmv|max:76800',
        ];
    }
}

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
            'detonation' => 'required|in:так,ні,інше',
            'stream' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'video' => 'nullable|file|mimetypes:video/mp4,video/mpeg,video/quicktime,video/x-msvideo,video/x-flv,video/webm|max:76800',
        ];
    }
}

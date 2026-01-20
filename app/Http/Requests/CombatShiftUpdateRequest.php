<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CombatShiftUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position_id' => 'required|exists:positions,id',
            'status' => 'required|in:opened,closed',
            'started_at' => 'required|date',
            'ended_at' => 'nullable|date|after_or_equal:started_at',
            'drones' => 'nullable|array',
            'drones.*' => 'integer|min:0',
            'ammunition' => 'nullable|array',
            'ammunition.*' => 'integer|min:0',
        ];
    }
}

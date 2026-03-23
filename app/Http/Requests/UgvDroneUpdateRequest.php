<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UgvDroneUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('ugv_drones', 'serial_number')
                    ->whereNull('deleted_at')
                    ->ignore($this->route('drone')),
            ],
            'status' => 'required|in:active,lost,repair,non_operational',
            'position_id' => 'required|exists:positions,id',
            'shift_type' => 'required|in:day,night,both',
            'lost_at' => 'nullable|date',
        ];
    }
}

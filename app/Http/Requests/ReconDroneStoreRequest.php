<?php

namespace App\Http\Requests;

use App\Enums\PositionTypesEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReconDroneStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:255|unique:recon_drones,serial_number',
            'status' => ['required', Rule::in(['active', 'lost', 'repair', 'non_operational'])],
            'position_id' => [
                'required',
                Rule::exists('positions', 'id')->where(function ($query) {
                    $query->where('type', PositionTypesEnum::RECON->value);
                }),
            ],
            'shift_type' => ['required', Rule::in(['day', 'night', 'both'])],
        ];
    }

    public function messages(): array
    {
        return [
            'position_id.exists' => 'Обрана позиція повинна мати тип Recon.',
        ];
    }
}

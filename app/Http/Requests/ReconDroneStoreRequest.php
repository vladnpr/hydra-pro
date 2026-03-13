<?php

namespace App\Http\Requests;

use App\Enums\PositionTypesEnum;
use App\Enums\ShiftTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

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
            'serial_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('recon_drones', 'serial_number')->whereNull('deleted_at')
            ],
            'status' => ['required', Rule::in(['active', 'lost', 'repair', 'non_operational'])],
            'shift_type' => ['required', new Enum(ShiftTypeEnum::class)],
            'position_id' => [
                'required',
                Rule::exists('positions', 'id')->where(function ($query) {
                    $query->where('type', PositionTypesEnum::RECON->value);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'position_id.exists' => 'Обрана позиція повинна мати тип Recon.',
            'serial_number.unique' => 'Дрон з таким серійним номером вже існує.',
        ];
    }
}

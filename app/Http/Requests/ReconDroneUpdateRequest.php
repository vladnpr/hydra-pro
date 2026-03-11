<?php

namespace App\Http\Requests;

use App\Enums\PositionTypesEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReconDroneUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('drone');

        return [
            'name' => 'required|string|max:255',
            'serial_number' => ['nullable', 'string', 'max:255', Rule::unique('recon_drones', 'serial_number')->ignore($id)],
            'status' => ['required', Rule::in(['active', 'lost', 'repair', 'non_operational'])],
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
        ];
    }
}

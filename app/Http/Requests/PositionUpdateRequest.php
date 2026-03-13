<?php

namespace App\Http\Requests;

use App\Enums\PositionTypesEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PositionUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|boolean',
            'type' => ['required', new Enum(PositionTypesEnum::class)],
        ];
    }
}

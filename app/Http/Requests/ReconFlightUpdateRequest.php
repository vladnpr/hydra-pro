<?php

namespace App\Http\Requests;

use App\Enums\ReconMissionResultsEnum;
use App\Enums\ReconMissionTypesEnum;
use App\Enums\ShiftTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ReconFlightUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recon_drone_id' => 'required|exists:recon_drones,id',
            'ammunition' => [
                'nullable',
                'array',
                'max:4',
                function ($attribute, $value, $fail) {
                    $total = 0;
                    foreach ($value as $item) {
                        if (!empty($item['id']) && !empty($item['quantity'])) {
                            $total += (int) $item['quantity'];
                        }
                    }
                    if ($total > 4) {
                        $fail('Загальна кількість боєприпасів за один політ не може перевищувати 4.');
                    }
                },
            ],
            'ammunition.*.id' => 'nullable|required_with:ammunition.*.quantity|exists:ammunition,id',
            'ammunition.*.quantity' => 'nullable|required_with:ammunition.*.id|integer|min:1',
            'mission_type' => ['required', new Enum(ReconMissionTypesEnum::class)],
            'coordinates' => [
                'required_unless:mission_type,' . ReconMissionTypesEnum::OTHER->value,
                'nullable',
                'string',
                'max:255'
            ],
            'target_name' => [
                'required_if:mission_type,' . ReconMissionTypesEnum::OTHER->value,
                'nullable',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if (empty($value)) return;

                    // 1. Формат 36U UA 24232 91610 (MGRS-подібний)
                    if (preg_match('/^\d{2}[A-Z]\s+[A-Z]{2}\s+\d{5}\s+\d{5}$/i', $value)) {
                        $fail('Поле "Назва цілі" не може містити координати у форматі MGRS.');
                    }
                    // 2. Формат 50.5000, 31.0500 (Decimal Degrees)
                    if (preg_match('/^-?\d{1,3}\.\d+,\s*-?\d{1,3}\.\d+$/', $value)) {
                        $fail('Поле "Назва цілі" не може містити координати у десятковому форматі.');
                    }
                    // 3. Формат 50°30′00″N 31°03′00″E (DMS)
                    if (preg_match('/\d{1,3}°\d{1,2}′\d{1,2}″[NSEW]/i', $value)) {
                        $fail('Поле "Назва цілі" не може містити координати у форматі градусів/мінут/секунд.');
                    }
                },
            ],
            'flight_time' => 'required|date',
            'landing_time' => 'nullable|date|after_or_equal:flight_time',
            'result' => ['required', new Enum(ReconMissionResultsEnum::class)],
            'shift_type' => ['required', new Enum(ShiftTypeEnum::class)],
            'stream_status' => 'boolean',
            'video' => 'nullable|file|mimetypes:video/mp4,video/mpeg,video/quicktime,video/x-msvideo,video/x-flv,video/webm|max:76800',
            'description' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'ammunition.max' => 'Ви можете додати не більше :max боєприпасів за один політ',
            'ammunition.*.id.required_with' => 'Будь ласка, оберіть боєприпас або видаліть поле кількості',
            'ammunition.*.quantity.required_with' => 'Будь ласка, вкажіть кількість для обраного боєприпасу',
        ];
    }
}

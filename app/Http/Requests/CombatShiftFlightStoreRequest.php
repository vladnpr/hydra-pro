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
            'ammunition_id' => 'required_unless:mission,logistics|nullable|exists:ammunition,id',
            'mission' => 'required|in:strike,patrol,logistics',
            'coordinates' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    if ($this->mission === 'logistics') {
                        $patterns = [
                            '/\d{1,2}[A-Z]\s+[A-Z]{2}\s+\d+\s+\d+/', // UTM/MGRS
                            '/\d+\.\d+,\s*\d+\.\d+/', // Decimal coordinates
                            '/\d+°\d+[\'"]?\d+\.?\d*["\']?[NS]\s+\d+°\d+[\'"]?\d+\.?\d*["\']?[EW]/u', // Degrees
                            '/Zone\s+\d+[A-Z]?,\s*\d+\s*mE,\s*\d+\s*mN/i', // Zone
                        ];

                        foreach ($patterns as $pattern) {
                            if (preg_match($pattern, $value)) {
                                $fail('Поле не може містити координати для місії "Логістика".');
                                break;
                            }
                        }
                    }
                },
            ],
            'flight_time' => 'required|date',
            'result' => 'required|string|in:влучання,удар в районі цілі,втрата борту,відпрацювали,відпрацювали (витрата борту),відпрацювали (повернули борт)',
            'detonation' => 'required_unless:mission,logistics|nullable|in:так,ні,інше',
            'stream' => 'nullable|string|max:255',
            'note' => 'nullable|string',
            'video' => 'nullable|file|mimetypes:video/mp4,video/mpeg,video/quicktime,video/x-msvideo,video/x-flv,video/webm|max:76800',
        ];
    }
}

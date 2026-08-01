<?php

namespace Database\Seeders;

use App\Models\AirDefenceAmmunition;
use App\Models\AirDefenceDrone;
use App\Models\AirDefenceFlight;
use App\Models\Position;
use Illuminate\Database\Seeder;

class AirDefenceStatisticsSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Створюємо позицію для ППО, якщо її немає
        $position = Position::firstOrCreate(
            ['name' => 'ППО Головна'],
            ['type' => 'air_defence', 'status' => true]
        );

        // 2. Створюємо дрони
        $generalChereshnia = AirDefenceDrone::firstOrCreate(
            ['name' => 'Генерал черешня AIR'],
            ['model' => 'General Chereshnia', 'status' => 'active']
        );

        $sting = AirDefenceDrone::firstOrCreate(
            ['name' => 'Sting'],
            ['model' => 'Sting', 'status' => 'active']
        );

        // 3. Створюємо боєприпас (заглушка)
        $ammunition = AirDefenceAmmunition::firstOrCreate(
            ['name' => 'Стандартний БК ППО'],
            ['type' => 'стандарт', 'status' => 'active']
        );

        // Дані з запиту (Цільові показники):
        // Вильотів - 956
        // Втрати - 16 шт Генерал черешня АІR
        // Знищення - 2 шт (влучання)
        // Втрати стінга - 6 шт

        $targetTotalFlights = 956;
        $targetChereshniaLosses = 16;
        $targetStingLosses = 6;
        $targetDestructions = 2;

        // Поточні показники в базі
        $currentTotalFlights = AirDefenceFlight::count();
        $currentChereshniaLosses = AirDefenceFlight::where('result', 'втрата борта')
            ->where('air_defence_drone_id', $generalChereshnia->id)
            ->count();
        $currentStingLosses = AirDefenceFlight::where('result', 'втрата борта')
            ->where('air_defence_drone_id', $sting->id)
            ->count();
        $currentDestructions = AirDefenceFlight::where('result', 'влучання')->count();

        // Розраховуємо скільки треба додати
        $addChereshniaLosses = max(0, $targetChereshniaLosses - $currentChereshniaLosses);
        $addStingLosses = max(0, $targetStingLosses - $currentStingLosses);
        $addDestructions = max(0, $targetDestructions - $currentDestructions);

        // Втрати Генерал черешня
        for ($i = 0; $i < $addChereshniaLosses; $i++) {
            AirDefenceFlight::create([
                'position_id' => $position->id,
                'air_defence_drone_id' => $generalChereshnia->id,
                'air_defence_ammunition_id' => $ammunition->id,
                'result' => 'втрата борта',
                'comment' => 'Автоматичний seed: Втрата Генерал черешня AIR',
            ]);
        }

        // Втрати Sting
        for ($i = 0; $i < $addStingLosses; $i++) {
            AirDefenceFlight::create([
                'position_id' => $position->id,
                'air_defence_drone_id' => $sting->id,
                'air_defence_ammunition_id' => $ammunition->id,
                'result' => 'втрата борта',
                'comment' => 'Автоматичний seed: Втрата Sting',
            ]);
        }

        // Знищення (влучання)
        for ($i = 0; $i < $addDestructions; $i++) {
            AirDefenceFlight::create([
                'position_id' => $position->id,
                'air_defence_drone_id' => $generalChereshnia->id,
                'air_defence_ammunition_id' => $ammunition->id,
                'result' => 'влучання',
                'comment' => 'Автоматичний seed: Знищення цілі',
            ]);
        }

        // Перераховуємо загальну кількість після додавання специфічних результатів
        $currentTotalAfterSpecs = AirDefenceFlight::count();
        $addOtherFlights = max(0, $targetTotalFlights - $currentTotalAfterSpecs);

        // Інші вильоти (промахи), щоб догнати до цілі
        for ($i = 0; $i < $addOtherFlights; $i++) {
            AirDefenceFlight::create([
                'position_id' => $position->id,
                'air_defence_drone_id' => $generalChereshnia->id,
                'air_defence_ammunition_id' => $ammunition->id,
                'result' => 'промах',
                'comment' => 'Автоматичний seed: Плановий виліт',
            ]);
        }
    }
}

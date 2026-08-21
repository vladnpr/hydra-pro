<?php

namespace Tests\Feature;

use App\Models\CombatShift;
use App\Models\CombatShiftFlight;
use App\Models\Position;
use App\Models\ReconFlight;
use App\Models\User;
use App\Services\CombatShiftsAdminService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    public function test_dashboard_renders_with_period_filter_elements(): void
    {
        $response = $this->actingAs($this->admin)->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Період:');
        $response->assertSee('День');
        $response->assertSee('Тиждень');
        $response->assertSee('Місяць');
        $response->assertSee('Від дати');
    }

    public function test_service_resolves_date_ranges_correctly(): void
    {
        /** @var CombatShiftsAdminService $service */
        $service = app(CombatShiftsAdminService::class);

        // Day
        [$fromDay, $toDay] = $service->resolveDateRange('day');
        $this->assertNotNull($fromDay);
        $this->assertNotNull($toDay);
        $this->assertEquals(now()->startOfDay()->toDateTimeString(), $fromDay->toDateTimeString());

        // Ukrainian 'день'
        [$fromUaDay] = $service->resolveDateRange('день');
        $this->assertEquals(now()->startOfDay()->toDateTimeString(), $fromUaDay->toDateTimeString());

        // Week
        [$fromWeek] = $service->resolveDateRange('week');
        $this->assertEquals(now()->subDays(7)->startOfDay()->toDateTimeString(), $fromWeek->toDateTimeString());

        // Month
        [$fromMonth] = $service->resolveDateRange('month');
        $this->assertEquals(now()->subDays(30)->startOfDay()->toDateTimeString(), $fromMonth->toDateTimeString());

        // Specific date
        [$fromDateFrom] = $service->resolveDateRange(null, '2026-08-01');
        $this->assertEquals(Carbon::parse('2026-08-01')->startOfDay()->toDateTimeString(), $fromDateFrom->toDateTimeString());

        // All / Null
        [$fromAll, $toAll] = $service->resolveDateRange('all');
        $this->assertNull($fromAll);
        $this->assertNull($toAll);
    }

    public function test_stats_are_properly_filtered_by_period(): void
    {
        $position = Position::create([
            'name' => 'Позиція 1',
            'type' => 'fpv',
            'status' => true,
        ]);
        $drone = \App\Models\Drone::create([
            'name' => 'Дрон 1',
            'model' => '7 дюймів',
            'status' => 1,
        ]);
        $shift = CombatShift::create([
            'position_id' => $position->id,
            'status' => 'closed',
            'started_at' => now()->subDays(10),
            'ended_at' => now()->subDays(9),
        ]);

        // Flight 1: today
        CombatShiftFlight::create([
            'combat_shift_id' => $shift->id,
            'drone_id' => $drone->id,
            'coordinates' => '48.123, 37.123',
            'flight_time' => now(),
            'result' => 'влучання',
            'mission' => 'strike',
        ]);

        // Flight 2: 3 days ago
        CombatShiftFlight::create([
            'combat_shift_id' => $shift->id,
            'drone_id' => $drone->id,
            'coordinates' => '48.123, 37.123',
            'flight_time' => now()->subDays(3),
            'result' => 'влучання',
            'mission' => 'strike',
        ]);

        // Flight 3: 15 days ago
        CombatShiftFlight::create([
            'combat_shift_id' => $shift->id,
            'drone_id' => $drone->id,
            'coordinates' => '48.123, 37.123',
            'flight_time' => now()->subDays(15),
            'result' => 'влучання',
            'mission' => 'strike',
        ]);

        /** @var CombatShiftsAdminService $service */
        $service = app(CombatShiftsAdminService::class);

        // All time
        $allStats = $service->getDashboardStats();
        $this->assertEquals(3, $allStats['total']['fpv']['total_flights']);

        // Day (today only)
        $dayStats = $service->getDashboardStats('day');
        $this->assertEquals(1, $dayStats['total']['fpv']['total_flights']);

        // Week (last 7 days -> today + 3 days ago)
        $weekStats = $service->getDashboardStats('week');
        $this->assertEquals(2, $weekStats['total']['fpv']['total_flights']);

        // Month (last 30 days -> all 3 flights)
        $monthStats = $service->getDashboardStats('month');
        $this->assertEquals(3, $monthStats['total']['fpv']['total_flights']);

        // From specific date (e.g. 5 days ago)
        $customStats = $service->getDashboardStats(null, now()->subDays(5)->format('Y-m-d'));
        $this->assertEquals(2, $customStats['total']['fpv']['total_flights']);

        // Check positions stats filtering
        $this->assertEquals(1, $dayStats['positions'][$position->id]['fpv']['total_flights']);
        $this->assertEquals(2, $weekStats['positions'][$position->id]['fpv']['total_flights']);
        $this->assertEquals(3, $monthStats['positions'][$position->id]['fpv']['total_flights']);
    }

    public function test_dashboard_route_handles_filter_parameters(): void
    {
        $responseDay = $this->actingAs($this->admin)->get(route('home', ['period' => 'day']));
        $responseDay->assertStatus(200);

        $responseWeek = $this->actingAs($this->admin)->get(route('home', ['period' => 'week']));
        $responseWeek->assertStatus(200);

        $responseMonth = $this->actingAs($this->admin)->get(route('home', ['period' => 'month']));
        $responseMonth->assertStatus(200);

        $responseDate = $this->actingAs($this->admin)->get(route('home', ['date_from' => '2026-08-01']));
        $responseDate->assertStatus(200);
    }
}

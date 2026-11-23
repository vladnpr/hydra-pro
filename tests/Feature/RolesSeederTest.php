<?php

namespace Tests\Feature;

use Database\Seeders\RolesSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RolesSeederTest extends TestCase
{
    public function test_it_seeds_all_account_roles_without_duplicates(): void
    {
        $migration = require database_path('migrations/2026_11_23_144051_create_roles_table.php');
        $migration->up();

        $seeder = new RolesSeeder();
        $seeder->run();
        $seeder->run();

        $roles = DB::table('roles')->orderBy('name')->pluck('name')->all();

        $this->assertSame(['admin', 'air_defence', 'guest', 'manager', 'recon', 'ugv', 'user', 'vampire'], $roles);
        $this->assertSame(0, DB::table('roles')->where('description', '')->count());
    }
}
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin' => 'Administrator',
            'manager' => 'Manager',
            'user' => 'User',
            'guest' => 'Guest',
            'recon' => 'Reconnaissance',
            'vampire' => 'Vampire',
            'ugv' => 'UGV',
            'air_defence' => 'Air defence',
        ];

        foreach ($roles as $name => $description) {
            DB::table('roles')->updateOrInsert(['name' => $name], ['description' => $description]);
        }
    }
}
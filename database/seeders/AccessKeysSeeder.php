<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccessKeysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $keys = [
            'manage-air-defence' => 'Manage air defence',
            'manage-air-defence-drones' => 'Manage air defence drones',
            'manage-air-defence-ammunition' => 'Manage air defence ammunition',
            'manage-vampire' => 'Manage vampire operations',
            'manage-ugv' => 'Manage UGV operations',
            'manage-ugv-drones' => 'Manage UGV drones',
            'manage-ugv-ammunition' => 'Manage UGV ammunition',
            'manage-recon' => 'Manage reconnaissance',
            'manage-vampire-drones' => 'Manage vampire drones',
            'manage-vampire-ammunition' => 'Manage vampire ammunition',
            'manage-recon-ammunition' => 'Manage reconnaissance ammunition',
            'manage-recon-drones' => 'Manage reconnaissance drones',
            'manage-users' => 'Manage users',
            'manage-positions' => 'Manage positions',
            'manage-fpv-ammunition' => 'Manage FPV ammunition',
            'manage-fpv-drones' => 'Manage FPV drones',
            'view-reports' => 'View reports',
            'view-duty-reports' => 'View duty reports',
            'view-dashboard-stats' => 'View dashboard statistics',
            'access-combat' => 'Access combat',
            'manage-combat' => 'Manage combat',
        ];

        foreach ($keys as $key => $description) {
            DB::table('access_keys')->updateOrInsert(['key' => $key], ['description' => $description]);
        }

    }
}

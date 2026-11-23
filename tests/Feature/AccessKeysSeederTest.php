<?php

namespace Tests\Feature;

use Database\Seeders\AccessKeysSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AccessKeysSeederTest extends TestCase
{
    public function test_it_seeds_every_gate_without_duplicates(): void
    {
        Schema::create('access_keys', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('description');
        });

        $seeder = new AccessKeysSeeder();
        $seeder->run();
        $seeder->run();

        $keys = DB::table('access_keys')->pluck('key')->all();
        $gates = array_keys(Gate::abilities());
        sort($keys);
        sort($gates);

        $this->assertSame($gates, $keys);
        $this->assertSame(0, DB::table('access_keys')->where('description', '')->count());
    }
}
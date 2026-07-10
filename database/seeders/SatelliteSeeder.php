<?php

namespace Database\Seeders;

use App\Models\Satellite;
use Illuminate\Database\Seeder;

class SatelliteSeeder extends Seeder
{
    public function run(): void
    {
        $satellites = [
            ['name' => 'Arcades', 'slug' => 'arcades'],
            ['name' => 'Avondale', 'slug' => 'avondale'],
            ['name' => 'Chamba Valley', 'slug' => 'chamba-valley'],
            ['name' => 'Woodies', 'slug' => 'woodies'],
            ['name' => 'North Side', 'slug' => 'north-side'],
            ['name' => 'South Side', 'slug' => 'south-side'],
        ];

        foreach ($satellites as $satellite) {
            Satellite::query()->updateOrCreate(
                ['slug' => $satellite['slug']],
                [
                    'name' => $satellite['name'],
                    'town' => 'Lusaka',
                    'is_active' => true,
                ]
            );
        }
    }
}

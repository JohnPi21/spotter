<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TestCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            MuscleGroupsSeeder::class,
            ExercisesSeeder::class,
            RolesAndPermissionsSeeder::class,
        ]);
    }
}

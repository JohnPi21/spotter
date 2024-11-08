<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MuscleGroupsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $muscleGroups = [
            ['id' => 1, 'name' => 'Chest'],         // muscleGroupId 1
            ['id' => 2, 'name' => 'Back'],          // muscleGroupId 2
            ['id' => 3, 'name' => 'Triceps'],       // muscleGroupId 3
            ['id' => 4, 'name' => 'Biceps'],        // muscleGroupId 4
            ['id' => 5, 'name' => 'Shoulders'],     // muscleGroupId 5
            ['id' => 6, 'name' => 'Legs'],          // muscleGroupId 6
            ['id' => 7, 'name' => 'Glutes'],        // muscleGroupId 7
            ['id' => 8, 'name' => 'Calves'],        // muscleGroupId 8
            ['id' => 11, 'name' => 'Forearms'],     // muscleGroupId 11
            ['id' => 12, 'name' => 'Abs']           // muscleGroupId 12
        ];

        DB::table('muscle_groups')->insert($muscleGroups);
    }
}

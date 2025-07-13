<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExercisesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $exercises_file = File::get(database_path('seeders/exercises.json'));

        $exercises = json_decode($exercises_file, true);

        $payload = [];

        foreach ($exercises as $exercise) {
            $payload[] = [
                'name' => $exercise['name'],
                'muscle_group_id' => $exercise['muscleGroupId'],
                'exercise_type' => $exercise['exerciseType'],
                'youtube_id' => $exercise['youtubeId'],
            ];
        };

        DB::table('exercises')->insert($payload);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ExercisesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $exercises = [];

        foreach(Storage::json('exercises.json') as $exercise){
            $exercises[] = [
                'name' => $exercise['name'],
                'muscle_group_id' => $exercise['muscleGroupId'],
                'exercise_type' => $exercise['exerciseType'],
                'youtube_id' => $exercise['youtubeId'],
            ];
        };

        DB::table('exercises')->insert($exercises);
            
    }
}

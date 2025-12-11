<?php

namespace App\Ai\Agents;

use App\Contracts\AiClient;
use App\Data\Mesocycle\CreateAiMesocycleData;
use App\Enums\UnitsOfMeasure;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Services\PrismAiClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\EnumSchema;

class MesocycleAgent
{
    // For now we will use PRISM CLASSES to build this schema
    public ObjectSchema $schema;
    public string $systemPrompt;
    public string $prompt;

    public function __construct(public AiClient $aiClient)
    {
        // --------------------
        // ExerciseTemplateData
        // --------------------
        $exerciseSchema = new ObjectSchema(
            name: 'exercise',
            description: 'Exercise id',
            properties: [
                // new NumberSchema(
                //     name: 'muscleGroup',
                //     description: 'A muscle group ID from the provided muscle groups list',
                //     minimum: 1,
                // ),
                new NumberSchema(
                    name: 'exerciseID',
                    description: 'An exercise ID from the provided exercises list',
                    minimum: 1,
                ),
            ],
            requiredFields: ['exerciseID'],
        );

        // -------------
        // DayTemplateData
        // -------------
        $daySchema = new ObjectSchema(
            name: 'day',
            description: 'A day containing label and exercises',
            properties: [
                new StringSchema(
                    name: 'label',
                    description: 'Label of the day (e.g. Push, Pull, Legs, Day 1, Day 2)',
                ),
                // here is the exercises array
                new ArraySchema(
                    name: 'exercises',
                    description: 'A list of exercises for that day',
                    items: $exerciseSchema,
                    minItems: 1,
                ),
            ],
            requiredFields: ['label', 'exercises'],
        );

        // -----------------
        // CreateMesocycleData
        // -----------------
        $this->schema = new ObjectSchema(
            name: 'mesocycle',
            description: 'Mesocycle structure',
            properties: [
                new StringSchema(
                    name: 'name',
                    description: 'Mesocycle name',
                ),

                new EnumSchema(
                    name: 'unit',
                    description: 'Unit of measure for progression',
                    options: array_map(
                        fn(UnitsOfMeasure $unit) => $unit->value,
                        UnitsOfMeasure::cases(),
                    ),
                ),

                new NumberSchema(
                    name: 'weeksDuration',
                    description: 'Number of weeks',
                    minimum: 1,
                    maximum: 12,
                ),

                new ArraySchema(
                    name: 'days',
                    description: 'Workout days containing exercises and muscle groups',
                    items: $daySchema,
                    minItems: 1,
                    maxItems: 7,
                ),
            ],
            requiredFields: ['name', 'unit', 'weeksDuration', 'days'],
        );

        $this->systemPrompt = "
            You are a coach for Spotacus.app. Provide a mesocycle structure based on the schema attached and the user personal preferences.
            You can also find the exercises supported by the app and the msucle groups. 'm' field from exercises references the id from muscle groups.
        ";
    }


    public function generate(CreateAiMesocycleData $dto)
    {
        die;
        // DTO with user preferences, EXERCISES + Muscle Groups, SYSTEM PROMPT, SCHEMA
        $this->preparePrompt();
        $response = $this->aiClient->structured('Generate a mesocycle based on the schema', 'You are a coach for Spotacus.app', $this->schema);
    }

    public function preparePrompt(CreateAiMesocycleData $dto): string
    {
        $data = $this->prepareExercises();

        $exercisesJson    = json_encode($data['exercises'], JSON_UNESCAPED_UNICODE);
        $muscleGroupsJson = json_encode($data['muscleGroups'], JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
# OBJECTIVE
Generate ONE "mesocycle" JSON object that conforms to the attached JSON schema.
The object will be used directly by the Spotacus app.

# INPUT

## UserPreferences
{
  "name": "{$dto->name}",
  "unit": "{$dto->unit->value}",
  "weeksDuration": {$dto->weeksDuration},
  "daysPerWeek": {$dto->daysPerWeek},
  "sessionDuration": {$dto->sessionDuration},
  "primaryGoal": "{$dto->primaryGoal->value}",
  "splitPreference": "{$dto->splitPreference->value}",
  "experienceLevel": "{$dto->experienceLevel->value}"
}

## Exercises
Array of all available exercises (app defaults + user custom):
- "i": exercise ID (must be used as "exerciseID" in the output)
- "n": name
- "m": muscle group ID

EXERCISES = {$exercisesJson}

## MuscleGroups
Array of all muscle groups:
- "i": muscle group ID
- "n": muscle group name

MUSCLE_GROUPS = {$muscleGroupsJson}

# GENERATION RULES

1. Schema compliance
   - The output MUST be valid JSON.
   - The output MUST match the "mesocycle" schema exactly.
   - Do NOT add extra fields.

2. Days
   - "days" array MUST contain exactly {$dto->daysPerWeek} elements.
   - Each day:
     - "label" should reflect the splitPreference (e.g. "Upper 1", "Lower 1", "Push", "Pull", "Legs", "Full Body").
     - "exercises" array length: 4–8 exercises, appropriate for ~{$dto->sessionDuration} minutes.

3. Exercises
   - Use ONLY exercise IDs from EXERCISES.
   - For each exercise in a day, output:
     { "exerciseID": <value of "i" from EXERCISES> }
   - Avoid duplicating the same exercise too many times across the week unless it is a core movement.

4. Programming logic
   - Primary goal = "{$dto->primaryGoal->value}":
     - Prioritize exercise selection and distribution that supports this goal (e.g. hypertrophy → enough volume per muscle group).
   - Experience level = "{$dto->experienceLevel->value}":
     - Beginner: simpler exercises, fewer variations.
     - Intermediate/advanced: you may use more variation.

5. Muscle group balance
   - Use the "m" field on exercises and MUSCLE_GROUPS to:
     - Ensure that major muscle groups are trained with appropriate frequency.
     - Avoid overloading the same muscle group on consecutive days.

# OUTPUT

- Return ONLY the "mesocycle" JSON object (no markdown, no explanation).
- All IDs must be valid and correspond to EXERCISES / MUSCLE_GROUPS.
PROMPT;
    }

    public function prepareExercises(): array
    {
        $exercises = Cache::rememberForever('ai_exercises', function () {
            return Exercise::select(['id', 'name', 'muscle_group_id'])->get()->map(function ($ex) {
                return [
                    'i' => $ex->id,
                    'n' => $ex->name,
                    'm' => $ex->muscle_group_id
                ];
            })->toArray();
        });

        $muscleGroups = Cache::rememberForever('ai_muscle_groups', function () {
            return MuscleGroup::select(['id', 'name'])->get()->map(fn($mg) => [
                'i' => $mg->id,
                'n' => $mg->name
            ])->toArray();
        });

        // User custom exercises
        $customExercises = Exercise::select(['id', 'name', 'muscle_group_id'])->where('user_id', Auth::id())->get()->map(function ($ex) {
            return [
                'i' => $ex->id,
                'n' => $ex->name,
                'm' => $ex->muscle_group_id
            ];
        })->toArray();

        $exercises = array_merge($exercises, $customExercises);

        return [
            'exercises' => $exercises,
            'muscleGroups' => $muscleGroups
        ];
    }

    // RAW JSON SCHEMA
    // public array $schema = [
    //     'name' => 'mesocycle',
    //     'description' => 'Mesocycle structure',
    //     'type' => 'object',
    //     'properties' => [
    //         'name' => [
    //             'type' => 'string',
    //             'description' => 'Mesocycle name',
    //         ],
    //         'unit' => [
    //             'type' => 'string',
    //             'description' => 'Unit of measure for progression',
    //             'enum' => UnitsOfMeasure::cases(),
    //         ],
    //         'weeksDuration' => [
    //             'type' => 'integer',
    //             'description' => 'Number of weeks',
    //             'minimum' => 1,
    //             'maximum' => 12,
    //         ],
    //         'days' => [
    //             'type' => 'array',
    //             'description' => 'Workout days containing exercises and muscle groups',
    //             'minItems' => 1,
    //             'maxItems' => 7,
    //             'items' => [
    //                 'type' => 'object',
    //                 'description' => 'A day containing label and exercises',
    //                 'properties' => [
    //                     'label' => [
    //                         'type' => 'string',
    //                         'description' => 'Label of the day (e.g. Push, Pull, Legs, Day 1, Day 2)',
    //                     ],
    //                     'exercises' => [
    //                         'type' => 'array',
    //                         'description' => 'A list of exercises for that day',
    //                         'minItems' => 1,
    //                         'items' => [
    //                             'type' => 'object',
    //                             'description' => 'Exercise id and muscle group id',
    //                             'properties' => [
    //                                 'muscleGroup' => [
    //                                     'type' => 'integer',
    //                                     'description' => 'A muscle group ID from the provided muscle groups list',
    //                                     'minimum' => 1,
    //                                 ],
    //                                 'exerciseID' => [
    //                                     'type' => 'integer',
    //                                     'description' => 'An exercise ID from the provided exercises list',
    //                                     'minimum' => 1,
    //                                 ],
    //                             ],
    //                             'required' => ['muscleGroup', 'exerciseID'],
    //                         ],
    //                     ],
    //                 ],
    //                 'required' => ['label', 'exercises'],
    //             ],
    //         ],
    //     ],
    //     'required' => ['name', 'unit', 'weeksDuration', 'days'],
    // ];
};

<?php

namespace App\Ai\Agents;

use App\Contracts\AiClient;
use App\Data\Mesocycle\CreateAiMesocycleData;
use App\Data\Mesocycle\CreateMesocycleData;
use App\Enums\UnitsOfMeasure;
use App\Exceptions\InvalidMesocycleException;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\EnumSchema;

class MesocycleAgent
{
    private ObjectSchema $schema;
    private string $systemPrompt;
    private string $promptVersion = '';
    private string $systemPromptVersion = '';

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

        $this->systemPrompt = <<<SYSTEM_PROMPT
        You are an expert strength and hypertrophy coach working for Spotacus.app.

        Your task is to design safe, realistic, and well-balanced workout mesocycles.
        You think in terms of training splits, weekly volume, fatigue management, and exercise selection.

        You MUST:
        - Follow the provided JSON schema strictly.
        - Output ONLY valid JSON, no explanations or markdown.
        - Use only the provided exercise IDs.
        - Respect user constraints (experience level, goals, session duration).

        You MUST NOT:
        - Invent exercises or IDs.
        - Add fields not defined in the schema.
        - Explain your reasoning in text.
        SYSTEM_PROMPT;
    }


    public function generate(CreateAiMesocycleData $dto)
    {
        $prompt = $this->preparePrompt($dto);

        $response = $this->aiClient->structured($prompt, $this->systemPrompt, $this->schema);

        return $this->prepareMesoDto($response);
    }

    private function preparePrompt(CreateAiMesocycleData $dto): string
    {
        [$preparedExercises, $muscleGroups] = $this->prepareExercises($dto);

        $exercisesJson    = json_encode($preparedExercises, JSON_UNESCAPED_UNICODE);
        $muscleGroupsJson = json_encode($muscleGroups, JSON_UNESCAPED_UNICODE);
        $dtoJson = $dto->toJson();

        return <<<PROMPT
# OBJECTIVE
Generate ONE "mesocycle" JSON object that conforms to the attached JSON schema.
The object will be used directly by the Spotacus app.

# INPUT

## UserPreferences
$dtoJson

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
     - "exercises" array length: 4–8 exercises, appropriate for ~{$dto->sessionDuration->value} minutes.

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

    private function prepareExercises(CreateAiMesocycleData $dto): array
    {
        $exercises =  Exercise::select(['id', 'name', 'muscle_group_id'])
            ->whereIn('exercise_type', $dto->equipment)
            ->where('user_id', null)
            ->get()
            ->map(function ($ex) {
                return [
                    'i' => $ex->id,
                    'n' => $ex->name,
                    'm' => $ex->muscle_group_id
                ];
            })->toArray();

        // Muscle Groups
        $muscleGroups = Cache::rememberForever('ai_muscle_groups', function () {
            return MuscleGroup::select(['id', 'name'])->get()->map(fn($mg) => [
                'i' => $mg->id,
                'n' => $mg->name
            ])->toArray();
        });

        // User custom exercises
        $customExercises = Exercise::select(['id', 'name', 'muscle_group_id'])
            ->where('user_id', Auth::id())
            ->get()
            ->map(function ($ex) {
                return [
                    'i' => $ex->id,
                    'n' => $ex->name,
                    'm' => $ex->muscle_group_id
                ];
            })->toArray();

        $exercises = array_merge($exercises, $customExercises);

        return [$exercises, $muscleGroups];
    }

    private function prepareMesoDto(string $aiResponse): CreateMesocycleData
    {
        try {
            $aiResponse = json_decode($aiResponse, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            report($e);
            throw new InvalidMesocycleException('AI generated an invalid mesocycle JSON');
        }

        if (!isset($aiResponse['days']) || !is_array($aiResponse['days'])) {
            throw new InvalidMesocycleException("AI mesocycle doesn't contain days.");
        }

        $generatedExerciseIds = collect($aiResponse['days'])
            ->flatMap(fn($d) => $d['exercises'] ?? [])
            ->pluck('exerciseID')
            ->filter()
            ->unique()
            ->values();

        $exercisesByMuscleGroup = $this->exerciseLookup($generatedExerciseIds->toArray());

        foreach ($aiResponse['days'] as &$day) {
            if (! isset($day['exercises']) || !is_array($day['exercises'])) {
                throw new InvalidMesocycleException('Day is missing exercises array');
            };

            foreach ($day['exercises'] as &$exercise) {
                if (! isset($exercise['exerciseID'])) {
                    throw new InvalidMesocycleException('Exercise is missing exercise ID');
                }

                $muscleID = $exercisesByMuscleGroup->get($exercise['exerciseID'])?->muscle_group_id;

                if ($muscleID === null) {
                    throw new InvalidMesocycleException("Unknown exercise ID {$exercise['exerciseID']}");
                }
                $exercise['muscleGroup'] = $muscleID;
            }
        }

        unset($day, $exercise);

        try {
            $mesocycleDto = CreateMesocycleData::from($aiResponse);
        } catch (\Throwable $th) {
            report($th);
            throw new InvalidMesocycleException('DTO creation failed. Invalid Mesocycle data.');
        }

        return $mesocycleDto;
    }

    /*
    * @var int[] $ids
    */
    private function exerciseLookup(array $ids)
    {
        return Exercise::select(['id', 'muscle_group_id'])
            ->whereIn('id', $ids)
            ->where(
                fn($q) =>
                $q->where('user_id', Auth::id())
                    ->orWhereNull('user_id')
            )
            ->get()
            ->keyBy('id');
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

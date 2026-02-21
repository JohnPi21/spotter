<?php

namespace App\Ai\Prompts;

use App\Data\Mesocycle\CreateAiMesocycleData;
use App\Enums\UnitsOfMeasure;
use Prism\Prism\Contracts\Schema;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\EnumSchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;
use stdClass;

class MesocyclePrompt
{
    public function __construct() {}

    // schema_key mesocycle.create
    // schema_version 1.0.0
    // schema_hash Hash::from($schema->toArray())
    // prompt_version 1.0.0
    // system_prompt_version: 1.0.0

    public function getVersions(): stdClass
    {
        $schema = $this->schema()->toArray();
        ksort($schema);

        return (object) [
            // 'schema_key'        => 'mesocycle.generate',
            'schema_version' => '1.0.0',
            'schema_hash' => hash('sha256', json_encode($schema)),
            'prompt_version' => '1.0.0',
            'system_prompt_version' => '1.0.0',
        ];
    }

    public function schema(): Schema
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
        return new ObjectSchema(
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
                        fn (UnitsOfMeasure $unit) => $unit->value,
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
    }

    public function prompt(array $exercises, array $muscleGroups, CreateAiMesocycleData $dto): string
    {
        $exercisesJson = json_encode($exercises, JSON_UNESCAPED_UNICODE);
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

    public function systemPrompt(): string
    {
        return <<<'SYSTEM_PROMPT'
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
}

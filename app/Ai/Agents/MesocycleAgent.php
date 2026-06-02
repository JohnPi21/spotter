<?php

namespace App\Ai\Agents;

use App\Ai\Prompts\MesocyclePrompt;
use App\Contracts\AiClient;
use App\Data\Ai\AiCallContextData;
use App\Data\Mesocycle\CreateAiMesocycleData;
use App\Data\Mesocycle\CreateMesocycleData;
use App\Exceptions\InvalidMesocycleException;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Services\PrismAiClient;
use Illuminate\Support\Facades\Cache;

class MesocycleAgent
{
    public int $userId;

    public const CREATE_MESOCYCLE = 'CREATE_MESOCYCLE';

    /**
     * @param  PrismAiClient  $aiClient
     */
    public function __construct(public AiClient $aiClient, public MesocyclePrompt $promptGenerator) {}

    public function generate(int $userId, CreateAiMesocycleData $dto)
    {
        $this->userId = $userId;

        [$preparedExercises, $muscleGroups] = $this->prepareExercises($dto);

        $prompt = $this->promptGenerator->prompt($preparedExercises, $muscleGroups, $dto);
        $systemPrompt = $this->promptGenerator->systemPrompt();
        $schema = $this->promptGenerator->schema();
        $versionMeta = $this->promptGenerator->getVersions();

        $aiCallContextData = new AiCallContextData(
            prompt: $prompt,
            systemPrompt: $systemPrompt,
            schema: $schema,
            userId: $userId,
            callerClass: $this::class,
            operationKey: self::CREATE_MESOCYCLE,
            schemaVersion: $versionMeta->schema_version,
            schemaHash: $versionMeta->schema_hash,
            promptVersion: $versionMeta->prompt_version,
            systemPromptVersion: $versionMeta->system_prompt_version
        );

        // Test the response
        [$aiRequest, $response] = $this->aiClient->structured($aiCallContextData);

        return $this->prepareMesoDto($response);
    }

    private function prepareExercises(CreateAiMesocycleData $dto): array
    {
        // Default Exercises (can't cash because of equipment filter)
        $exercises = Exercise::select(['id', 'name', 'muscle_group_id'])
            ->whereIn('exercise_type', $dto->equipment)
            ->where('user_id', null)
            ->get()
            ->map(function ($ex) {
                return [
                    'i' => $ex->id,
                    'n' => $ex->name,
                    'm' => $ex->muscle_group_id,
                ];
            })->toArray();

        // Muscle Groups
        $muscleGroups = Cache::rememberForever('ai_muscle_groups', function () {
            return MuscleGroup::select(['id', 'name'])->get()->map(fn ($mg) => [
                'i' => $mg->id,
                'n' => $mg->name,
            ])->toArray();
        });

        // User custom exercises
        $customExercises = Exercise::select(['id', 'name', 'muscle_group_id'])
            ->where('user_id', $this->userId)
            ->get()
            ->map(function ($ex) {
                return [
                    'i' => $ex->id,
                    'n' => $ex->name,
                    'm' => $ex->muscle_group_id,
                ];
            })->toArray();

        $exercises = array_merge($exercises, $customExercises);

        return [$exercises, $muscleGroups];
    }

    private function prepareMesoDto(array $aiResponse): CreateMesocycleData
    {
        // try {
        //     $aiResponse = json_decode($aiResponse, true, 512, JSON_THROW_ON_ERROR);
        // } catch (\JsonException $e) {
        //     report($e);
        //     throw new InvalidMesocycleException('AI generated an invalid mesocycle JSON');
        // }

        if (! isset($aiResponse['days']) || ! is_array($aiResponse['days'])) {
            throw new InvalidMesocycleException("AI mesocycle doesn't contain days.");
        }

        $generatedExerciseIds = collect($aiResponse['days'])
            ->flatMap(fn ($d) => $d['exercises'] ?? [])
            ->pluck('exerciseId')
            ->filter()
            ->unique()
            ->values();

        $exercisesByMuscleGroup = $this->exerciseLookup($generatedExerciseIds->toArray());

        foreach ($aiResponse['days'] as &$day) {
            if (! isset($day['exercises']) || ! is_array($day['exercises'])) {
                throw new InvalidMesocycleException('Day is missing exercises array');
            }

            foreach ($day['exercises'] as &$exercise) {
                if (! isset($exercise['exerciseId'])) {
                    throw new InvalidMesocycleException('Exercise is missing exercise ID');
                }

                $muscleId = $exercisesByMuscleGroup->get($exercise['exerciseId'])?->muscle_group_id;

                if ($muscleId === null) {
                    throw new InvalidMesocycleException("Unknown exercise ID {$exercise['exerciseId']}");
                }
                $exercise['muscleGroup'] = $muscleId;

                if (isset($exercise['sets']) && is_array($exercise['sets'])) {
                    foreach ($exercise['sets'] as $set) {
                        foreach (array_keys($set) as $key) {
                            $this->checkForNull($set, $key);
                        }
                    }
                }
                // Make a new action to validate this
                // Calculate 1RM out of this if needed
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
    * @return \Illuminate\Support\Collection<int, Exercise>
    */
    private function exerciseLookup(array $ids)
    {
        return Exercise::select(['id', 'muscle_group_id'])
            ->whereIn('id', $ids)
            ->where(
                fn ($q) => $q->where('user_id', $this->userId)
                    ->orWhereNull('user_id')
            )
            ->get()
            ->keyBy('id');
    }

    private function checkForNull(array &$set, string $entity = 'minReps'): void
    {
        if (! isset($set[$entity])) {
            $set[$entity] = null;

            return;
        }

        if (! is_numeric($set[$entity]) || filter_var($set[$entity], FILTER_VALIDATE_INT) === false) {
            $set[$entity] = null;
        }
    }

    // private function calculate1RM(int $minReps, int $maxReps): int
    // {
    // Brzycki for reps <= 5 | 1RM = W × 36 / (37 - r)
    // Epley for reps > 5 | 1RM = W (1 + r / 30)
    // if($maxReps <= 5)
    //     return 0;
    // }
}

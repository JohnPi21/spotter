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
use Illuminate\Support\Facades\Cache;
use App\Services\PrismAiClient;
use App\Models\User;

class MesocycleAgent
{
    public int $userId;

    /**
     * @param PrismAiClient $aiClient
     */
    public function __construct(public AiClient $aiClient, public MesocyclePrompt $promptGenerator) {}


    public function generate(int $userId, CreateAiMesocycleData $dto)
    {
        $this->userId = $userId;

        [$preparedExercises, $muscleGroups] = $this->prepareExercises($dto);

        $prompt       = $this->promptGenerator->prompt($preparedExercises, $muscleGroups, $dto);
        $systemPrompt = $this->promptGenerator->systemPrompt();
        $schema       = $this->promptGenerator->schema();
        $versionMeta  = $this->promptGenerator->getVersions();

        // Make sure we have all the info needed to construct the aireqeust
        $aiCallContextData = AiCallContextData::from($prompt, $systemPrompt, $schema, $userId, get_class($this), ...$versionMeta);

        $response = $this->aiClient->structured($aiCallContextData);

        return $this->prepareMesoDto($response);
    }


    private function prepareExercises(CreateAiMesocycleData $dto): array
    {
        // Default Exercises (can't cash because of equipment filter) 
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
            ->where('user_id', $this->userId)
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
    * @return \Illuminate\Support\Collection<int, Exercise>
    */
    private function exerciseLookup(array $ids)
    {
        return Exercise::select(['id', 'muscle_group_id'])
            ->whereIn('id', $ids)
            ->where(
                fn($q) =>
                $q->where('user_id', $this->userId)
                    ->orWhereNull('user_id')
            )
            ->get()
            ->keyBy('id');
    }
};

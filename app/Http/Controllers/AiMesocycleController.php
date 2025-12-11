<?php

namespace App\Http\Controllers;

use App\Ai\Agents\MesocycleAgent;
use App\Data\Mesocycle\CreateAiMesocycleData;
use App\Enums\EquipmentsEnum;
use App\Enums\ExperienceEnum;
use App\Enums\SessionDurationEnum;
use App\Enums\SplitsEnum;
use App\Enums\TrainingGoalsEnum;
use App\Http\Requests\AiMesocycleRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiMesocycleController extends Controller
{
    public function create()
    {
        return Inertia::render("mesocycles/AiCreate", [
            'experienceOptions' => ExperienceEnum::options(),
            'primaryGoalOptions' => TrainingGoalsEnum::options(),
            'splitPreferenceOptions' => SplitsEnum::options(),
            'equipmentOptions' => EquipmentsEnum::options(),
            'sessionDurationOptions' => SessionDurationEnum::values(),
        ]);
    }

    public function generate(AiMesocycleRequest $request, MesocycleAgent $agent)
    {
        $aiMesocycleDTO = CreateAiMesocycleData::from($request->validated());

        // pass to agenta
        $schema = $agent->generate($aiMesocycleDTO);


        // send user to preview page
        // return Inertia::render("Mesocycle/Edit");
    }
}

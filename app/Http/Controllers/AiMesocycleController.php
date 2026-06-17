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
use App\Models\MesoTemplate;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AiMesocycleController extends Controller
{
    public function create()
    {
        return Inertia::render('mesocycles/AiCreate', [
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

        // @TODO implement this route later
        return to_route('mesocycles')->with('error', 'Feature in development!');

        // pass to agenta
        // $mesoDTO = $agent->generate(Auth::id(), $aiMesocycleDTO);

        // $mesoTemplate = MesoTemplate::create();

        // send user to preview page
        // @TODO: I also added min and max range for the user to also save the the range he wants to do that exercise
        // Ask ai to also include the recommended range in the request and also include a comment why he chose what he chose in a comment somewhere

        // @TODO: make here so we save the template in the DB and redirect the user to mesocycles/create?template=ID_OF_TEMPLATE
        // ASK FROM AI also some commentary about the template and why it chose what it chose
        // IT can be added directly in the structure
        // Maybe create a chat feature in the forntend that will let the user ask more questions and the ai to send more details or edit the tmeplate
        // return to_route('mesocycles.create')->with('custom', ['template' => $mesoDTO->toArray()]);
    }
}

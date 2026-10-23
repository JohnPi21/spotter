<?php

namespace App\Actions\Mesocycle;

use App\Data\Mesocycle\CreateMesocycleTemplateData;
use App\Models\MesoTemplate;
use App\Services\MesocyclePlan;

class CreateMesoTemplate
{
    public function __construct(protected MesocyclePlan $plan) {}

    public function execute(CreateMesocycleTemplateData $mesoTemplateDTO): void
    {
        $attributes = $mesoTemplateDTO->toArray();
        $schema = is_string($attributes['schema'])
            ? json_decode($attributes['schema'], true)
            : json_decode(json_encode($attributes['schema'], JSON_THROW_ON_ERROR), true);
        $attributes['schema'] = $this->plan->validate($schema);
        $attributes['planned_stats'] = $this->plan->stats($attributes['schema']);

        MesoTemplate::create($attributes);
    }
}

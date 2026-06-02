<?php

namespace App\Actions\Mesocycle;

use App\Data\Mesocycle\CreateMesocycleTemplateData;
use App\Models\MesoTemplate;

class CreateMesoTemplate
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function execute(CreateMesocycleTemplateData $mesoTemplateDTO): void
    {
        MesoTemplate::create($mesoTemplateDTO->toArray());
    }
}

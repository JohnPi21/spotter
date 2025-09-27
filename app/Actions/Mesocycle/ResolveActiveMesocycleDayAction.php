<?php

namespace App\Actions\Mesocycle;

use App\Models\Mesocycle;
use App\Exceptions\AppException;

class ResolveActiveMesocycleDayAction
{
    public function __construct() {}

    function __invoke(): array | AppException
    {
        $mesocycle = Mesocycle::select('id')::with('days')::mine()->where('status', Mesocycle::STATUS_ACTIVE)->first();

        if (! $mesocycle) {
            throw new AppException(404, __("No active mesocycle"), "NO_ACTIVE_MESOCYCLE");
        }

        $currentDayId = $mesocycle->days()->whereNull('finished_at')->orderBy('id')->value('id');

        $currentDayId ??= $mesocycle->days()->orderByDesc('id')->value('id');

        if (! $currentDayId) {
            throw new AppException(404, __("No day found for mesocycle"), 'NO_DAY_FOUND');
        }

        return [$currentDayId, $mesocycle->id];
    }
}

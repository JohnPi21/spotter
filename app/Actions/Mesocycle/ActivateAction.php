<?php

namespace App\Actions\Mesocycle;

use App\Models\Mesocycle;
use Illuminate\Support\Facades\DB;
use App\Exceptions\AppException;

class ActivateAction
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function __invoke(Mesocycle $mesocycle): void
    {
        $affected = DB::update('
            UPDATE mesocycles
            SET status = 
                CASE WHEN id = ? THEN ? ELSE ?  END
                updated_at = CURRENT_TIMESTAMP
            WHERE user_id = ?
            AND EXISTS(
                SELECT 1 from meso_days d
                WHERE d.mesocycle_id = ?
                AND d.finished_at IS NULL
            )
        ', [$mesocycle->id, Mesocycle::STATUS_ACTIVE, Mesocycle::STATUS_INACTIVE, $mesocycle->user_id, $mesocycle->id]);

        if (! $affected) {
            throw new AppException(422, __('Mesocycle cannot activate (no unfinished days)'), 'NO_UNFINISHED_DAYS');
        }
    }
}

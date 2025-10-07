<?php

namespace App\Data\Set;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class UpdateData extends Data
{
    public function __construct(
        public int $reps,
        public string $weight,
        public ?Carbon $finished_at,
        bool $status,
    ) {
        $this->finished_at = $status ? now() : null;
    }
}

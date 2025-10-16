<?php

namespace App\Data\Set;

use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Attributes\Hidden;

class UpdateSetData extends Data
{
    public function __construct(
        public int $reps,
        public string $weight,
        public ?Carbon $finished_at,
        #[Hidden]
        public ?bool $status,
    ) {
        if ($this->status || is_null($this->status)) {
            $this->finished_at = now();
        } else {
            $this->finished_at = null;
        }
    }
}

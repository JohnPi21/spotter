<?php

namespace App\Models;

use App\Enums\RequestStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class AiRequest extends Model
{
    protected $guarded = [];

    public function setFailed(Throwable $e, ?string $stage = null): self
    {
        $this->update([
            'status' => RequestStatusEnum::FAILED,
            'error_class' => get_class($e),
            'error_message' => $e->getMessage(),
            'error_stage' => $stage ?? 'UNDEFINED',
        ]);

        return $this;
    }
}

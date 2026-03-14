<?php

namespace App\Models;

use App\Enums\RequestStatusEnum;
use Exception;
use Illuminate\Database\Eloquent\Model;

class AiRequest extends Model
{
    protected $guarded = [];

    public function setFailed(Exception $e, string $stage): self
    {
        $this->update([
            'status' => RequestStatusEnum::FAILED,
            'error_class' => get_class($e),
            'error_message' => $e->getMessage(),
            'error_stage' => 'transport',
        ]);

        return $this;
    }
}

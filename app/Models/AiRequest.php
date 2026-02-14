<?php

namespace App\Models;

use App\Enums\RequestStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class AiRequest extends Model
{
    protected $guarded = [];

    public function setFailed(): self
    {
        $this->update(['status' => RequestStatusEnum::FAILED]);

        return $this;
    }
}

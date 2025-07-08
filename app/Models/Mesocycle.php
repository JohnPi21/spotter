<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Attributes\Scope;

class Mesocycle extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 0;

    protected $guarded = [];

    public function days(): HasMany
    {
        return $this->hasMany(MesoDay::class);
    }

    #[Scope]
    protected function mine(Builder $query): void
    {
        $query->where('user_id', Auth::id());
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', 1);
    }
}

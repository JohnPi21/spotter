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
    public const MIN_WEEKS = 3;
    public const MAX_WEEKS = 12;

    protected $guarded = [];

    public function days(): HasMany
    {
        return $this->hasMany(MesoDay::class);
    }

    #[Scope]
    protected function mine(Builder $query): void
    {
        $this->ownedBy($query, Auth::id());
    }

    #[Scope]
    protected function ownedBy(Builder $query, ?int $userID): void
    {
        $query->where('user_id', $userID);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('status', 1);
    }

    public static function weeksRange(): array
    {
        return range(self::MIN_WEEKS, self::MAX_WEEKS);
    }
}

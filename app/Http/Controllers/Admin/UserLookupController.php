<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserLookupController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $term = trim($validated['search'] ?? '');

        $users = User::query()
            ->select('id', 'name', 'email')
            ->when($term !== '', function (Builder $builder) use ($term): void {
                $builder->where(function (Builder $builder) use ($term): void {
                    $builder
                        ->whereLike('name', '%'.$term.'%')
                        ->orWhereLike('email', '%'.$term.'%');
                });
            })
            ->orderBy('name')
            ->orderBy('id')
            ->simplePaginate()
            ->withQueryString();

        return response()->json([
            'users' => $users,
        ]);
    }
}

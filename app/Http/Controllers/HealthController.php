<?php

namespace App\Http\Controllers;

use Illuminate\Database\DatabaseManager;
use Illuminate\Http\JsonResponse;
use PDOException;

class HealthController extends Controller
{
    public function __construct(protected DatabaseManager $database) {}

    public function __invoke(): JsonResponse
    {
        $startedAt = hrtime(true);
        $status = 'ok';
        $httpStatus = 200;

        try {
            $this->database->connection()->select('SELECT 1');
        } catch (PDOException) {
            $status = 'error';
            $httpStatus = 503;
        }

        return response()->json([
            'status' => $status,
            'checked_at' => now()->toISOString(),
            'database_duration_ms' => round((hrtime(true) - $startedAt) / 1_000_000, 2),
        ], $httpStatus);
    }
}

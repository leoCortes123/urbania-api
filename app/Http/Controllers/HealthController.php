<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class HealthController extends Controller
{
    public function check(): JsonResponse
    {
        $traceId = request()->header('X-Trace-Id', (string) Str::uuid());

        /** @var array<string, array<string, bool|string>> $checks */
        $checks = [
            'database' => $this->checkDatabase(),
            'redis' => $this->checkRedis(),
            'storage' => $this->checkStorage(),
        ];

        $allHealthy = collect($checks)->every(
            fn (array $check): bool => (bool) $check['healthy']
        );

        return response()->json([
            'data' => [
                'status' => $allHealthy ? 'healthy' : 'unhealthy',
                'timestamp' => now()->toIso8601ZuluString(),
                'checks' => $checks,
            ],
            'meta' => [
                'trace_id' => $traceId,
            ],
        ], $allHealthy ? 200 : 503);
    }

    /**
     * @return array<string, bool|string>
     */
    private function checkDatabase(): array
    {
        try {
            DB::connection()->getPdo();

            return ['healthy' => true, 'message' => 'Connected'];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => $this->sanitizeMessage($e->getMessage())];
        }
    }

    /**
     * @return array<string, bool|string>
     */
    private function checkRedis(): array
    {
        try {
            Redis::connection()->ping();

            return ['healthy' => true, 'message' => 'Connected'];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => $this->sanitizeMessage($e->getMessage())];
        }
    }

    /**
     * @return array<string, bool|string>
     */
    private function checkStorage(): array
    {
        try {
            $path = 'health-check-'.Str::random(8);
            Storage::put($path, 'health');
            Storage::delete($path);

            return ['healthy' => true, 'message' => 'Writable'];
        } catch (\Throwable $e) {
            return ['healthy' => false, 'message' => $this->sanitizeMessage($e->getMessage())];
        }
    }

    private function sanitizeMessage(string $message): string
    {
        if (mb_check_encoding($message, 'UTF-8')) {
            return $message;
        }

        return mb_convert_encoding($message, 'UTF-8', 'UTF-8');
    }
}

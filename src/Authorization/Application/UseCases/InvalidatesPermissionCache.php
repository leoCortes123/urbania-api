<?php

declare(strict_types=1);

namespace Urbania\Authorization\Application\UseCases;

use Illuminate\Cache\RedisStore;
use Illuminate\Support\Facades\Cache;

trait InvalidatesPermissionCache
{
    private function invalidatePermissionCacheForUser(string $userId): void
    {
        $store = Cache::getStore();

        if (! $store instanceof RedisStore) {
            return;
        }

        $redis = $store->connection();
        $pattern = "perms:{$userId}:*";
        $keys = $redis->keys($pattern);

        if ($keys === []) {
            return;
        }

        $redis->del(...$keys);
    }
}

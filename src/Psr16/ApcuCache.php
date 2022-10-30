<?php

namespace Libertyphp\Cache\Psr16;

use DateTimeImmutable;
use Psr\SimpleCache\CacheInterface;

class ApcuCache implements CacheInterface
{
    private static function calculateTtlSeconds(null|int|\DateInterval $ttl = null): int
    {
        if ($ttl instanceof \DateInterval) {
            $currentDateTime    = new DateTimeImmutable();
            $expirationDateTime = $currentDateTime->add($ttl);

            $ttlSeconds = $expirationDateTime->getTimestamp() - $currentDateTime->getTimestamp();
        } else {
            $ttlSeconds = (int) $ttl;
        }

        if ($ttlSeconds < 0) {
            throw new InvalidTtlException('TTL must be equal to or greater than zero');
        }

        return $ttlSeconds;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = \apcu_fetch($key, $success);
        if (!$success) {
            return $default;
        }

        return $value;
    }

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool
    {
        return \apcu_store($key, $value, self::calculateTtlSeconds($ttl));
    }

    public function delete(string $key): bool
    {
        return \apcu_delete($key);
    }

    public function clear(): bool
    {
        return \apcu_clear_cache();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $fetched = \apcu_fetch($keys, $success);
        if (!$success) {
            $fetched = [];
        }

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $fetched[$key] ?? $default;
        }

        return $result;
    }

    public function setMultiple(iterable $values, null|int|\DateInterval $ttl = null): bool
    {
        $result = \apcu_store($values, null, self::calculateTtlSeconds($ttl));
        return is_bool($result) ? $result : count($result) === 0;
    }

    public function deleteMultiple(iterable $keys): bool
    {
        $result = \apcu_delete($keys);
        return is_bool($result) ? $result : count($result) === 0;
    }

    public function has(string $key): bool
    {
        return \apcu_exists($key);
    }
}

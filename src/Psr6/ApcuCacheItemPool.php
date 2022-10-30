<?php

namespace Libertyphp\Cache\Psr6;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ApcuCacheItemPool implements CacheItemPoolInterface
{
    /** @var CacheItemInterface[] */
    private array $deferred = [];

    public function getItem(string $key): CacheItemInterface
    {
        $value = \apcu_fetch($key, $success);
        if (!$success) {
            return new ApcuCacheItem($key, null);
        }

        return new ApcuCacheHitItem($key, $value);
    }

    public function getItems(array $keys = []): iterable
    {
        $fetched = \apcu_fetch($keys, $success);
        if (!$success) {
            $fetched = [];
        }

        /** @var ApcuCacheItem[] $items */
        $items = [];

        foreach ($keys as $key) {
            $fetchedValue = $fetched[$key] ?? null;
            $items[$key] = $fetchedValue === null ? new ApcuCacheItem($key, null) : new ApcuCacheHitItem($key, $fetchedValue);
        }

        return $items;
    }

    public function hasItem(string $key): bool
    {
        return \apcu_exists($key);
    }

    public function clear(): bool
    {
        return \apcu_clear_cache();
    }

    public function deleteItem(string $key): bool
    {
        return \apcu_delete($key);
    }

    public function deleteItems(array $keys): bool
    {
        $result = \apcu_delete($keys);
        return is_bool($result) ? $result : count($result) === 0;
    }

    public function save(CacheItemInterface $item): bool
    {
        /** @var ApcuCacheItem $item */
        return \apcu_store($item->getKey(), $item->get(), $item->getTtlSeconds());
    }

    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[$item->getKey()] = $item;
        return true;
    }

    public function commit(): bool
    {
        $success = true;
        foreach ($this->deferred as $item) {
            if (!$this->save($item)) {
                $success = false;
            }
        }

        $this->deferred = [];

        return $success;
    }
}

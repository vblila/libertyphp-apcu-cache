<?php

namespace Libertyphp\Cache\Psr6;

use Psr\Cache\CacheItemInterface;

class ApcuCacheHitItem extends ApcuCacheItem implements CacheItemInterface
{
    public function isHit(): bool
    {
        return true;
    }
}

<?php

namespace Libertyphp\Cache\Psr6;

use DateTimeImmutable;
use Psr\Cache\CacheItemInterface;

class ApcuCacheItem implements CacheItemInterface
{
    private string $key;

    private mixed $value;

    private ?\DateTimeInterface $expiredAt = null;

    private int|\DateInterval|null $expiresAfter = null;

    public function __construct(string $key, mixed $value)
    {
        $this->key   = $key;
        $this->value = $value;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function get(): mixed
    {
        return $this->value;
    }

    public function isHit(): bool
    {
        return false;
    }

    public function set(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function expiresAt(?\DateTimeInterface $expiration): static
    {
        $this->expiresAfter = null;
        $this->expiredAt    = $expiration;

        return $this;
    }

    public function expiresAfter(int|\DateInterval|null $time): static
    {
        $this->expiredAt    = null;
        $this->expiresAfter = $time;

        return $this;
    }

    public function getTtlSeconds(): int
    {
        $ttlSeconds = 0;

        if ($this->expiredAt !== null) {
            $currentTime = new DateTimeImmutable();
            $ttlSeconds = $this->expiredAt->getTimestamp() - $currentTime->getTimestamp();
        } elseif ($this->expiresAfter !== null) {
            if ($this->expiresAfter instanceof \DateInterval) {
                $currentDateTime = new DateTimeImmutable();
                $expirationDateTime = $currentDateTime->add($this->expiresAfter);
                $ttlSeconds = $expirationDateTime->getTimestamp() - $currentDateTime->getTimestamp();
            } else {
                // From PSR-6:
                // An integer parameter (time) is understood to be the time in seconds until expiration.
                $ttlSeconds = (int) $this->expiresAfter;
            }
        }

        return $ttlSeconds;
    }
}

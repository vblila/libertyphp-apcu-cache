APCu Cache
==========
[PSR-6](https://www.php-fig.org/psr/psr-6/) and [PSR-16](https://www.php-fig.org/psr/psr-16/) cache implementation using [APCu](https://www.php.net/manual/en/book.apcu.php). APCu is an in-memory key-value store for PHP. Keys are of type string and values can be any PHP variables.

Why APCu Cache?
===============

Network
-------
APCu is non-distributed cache which you can use only in HTTP request (it doesn't work in PHP CLI). If you have a web application that doesn't run on different web servers it's better to use APCu cache, because it's the simplest cache without requests over the network.

Speed
-----
APCu is faster than Memcached and Redis. Benchmark results of setting and getting of 10000 string values (Intel Core i5-8250U, DDR4 2400MHz):
```
APCu
set 0.01187s
get 0.00633s

Memcached
set 0.66142s
get 0.63786s

Redis
set 0.37687s
get 0.39227s
```

So, if your web application makes a lot of requests to the cache, it's better to have a warmed APCu cache on each worker.

Install
=======
```
composer require libertyphp/apcu-cache
```

Docker configuration
====================
If you use php-fpm docker add this code to install APCu in PHP:
```dockerfile
ARG APCU_VERSION=5.1.21

RUN pecl install apcu-${APCU_VERSION} && docker-php-ext-enable apcu
RUN echo "apc.shm_size=1024M" >> /usr/local/etc/php/php.ini
RUN echo "apc.ttl=0" >> /usr/local/etc/php/php.ini
RUN echo "apc.gc_ttl=0" >> /usr/local/etc/php/php.ini
```

Using  PSR-6 implementation
===========================
```php
$cachePool = new ApcuCacheItemPool();
$cacheItem = (new ApcuCacheItem('u1030_rating', 98))
    ->expiresAfter(3600);

$cachePool->save($cacheItem);
```

Using PSR-16 implementation
===========================
```php
$cache = new ApcuCache();
$cache->set('u1030_rating', 98, 3600);
```

Copyright
=========
Copyright (c) 2022 Vladimir Lila. See LICENSE for details.

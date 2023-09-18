<?php

namespace Modules\Common\Support\Traits;

use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    /** @var mixed */
    protected $key;

    protected $cache;

    protected string $cachePrefix;

    public function getCache($driver = 'redis')
    {
        if ($this->cache instanceof \Illuminate\Contracts\Cache\Repository) {
            return $this->cache;
        }

        return $this->cache = Cache::driver($driver);
    }

    protected function getCacheKey()
    {
        return $this->cachePrefix.$this->key;
    }
}

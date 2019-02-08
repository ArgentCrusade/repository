<?php

namespace ArgentCrusade\Repository\Helpers;

use Illuminate\Contracts\Cache\Repository as CacheRepository;

class RepositoryCacheKeys
{
    /** @var string */
    private $repository;

    /**
     * RepositoryCacheKeys constructor.
     *
     * @param string $repository
     */
    public function __construct(string $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get cache key for the current repository's cache keys.
     *
     * @return string
     */
    private function getRepositoryKeysCacheKey()
    {
        return 'eloquent-repository-'.$this->repository.'-keys';
    }

    /**
     * Remember given cache key for future cleanup.
     *
     * @param CacheRepository $cache
     * @param string          $cacheKey
     */
    public function rememberCacheKey(CacheRepository $cache, string $cacheKey)
    {
        $keys = $cache->get($this->getRepositoryKeysCacheKey());
        $keys = is_array($keys) ? $keys : [];

        $keys[] = $cacheKey;

        $cache->forever($this->getRepositoryKeysCacheKey(), $keys);
    }

    /**
     * Get all cache tags for the current repository.
     *
     * @param CacheRepository $cache
     *
     * @return array
     */
    public function getRepositoryCacheKeys(CacheRepository $cache)
    {
        return $cache->get($this->getRepositoryKeysCacheKey(), []);
    }

    /**
     * Forget all cache keys for the current repository.
     *
     * @param CacheRepository $cache
     */
    public function forgetRepositoryCacheKeys(CacheRepository $cache)
    {
        $cache->forget($this->getRepositoryKeysCacheKey());
    }
}

<?php

namespace ArgentCrusade\Repository\Contracts\Repositories;

interface CacheableRepositoryInterface
{
    /**
     * Set cache repository.
     *
     * @param mixed $repository
     *
     * @return mixed
     */
    public function setCacheRepository($repository);

    /**
     * Get the cache repository.
     *
     * @return mixed
     */
    public function getCacheRepository();

    /**
     * Get the cache key for the given method & arguments list.
     *
     * @param string $method
     * @param array  $args
     *
     * @return string
     */
    public function getCacheKey(string $method, array $args = []);

    /**
     * Get the cache duration in minutes.
     *
     * @return int
     */
    public function getCacheDuration();

    /**
     * Set the cache flag for the current request.
     *
     * @param bool $flag = true
     *
     * @return CacheableRepositoryInterface
     */
    public function skipCache(bool $flag = true);

    /**
     * Enables cache for the given request.
     *
     * @return CacheableRepositoryInterface
     */
    public function enableCache();

    /**
     * Disables cache for the given request.
     *
     * @return CacheableRepositoryInterface
     */
    public function disableCache();
}

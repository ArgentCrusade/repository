<?php

namespace ArgentCrusade\Repository\Helpers;

use ArgentCrusade\Repository\Contracts\Criterias\CriteriaInterface;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CacheableEloquentRepository
{
    /** @var Container */
    private $container;

    /** @var CacheRepository */
    private $cache;

    /** @var string */
    private $repository;

    /** @var RepositoryCacheKeys */
    private $cacheKeys;

    /** @var bool */
    private $hasTagsSupport = false;

    /**
     * CacheableEloquentRepository constructor.
     *
     * @param Container $container
     * @param string    $repository
     */
    public function __construct(Container $container, string $repository)
    {
        $this->container = $container;
        $this->repository = $repository;
        $this->cacheKeys = new RepositoryCacheKeys($repository);
    }

    /**
     * Get the cache repository tag.
     *
     * @return string
     */
    private function getCacheRepositoryTag()
    {
        return 'argentcrusade-repository-'.$this->repository;
    }

    /**
     * Set cache repository.
     *
     * @param CacheRepository $cache
     *
     * @return $this
     */
    public function setCacheRepository(CacheRepository $cache)
    {
        $this->cache = $cache;
        $this->hasTagsSupport = method_exists($cache, 'tags') && method_exists($cache->getStore(), 'tags');

        return $this;
    }

    /**
     * Determines whether the cache instance is available.
     *
     * @return bool
     */
    public function hasCacheRepository()
    {
        return !is_null($this->cache);
    }

    /**
     * Get the underlying cache repository.
     *
     * @return CacheRepository|null
     */
    public function getCacheRepository()
    {
        return $this->cache;
    }

    /**
     * Determines whether the cache repository has tags support.
     *
     * @return bool
     */
    public function hasTagsSupport()
    {
        return $this->hasTagsSupport;
    }

    /**
     * Apply repository cache tags if possible.
     *
     * @param array $tags = []
     *
     * @return CacheRepository
     */
    public function applyRepositoryCacheTags(array $tags = [])
    {
        if (!$this->hasTagsSupport()) {
            return $this->getCacheRepository();
        }

        $cacheTags = array_merge($tags, [$this->getCacheRepositoryTag()]);

        return $this->getCacheRepository()->tags(array_unique($cacheTags));
    }

    /**
     * Generate cache key for the given data.
     *
     * @param Collection $criteria
     * @param string     $method
     * @param array      $args
     *
     * @return string
     */
    public function generateCacheKey(Collection $criteria, string $method, array $args = [])
    {
        $serializedArgs = serialize($args);
        $serializedCriteria = $this->serializeCriteriaStack($criteria);

        $request = $this->container->make(Request::class);

        return implode(';', [
            'argentcrusade-repository',
            $this->repository.'@'.$method,
            md5($serializedArgs.'-'.$serializedCriteria),
            $request->fullUrl(),
        ]);
    }

    /**
     * Serialize given criteria stack.
     *
     * @param Collection $criteria
     *
     * @return string
     */
    private function serializeCriteriaStack(Collection $criteria)
    {
        try {
            return serialize($criteria);
        } catch (\Throwable $e) {
            return serialize($criteria->map(function (CriteriaInterface $criteria) {
                return $this->serializeCriteria($criteria);
            }));
        }
    }

    /**
     * Serialize given criteria.
     *
     * @param CriteriaInterface $criteria
     *
     * @return CriteriaInterface|array
     */
    private function serializeCriteria(CriteriaInterface $criteria)
    {
        try {
            serialize($criteria);

            return $criteria;
        } catch (\Throwable $e) {
            if ($e->getMessage() !== 'Serialization of \'Closure\' is not allowed') {
                throw $e;
            }

            $reflection = new \ReflectionObject($criteria);

            return [
                'hash' => md5((string) $reflection),
                'properties' => $reflection->getProperties(),
            ];
        }
    }

    /**
     * Remember cacheable item.
     *
     * @param bool     $allowed
     * @param array    $tags
     * @param string   $key
     * @param int      $duration
     * @param callable $callback
     * @param array    $args
     *
     * @return mixed
     */
    public function remember(bool $allowed, array $tags, string $key, int $duration, callable $callback, array $args)
    {
        if (!$allowed) {
            return call_user_func_array($callback, $args);
        } elseif (!$this->hasCacheRepository()) {
            return call_user_func_array($callback, $args);
        }

        $this->cacheKeys->rememberCacheKey($this->getCacheRepository(), $key);

        return $this->applyRepositoryCacheTags($tags)
            ->remember($key, $duration, function () use ($callback, $args) {
                return call_user_func_array($callback, $args);
            });
    }

    /**
     * Save given cache key to the repository's cache keys list.
     *
     * @param string $key
     *
     * @return CacheableEloquentRepository
     */
    public function rememberCacheKey(string $key)
    {
        $this->cacheKeys->rememberCacheKey($this->getCacheRepository(), $key);

        return $this;
    }

    /**
     * Reset entire repository cache.
     *
     * @param array $tags = []
     *
     * @return CacheableEloquentRepository
     */
    public function resetRepositoryCache(array $tags = [])
    {
        if (!$this->hasCacheRepository()) {
            return $this;
        } elseif ($this->hasTagsSupport()) {
            return $this->resetRepositoryCacheViaTags($tags);
        }

        $keys = $this->cacheKeys->getRepositoryCacheKeys($cache = $this->getCacheRepository());
        $this->cacheKeys->forgetRepositoryCacheKeys($cache);

        if (!is_array($keys) || empty($keys)) {
            return $this;
        }

        foreach ($keys as $key) {
            $cache->forget($key);
        }

        return $this;
    }

    /**
     * Reset repository cache using tags.
     *
     * @param array $tags = []
     *
     * @return CacheableEloquentRepository
     */
    protected function resetRepositoryCacheViaTags(array $tags = [])
    {
        $cacheTags = array_merge([$this->getCacheRepositoryTag()], $tags);

        $this->getCacheRepository()
            ->tags(array_unique($cacheTags))
            ->flush();

        return $this;
    }
}

<?php

namespace ArgentCrusade\Repository\Traits;

use ArgentCrusade\Repository\Contracts\Criterias\CriteriaInterface;
use ArgentCrusade\Repository\Helpers\CacheableEloquentRepository;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;

trait CacheableEloquentRepositoryTrait
{
    /** @var CacheableEloquentRepository */
    protected $repository;

    /** @var bool */
    protected $cacheSkipped = false;

    /** @var bool */
    protected $cacheEnabled = true;

    /**
     * Get the container instance.
     *
     * @return Container
     */
    abstract protected function getContainer();

    /**
     * Get the criteria stack.
     *
     * @return Collection
     */
    abstract public function getCriteriaStack();

    /**
     * Get cache tags for the flush operation.
     *
     * @param string|null $method
     * @param array       $args
     *
     * @return array
     */
    public function getCacheTagsForFlush(string $method = null, array $args = [])
    {
        return [];
    }

    /**
     * Get cache tags for the remember operation.
     *
     * @param string $method
     * @param array  $args
     *
     * @return array
     */
    public function getCacheTagsForRemember(string $method, array $args = [])
    {
        return [];
    }

    /**
     * Boot trait.
     */
    protected function bootCacheableEloquentRepositoryTrait()
    {
        $this->cacheEnabled = config('repository.cache.enabled') !== false;
        $this->cacheSkipped = false;
        $this->repository = new CacheableEloquentRepository(
            $this->getContainer(),
            static::class
        );

        $this->setCacheRepository(
            $this->getContainer()->make(CacheRepository::class)
        );
    }

    /**
     * Remember repository entry if possible.
     *
     * @param string   $method
     * @param array    $args
     * @param bool     $resetBuilder = false
     * @param callable $callback     = null
     *
     * @return mixed
     */
    protected function cachedRepositoryEntry(string $method, array $args, bool $resetBuilder = false, callable $callback = null)
    {
        $callback = $callback ?? function () use ($method, $args) {
            return call_user_func_array(['parent', $method], $args);
        };

        $result = $this->repository->remember(
            $this->cacheEnabled() && !$this->cacheSkipped(),
            $this->getCacheTagsForRemember($method, $args),
            $this->getCacheKey($method, $args),
            $this->getCacheDuration(),
            $callback,
            $args
        );

        if ($resetBuilder) {
            $this->resetBuilder();
        }

        return $result;
    }

    /**
     * Call parent method & reset repository cache.
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     */
    protected function callParentAndResetCache(string $method, array $args)
    {
        $result = call_user_func_array(['parent', $method], $args);

        $this->resetRepositoryCache($method, $args);

        return $result;
    }

    /**
     * Reset the entire repository cache.
     *
     * @param string|null $method
     * @param array       $args
     *
     * @return static
     */
    public function resetRepositoryCache(string $method = null, array $args = [])
    {
        $this->repository->resetRepositoryCache(
            $this->getCacheTagsForFlush($method, $args)
        );

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCacheRepository($repository)
    {
        $this->repository->setCacheRepository($repository);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheRepository()
    {
        return $this->repository->getCacheRepository();
    }

    /**
     * {@inheritdoc}
     */
    public function skipCache(bool $flag = true)
    {
        $this->cacheSkipped = $flag;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function cacheSkipped()
    {
        return $this->cacheSkipped;
    }

    /**
     * {@inheritdoc}
     */
    public function enableCache()
    {
        $this->cacheEnabled = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function disableCache()
    {
        $this->cacheEnabled = false;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function cacheEnabled()
    {
        return $this->cacheEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey(string $method, array $args = [])
    {
        return $this->repository->generateCacheKey(
            $this->getCriteriaStack(),
            $method,
            $args
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDuration()
    {
        if (isset($this->cacheDuration)) {
            return $this->cacheDuration;
        }

        return config('repository.cache.duration', 15);
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $attributes = [])
    {
        return $this->callParentAndResetCache('create', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function update(array $attributes, $id)
    {
        return $this->callParentAndResetCache('update', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function delete($id)
    {
        return $this->callParentAndResetCache('delete', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function get(array $columns = ['*'])
    {
        return $this->cachedRepositoryEntry('get', func_get_args(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $columns = ['*'])
    {
        return $this->cachedRepositoryEntry('all', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function getByCriteria(CriteriaInterface $criteria, array $columns = ['*'])
    {
        return $this->cachedRepositoryEntry('getByCriteria', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function first(array $columns = ['*'])
    {
        return $this->cachedRepositoryEntry('first', func_get_args(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function firstOrFail(array $columns = ['*'])
    {
        return $this->cachedRepositoryEntry('firstOrFail', func_get_args(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id, array $columns = ['*'])
    {
        return $this->cachedRepositoryEntry('find', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function findOrFail($id, array $columns = ['*'])
    {
        return $this->cachedRepositoryEntry('findOrFail', func_get_args());
    }

    /**
     * {@inheritdoc}
     */
    public function findWhere(array $where, array $columns = ['*'])
    {
        return $this->cachedRepositoryEntry('findWhere', func_get_args(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function findWhereIn(string $field, array $values, array $columns = ['*'])
    {
        return $this->cachedRepositoryEntry('findWhereIn', func_get_args(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function findWhereNotIn(string $field, array $values, array $columns = ['*'])
    {
        return $this->cachedRepositoryEntry('findWhereNotIn', func_get_args(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function pluck(string $column, string $key = null)
    {
        return $this->cachedRepositoryEntry('pluck', func_get_args(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(int $perPage = null, array $columns = ['*'], string $pageName = 'page', int $page = null)
    {
        return $this->cachedRepositoryEntry('paginate', func_get_args(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function simplePaginate(int $perPage = null, array $columns = ['*'], string $pageName = 'page', int $page = null)
    {
        return $this->cachedRepositoryEntry('simplePaginate', func_get_args(), true);
    }
}

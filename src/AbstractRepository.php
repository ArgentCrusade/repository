<?php

namespace ArgentCrusade\Repository;

use ArgentCrusade\Repository\Contracts\Repositories\CacheableRepositoryInterface;
use ArgentCrusade\Repository\Contracts\RepositoryFilterInterface;
use ArgentCrusade\Repository\Criterias\Common\SearchCriteria;
use ArgentCrusade\Repository\Traits\CacheableEloquentRepositoryTrait;

abstract class AbstractRepository extends EloquentRepository implements CacheableRepositoryInterface
{
    use CacheableEloquentRepositoryTrait;

    /**
     * List of searchable columns.
     *
     * @var array
     */
    protected $searchable = [
        //
    ];

    /**
     * List of orderable columns.
     *
     * @var array
     */
    protected $orderable = [
        //
    ];

    /**
     * List of complex orderings.
     *
     * @var array
     */
    protected $ordering = [
        //
    ];

    /**
     * Get the searchable columns list.
     *
     * @return array
     */
    public function searchableColumns()
    {
        return $this->searchable;
    }

    /**
     * Get the orderable columns list.
     *
     * @return array
     */
    public function orderableColumns()
    {
        return $this->orderable;
    }

    /**
     * Get the complex orderings list.
     *
     * @return array
     */
    public function ordering()
    {
        return $this->ordering;
    }

    /**
     * Apply request filters to the current repository instance.
     *
     * @param array $requestFilters = []
     * @param array $filters        = []
     *
     * @return AbstractRepository
     */
    public function applyFilters(array $requestFilters = [], array $filters = [])
    {
        if (!count($requestFilters) || !count($filters)) {
            return $this->safeSearch($requestFilters);
        }

        collect($requestFilters)->each(function ($value, $key) use ($filters) {
            /** @var RepositoryFilterInterface $filter */
            $filter = $filters[$key] ?? null;

            if (is_null($filter)) {
                return;
            }

            $filter->apply($this, $value);
        });

        return $this->safeSearch($requestFilters);
    }

    /**
     * Apply search criteria to the current repository instance (if required).
     *
     * @param array $requestFilters
     *
     * @return $this
     */
    public function safeSearch(array $requestFilters = [])
    {
        if (empty($requestFilters['search']) || !count($this->searchable)) {
            return $this;
        }

        $this->pushCriteria(new SearchCriteria($this->searchable, $requestFilters['search']));

        return $this;
    }

    /**
     * Apply ordering by given field & direction.
     *
     * @param string $column
     * @param string $direction
     *
     * @return $this
     */
    public function safeOrderBy(string $column, string $direction = 'asc')
    {
        if (!$column) {
            return $this;
        }

        return $this->orderBy($column, $direction);
    }
}

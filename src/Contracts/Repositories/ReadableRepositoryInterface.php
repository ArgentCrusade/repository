<?php

namespace ArgentCrusade\Repository\Contracts\Repositories;

use ArgentCrusade\Repository\Contracts\Criterias\CriteriaInterface;
use Illuminate\Support\Collection;

interface ReadableRepositoryInterface
{
    /**
     * Get the criteria list.
     *
     * @return Collection
     */
    public function getCriteriaStack();

    /**
     * Push given criteria to the repository's criterias list.
     *
     * @param CriteriaInterface $criteria
     *
     * @return ReadableRepositoryInterface
     */
    public function pushCriteria(CriteriaInterface $criteria);

    /**
     * Remove given criteria from the repository's criterias list.
     *
     * @param CriteriaInterface|string $criteria
     *
     * @return ReadableRepositoryInterface
     */
    public function removeCriteria($criteria);

    /**
     * Reset repository's criterias list.
     *
     * @return ReadableRepositoryInterface
     */
    public function resetCriteria();

    /**
     * Set the skipped criteria flag.
     *
     * @param bool $flag
     *
     * @return ReadableRepositoryInterface
     */
    public function skipCriteria(bool $flag);

    /**
     * Get results by the given criteria.
     *
     * @param CriteriaInterface $criteria
     * @param array             $columns  = ['*']
     *
     * @return mixed
     */
    public function getByCriteria(CriteriaInterface $criteria, array $columns = ['*']);

    /**
     * Get the items from the current query.
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function get(array $columns = ['*']);

    /**
     * Get all records from the repository.
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function all(array $columns = ['*']);

    /**
     * Pluck by the given column.
     *
     * @param string $column
     * @param string $key    = null
     *
     * @return mixed
     */
    public function pluck(string $column, string $key = null);

    /**
     * Get the first result for the current query.
     *
     * @param array $columns
     *
     * @return mixed
     */
    public function first(array $columns = ['*']);

    /**
     * Find record by the given ID.
     *
     * @param int|string|mixed $id
     * @param array            $columns
     *
     * @return mixed
     */
    public function find($id, array $columns = ['*']);

    /**
     * Find results by the given field-value pair.
     *
     * @param string $field
     * @param mixed  $value
     * @param array  $columns
     *
     * @return mixed
     */
    public function findByField(string $field, $value, array $columns = ['*']);

    /**
     * Find results by the given multiple fields.
     *
     * @param array $where
     * @param array $columns
     *
     * @return mixed
     */
    public function findWhere(array $where, array $columns = ['*']);

    /**
     * Find results by the given field values.
     *
     * @param string $field
     * @param array  $values
     * @param array  $columns
     *
     * @return mixed
     */
    public function findWhereIn(string $field, array $values, array $columns = ['*']);

    /**
     * Find results by excluding given field values.
     *
     * @param string $field
     * @param array  $values
     * @param array  $columns
     *
     * @return mixed
     */
    public function findWhereNotIn(string $field, array $values, array $columns = ['*']);

    /**
     * Apply ordering.
     *
     * @param string $column
     * @param string $direction
     *
     * @return ReadableRepositoryInterface
     */
    public function orderBy(string $column, $direction = 'asc');

    /**
     * Include given relations.
     *
     * @param string|array $relations
     *
     * @return ReadableRepositoryInterface
     */
    public function with($relations);

    /**
     * Apply relation condition.
     *
     * @param string   $relation
     * @param callable $closure
     *
     * @return ReadableRepositoryInterface
     */
    public function whereHas(string $relation, callable $closure);
}

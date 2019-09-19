<?php

namespace ArgentCrusade\Repository;

use ArgentCrusade\Repository\Contracts\Criterias\CriteriaInterface;
use ArgentCrusade\Repository\Contracts\Repositories\ReadableRepositoryInterface;
use ArgentCrusade\Repository\Contracts\Repositories\WriteableRepositoryInterface;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

abstract class EloquentRepository extends BaseRepository implements WriteableRepositoryInterface, ReadableRepositoryInterface
{
    /** @var Container */
    protected $container;

    /** @var Model|null */
    protected $model;

    /** @var Builder|null */
    protected $builder;

    /** @var Collection|null */
    protected $criteriaStack;

    /** @var bool */
    protected $criteriaSkipped = false;

    /**
     * EloquentRepository constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->bootIfNotBooted();
    }

    /**
     * Get the repository model class name.
     *
     * @return string
     */
    abstract public function model();

    /**
     * Get the container instance.
     *
     * @return Container
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * Get the repository model instance.
     *
     * @param bool $fresh = false
     *
     * @return Model
     */
    protected function getModel(bool $fresh = false)
    {
        if (is_null($this->model) || $fresh) {
            $this->model = $this->container->make($this->model());
        }

        return $this->model;
    }

    /**
     * Get the fresh Eloquent builder instance.
     *
     * @return Builder
     */
    protected function getNewBuilder()
    {
        return $this->getModel()->newQuery();
    }

    /**
     * Reset Eloquent builder instance.
     *
     * @return Builder
     */
    protected function resetBuilder()
    {
        $this->builder = $this->getNewBuilder();

        return $this->builder;
    }

    /**
     * Get the Eloquent builder instance.
     *
     * @param bool $fresh = false
     *
     * @return Builder
     */
    protected function getBuilder(bool $fresh = false)
    {
        if (is_null($this->builder) || $fresh) {
            return $this->resetBuilder();
        }

        return $this->builder;
    }

    /**
     * Get the Eloquent builder instance wit applied criteria.
     *
     * @param bool $freshBuilder = false
     *
     * @return Builder
     */
    protected function getCriteriaBuilder(bool $freshBuilder = false)
    {
        $builder = $this->getBuilder($freshBuilder);

        if (!$this->criteriaSkipped && count($this->getCriteriaStack()) > 0) {
            $this->getCriteriaStack()->each->apply($builder);
        }

        return $builder;
    }

    /**
     * Apply given "where" conditions.
     *
     * @param Builder $builder
     * @param array   $where
     *
     * @return Builder
     */
    protected function applyConditions(Builder $builder, array $where)
    {
        collect($where)->each(function ($value, $field) use ($builder) {
            if (is_array($value)) {
                list($field, $operator, $value) = $value;

                $builder->where($field, $operator, $value);
            } else {
                $builder->where($field, '=', $value);
            }
        });

        return $builder;
    }

    /**
     * Call given closure & return given value.
     *
     * @param mixed    $value
     * @param callable $callback
     *
     * @return mixed
     */
    protected function tap($value, callable $callback)
    {
        call_user_func($callback);

        return $value;
    }

    /**
     * Get the closure for the `resetBuilder` method.
     *
     * @return \Closure
     */
    private function resetBuilderClosure()
    {
        return function () {
            $this->resetBuilder();
        };
    }

    /**
     * Get the criteria stack.
     *
     * @return Collection
     */
    public function getCriteriaStack()
    {
        if (is_null($this->criteriaStack)) {
            $this->criteriaStack = new Collection();
        }

        return $this->criteriaStack;
    }

    /**
     * Create new record with the given attributes.
     *
     * @param array $attributes
     *
     * @return Model
     */
    public function create(array $attributes = [])
    {
        $instance = $this->getModel()->newInstance($attributes);
        $instance->save();

        return $instance;
    }

    /**
     * Update record with the given ID with the given attributes.
     *
     * @param array            $attributes
     * @param int|string|mixed $id
     *
     * @throws ModelNotFoundException
     *
     * @return Model
     */
    public function update(array $attributes, $id)
    {
        $instance = $this->getNewBuilder()->findOrFail($id);
        $instance->update($attributes);

        return $instance;
    }

    /**
     * Delete record by the given ID.
     *
     * @param int|string|mixed $id
     *
     * @return bool
     */
    public function delete($id)
    {
        // Some application-level code might rely on the
        // Eloquent's `deleted` event, so we will load
        // model instance first and then delete it.

        $instance = $this->getNewBuilder()->findOrFail($id);
        $instance->delete();

        return true;
    }

    /**
     * Push given criteria to the repository's criterias list.
     *
     * @param CriteriaInterface $criteria
     *
     * @return EloquentRepository
     */
    public function pushCriteria(CriteriaInterface $criteria)
    {
        $this->getCriteriaStack()->push($criteria);

        return $this;
    }

    /**
     * Pop given criteria from the repository's criterias list.
     *
     * @param CriteriaInterface|string $criteria
     *
     * @return EloquentRepository
     */
    public function removeCriteria($criteria)
    {
        $this->criteriaStack = $this->getCriteriaStack()->reject(function (CriteriaInterface $existed) use ($criteria) {
            if (is_string($criteria)) {
                return get_class($existed) === $criteria;
            }

            return get_class($existed) === get_class($criteria);
        });

        return $this;
    }

    /**
     * Reset repository's criterias list.
     *
     * @return EloquentRepository
     */
    public function resetCriteria()
    {
        $this->criteriaStack = new Collection();

        return $this;
    }

    /**
     * Set the skipped criteria flag.
     *
     * @param bool $flag
     *
     * @return EloquentRepository
     */
    public function skipCriteria(bool $flag)
    {
        $this->criteriaSkipped = $flag;

        return $this;
    }

    /**
     * Get results by the given criteria.
     *
     * @param CriteriaInterface $criteria
     * @param array             $columns  = ['*']
     *
     * @return Collection
     */
    public function getByCriteria(CriteriaInterface $criteria, array $columns = ['*'])
    {
        /** @var static $repository */
        $repository = $this->getContainer()->make(static::class);

        return $repository->pushCriteria($criteria)->get();
    }

    /**
     * Set the relationships that should be eager loaded.
     *
     * @param string|array $relations
     *
     * @return EloquentRepository
     */
    public function with($relations)
    {
        $this->getBuilder()->with($relations);

        return $this;
    }

    /**
     * Add subselect queries to count the relations.
     *
     * @param string|array $relations
     *
     * @return EloquentRepository
     */
    public function withCount($relations)
    {
        $this->getBuilder()->withCount($relations);

        return $this;
    }

    /**
     * Load trashed models.
     *
     * @return EloquentRepository
     */
    public function withTrashed()
    {
        $this->getBuilder()->withTrashed();

        return $this;
    }

    /**
     * Load only trashed models.
     *
     * @return EloquentRepository
     */
    public function onlyTrashed()
    {
        $this->getBuilder()->onlyTrashed();

        return $this;
    }

    /**
     * Apply relation condition.
     *
     * @param string   $relation
     * @param callable $closure
     *
     * @return EloquentRepository
     */
    public function whereHas(string $relation, callable $closure)
    {
        $this->getBuilder()->whereHas($relation, $closure);

        return $this;
    }

    /**
     * Get the items from the current query.
     *
     * @param array $columns
     *
     * @return Collection
     */
    public function get(array $columns = ['*'])
    {
        return $this->tap(
            $this->getCriteriaBuilder()->get($columns),
            $this->resetBuilderClosure()
        );
    }

    /**
     * Get all records from the repository.
     *
     * @param array $columns
     *
     * @return Collection
     */
    public function all(array $columns = ['*'])
    {
        return $this->getNewBuilder()->get($columns);
    }

    /**
     * Get the first result for the current query.
     *
     * @param array $columns
     *
     * @return Model
     */
    public function first(array $columns = ['*'])
    {
        return $this->tap(
            $this->getCriteriaBuilder()->first($columns),
            $this->resetBuilderClosure()
        );
    }


    /**
     * Get the first result for the current query or throw exception.
     *
     * @param array $columns
     *
     * @throws ModelNotFoundException
     *
     * @return Model
     */
    public function firstOrFail(array $columns = ['*'])
    {
        return $this->tap(
            $this->getCriteriaBuilder()->firstOrFail($columns),
            $this->resetBuilderClosure()
        );
    }

    /**
     * Find record by the given ID.
     *
     * @param int|string|mixed $id
     * @param array            $columns
     *
     * @return Model
     */
    public function find($id, array $columns = ['*'])
    {
        return $this->tap(
            $this->getCriteriaBuilder()->find($id, $columns),
            $this->resetBuilderClosure()
        );
    }

    /**
     * Find record by the given ID or throw exception.
     *
     * @param int|string|mixed $id
     * @param array            $columns
     *
     * @throws ModelNotFoundException
     *
     * @return Model
     */
    public function findOrFail($id, array $columns = ['*'])
    {
        return $this->tap(
            $this->getCriteriaBuilder()->findOrFail($id, $columns),
            $this->resetBuilderClosure()
        );
    }

    /**
     * Find results by the given field-value pair.
     *
     * @param string $field
     * @param mixed  $value
     * @param array  $columns
     *
     * @return Collection
     */
    public function findByField(string $field, $value, array $columns = ['*'])
    {
        return $this->findWhere([$field => $value], $columns);
    }

    /**
     * Find results by the given multiple fields.
     *
     * @param array $where
     * @param array $columns
     *
     * @return Collection
     */
    public function findWhere(array $where, array $columns = ['*'])
    {
        return $this->tap(
            $this->applyConditions($this->getCriteriaBuilder(), $where)->get($columns),
            $this->resetBuilderClosure()
        );
    }

    /**
     * Find results by the given field values.
     *
     * @param string $field
     * @param array  $values
     * @param array  $columns
     *
     * @return Collection
     */
    public function findWhereIn(string $field, array $values, array $columns = ['*'])
    {
        return $this->tap(
            $this->getCriteriaBuilder()->whereIn($field, $values)->get($columns),
            $this->resetBuilderClosure()
        );
    }

    /**
     * Find results by excluding given field values.
     *
     * @param string $field
     * @param array  $values
     * @param array  $columns
     *
     * @return Collection
     */
    public function findWhereNotIn(string $field, array $values, array $columns = ['*'])
    {
        return $this->tap(
            $this->getCriteriaBuilder()->whereNotIn($field, $values)->get($columns),
            $this->resetBuilderClosure()
        );
    }

    /**
     * Pluck by the given column.
     *
     * @param string $column
     * @param string $key    = null
     *
     * @return Collection
     */
    public function pluck(string $column, string $key = null)
    {
        return $this->tap(
            $this->getCriteriaBuilder()->pluck($column, $key),
            $this->resetBuilderClosure()
        );
    }

    /**
     * Apply ordering.
     *
     * @param string $column
     * @param string $direction
     *
     * @return ReadableRepositoryInterface
     */
    public function orderBy(string $column, $direction = 'asc')
    {
        $this->getCriteriaBuilder()->orderBy($column, $direction);

        return $this;
    }

    /**
     * Create new paginator instance from the current query.
     *
     * @param int|null $perPage
     * @param array    $columns
     * @param string   $pageName
     * @param int|null $page
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = null, array $columns = ['*'], string $pageName = 'page', int $page = null)
    {
        return $this->tap(
            $this->getCriteriaBuilder()->paginate($perPage, $columns, $pageName, $page),
            $this->resetBuilderClosure()
        );
    }

    /**
     * Create new simple paginator from the current query.
     *
     * @param int|null $perPage
     * @param array    $columns
     * @param string   $pageName
     * @param int|null $page
     *
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate(int $perPage = null, array $columns = ['*'], string $pageName = 'page', int $page = null)
    {
        return $this->tap(
            $this->getCriteriaBuilder()->simplePaginate($perPage, $columns, $pageName, $page),
            $this->resetBuilderClosure()
        );
    }
}

<?php

namespace ArgentCrusade\Repository\Criterias\Common;

use ArgentCrusade\Repository\Contracts\Criterias\CacheableCriteriaInterface;

class ExactValueCriteria implements CacheableCriteriaInterface
{
    /** @var string */
    protected $column;

    /** @var mixed */
    protected $value;

    /**
     * ExactValueCriteria constructor.
     *
     * @param string $column
     * @param mixed  $value
     */
    public function __construct(string $column, $value)
    {
        $this->column = $column;
        $this->value = $value;
    }

    /**
     * Get cache hash for the current criteria.
     *
     * @return array
     */
    public function getCacheHash(): string
    {
        return md5($this->column.'-'.$this->value);
    }

    /**
     * Apply the given criteria.
     *
     * @param \Illuminate\Database\Eloquent\Builder $model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply($model)
    {
        return $model->where($this->column, $this->value);
    }
}

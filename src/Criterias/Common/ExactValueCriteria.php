<?php

namespace ArgentCrusade\Repository\Criterias\Common;

use ArgentCrusade\Repository\Contracts\Criterias\CriteriaInterface;

class ExactValueCriteria implements CriteriaInterface
{
    /** @var string */
    protected $column;

    /** @var mixed */
    protected $value;

    /**
     * ExactValueCriteria constructor.
     *
     * @param string $column
     * @param mixed $value
     */
    public function __construct(string $column, $value)
    {
        $this->column = $column;
        $this->value = $value;
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

<?php

namespace ArgentCrusade\Repository\Criterias\Common;

use ArgentCrusade\Repository\Contracts\Criterias\CriteriaInterface;
use Illuminate\Database\Eloquent\Builder;

class SearchCriteria implements CriteriaInterface
{
    /**
     * @var array
     */
    protected $columns;

    /**
     * @var string
     */
    protected $query;

    /**
     * SearchCriteria constructor.
     *
     * @param array  $columns
     * @param string $query
     */
    public function __construct(array $columns, string $query)
    {
        $this->columns = $columns;
        $this->query = $query;
    }

    /**
     * @param Builder $model
     *
     * @return Builder
     */
    public function apply($model)
    {
        if (!$this->query) {
            return $model;
        }

        return $model->where(function (Builder $query) {
            foreach ($this->columns as $column) {
                $query->orWhere($column, 'LIKE', '%'.$this->query.'%');
            }
        });
    }
}

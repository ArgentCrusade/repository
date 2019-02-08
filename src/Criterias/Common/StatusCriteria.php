<?php

namespace ArgentCrusade\Repository\Criterias\Common;

use ArgentCrusade\Repository\Contracts\Criterias\CriteriaInterface;
use Illuminate\Database\Eloquent\Builder;

class StatusCriteria implements CriteriaInterface
{
    /**
     * @var array
     */
    protected $statuses;

    /**
     * StatusCriteria constructor.
     *
     * @param array|string $statuses
     */
    public function __construct($statuses)
    {
        $this->statuses = collect(array_wrap($statuses))
            ->reject(function ($status) {
                return empty($status);
            })
            ->toArray();
    }

    /**
     * Apply the criteria.
     *
     * @param Builder $model
     *
     * @return Builder
     */
    public function apply($model)
    {
        if (!$this->statuses) {
            return $model;
        }

        return $model->whereIn('status', $this->statuses);
    }
}

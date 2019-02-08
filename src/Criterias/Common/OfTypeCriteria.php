<?php

namespace ArgentCrusade\Repository\Criterias\Common;

use ArgentCrusade\Repository\Contracts\Criterias\CriteriaInterface;
use Illuminate\Database\Eloquent\Builder;

class OfTypeCriteria implements CriteriaInterface
{
    /**
     * @var string
     */
    protected $type;

    /**
     * OfTypeCriteria constructor.
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        $this->type = $type;
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
        if (!$this->type) {
            return $model;
        }

        return $model->where('type', $this->type);
    }
}

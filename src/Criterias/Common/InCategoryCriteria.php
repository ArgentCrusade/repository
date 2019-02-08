<?php

namespace ArgentCrusade\Repository\Criterias\Common;

use ArgentCrusade\Repository\Contracts\Criterias\CriteriaInterface;
use Illuminate\Database\Eloquent\Builder;

class InCategoryCriteria implements CriteriaInterface
{
    /**
     * @var array
     */
    protected $categories;

    /**
     * InCategoryCriteria constructor.
     *
     * @param string|array $categories
     */
    public function __construct($categories)
    {
        $this->categories = array_wrap($categories);
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
        if (!$this->categories) {
            return $model;
        }

        return $model->whereIn('category_id', $this->categories);
    }
}

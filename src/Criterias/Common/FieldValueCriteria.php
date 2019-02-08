<?php

namespace ArgentCrusade\Repository\Criterias\Common;

use ArgentCrusade\Repository\Contracts\Criterias\CriteriaInterface;
use Illuminate\Database\Eloquent\Builder;

class FieldValueCriteria implements CriteriaInterface
{
    /**
     * @var string
     */
    protected $field;

    /**
     * @var array
     */
    protected $ids;

    /**
     * OwnedByCriteria constructor.
     *
     * @param string          $field
     * @param array|int|mixed $ids
     */
    public function __construct(string $field, $ids)
    {
        $this->field = $field;
        $this->ids = collect($ids)
            ->reject(function ($id) {
                return empty($id);
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
        if (!$this->ids) {
            return $model;
        }

        return $model->whereIn($this->field, $this->ids);
    }
}

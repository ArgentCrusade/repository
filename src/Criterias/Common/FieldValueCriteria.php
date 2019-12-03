<?php

namespace ArgentCrusade\Repository\Criterias\Common;

use ArgentCrusade\Repository\Contracts\Criterias\CacheableCriteriaInterface;
use Illuminate\Database\Eloquent\Builder;

class FieldValueCriteria implements CacheableCriteriaInterface
{
    /** @var string */
    protected $field;

    /** @var array */
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
        $this->ids = array_filter(is_array($ids) ? $ids : [$ids], function ($item) {
            return !empty($item);
        });
    }

    /**
     * Get cache hash for the current criteria.
     *
     * @return array
     */
    public function getCacheHash(): string
    {
        return md5($this->field.'-'.implode(',', $this->ids));
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

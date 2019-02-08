<?php

namespace ArgentCrusade\Repository\Contracts\Criterias;

interface CriteriaInterface
{
    /**
     * Apply the given criteria.
     *
     * @param \Illuminate\Database\Eloquent\Builder $model
     *
     * @return \Illuminate\Database\Eloquent\Builder|void
     */
    public function apply($model);
}

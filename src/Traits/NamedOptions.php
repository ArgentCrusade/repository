<?php

namespace ArgentCrusade\Repository\Traits;

use ArgentCrusade\Repository\AbstractRepository;
use Illuminate\Database\Eloquent\Model;

trait NamedOptions
{
    /**
     * Get the models list as select field options.
     *
     * @param callable $callback = null
     *
     * @return array
     */
    public function options(callable $callback = null)
    {
        /** @var AbstractRepository $repository */
        $repository = app(static::class);

        if (is_callable($callback)) {
            call_user_func_array($callback, [$repository]);
        }

        return $repository->get()
            ->mapWithKeys(function (Model $model) {
                return [
                    $model->getKey() => $model->getAttribute($this->optionLabelAttribute()),
                ];
            })
            ->toArray();
    }

    /**
     * Get the attribute name that will be used as option label.
     *
     * @return string
     */
    protected function optionLabelAttribute()
    {
        return 'name';
    }
}

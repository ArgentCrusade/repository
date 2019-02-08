<?php

namespace ArgentCrusade\Repository\Contracts;

use ArgentCrusade\Repository\AbstractRepository;

interface RepositoryFilterInterface
{
    /**
     * Apply filter to the given repository.
     *
     * @param AbstractRepository $repository
     * @param mixed              $value
     *
     * @return mixed
     */
    public function apply(AbstractRepository $repository, $value);
}

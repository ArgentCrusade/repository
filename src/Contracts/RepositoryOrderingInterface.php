<?php

namespace ArgentCrusade\Repository\Contracts;

use ArgentCrusade\Repository\AbstractRepository;

interface RepositoryOrderingInterface
{
    /**
     * Apply complex ordering criteria to the given repository.
     *
     * @param AbstractRepository $repository
     * @param string             $column
     * @param string             $direction
     *
     * @return AbstractRepository
     */
    public function apply(AbstractRepository $repository, $column, $direction);
}

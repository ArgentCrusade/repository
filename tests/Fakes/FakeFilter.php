<?php

namespace ArgentCrusade\Repository\Tests\Fakes;

use ArgentCrusade\Repository\AbstractRepository;
use ArgentCrusade\Repository\Contracts\RepositoryFilterInterface;

class FakeFilter implements RepositoryFilterInterface
{
    public function apply(AbstractRepository $repository, $value)
    {
        return $repository;
    }
}

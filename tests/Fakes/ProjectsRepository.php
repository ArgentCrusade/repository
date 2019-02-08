<?php

namespace ArgentCrusade\Repository\Tests\Fakes;

use ArgentCrusade\Repository\AbstractRepository;

class ProjectsRepository extends AbstractRepository
{
    use FakeTrait;

    protected $searchable = ['name'];

    public function model()
    {
        return Project::class;
    }
}

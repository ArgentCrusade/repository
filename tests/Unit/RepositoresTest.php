<?php

namespace ArgentCrusade\Repository\Tests\Unit;

use ArgentCrusade\Repository\Criterias\Common\SearchCriteria;
use ArgentCrusade\Repository\Tests\Fakes\FakeFilter;
use ArgentCrusade\Repository\Tests\Fakes\ProjectsRepository;
use ArgentCrusade\Repository\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RepositoresTest extends TestCase
{
    use RefreshDatabase;

    public function testRepositoryShouldBootTraits()
    {
        /** @var ProjectsRepository $repository */
        $repository = app(ProjectsRepository::class);
        $this->assertTrue($repository->fakeTraitBooted);
    }

    public function testRepositoryFilters()
    {
        /** @var ProjectsRepository $repository */
        $repository = app(ProjectsRepository::class);
        $filter = \Mockery::mock(FakeFilter::class.'[apply]');
        $filter->shouldReceive('apply')->once()->andReturn($repository);

        $repository->applyFilters(['fake' => 'fake'], ['fake' => $filter]);
    }

    public function testRepositorySearch()
    {
        /** @var ProjectsRepository $repository */
        $repository = app(ProjectsRepository::class);
        $this->assertSame(0, count($repository->getCriteriaStack()));

        $repository->safeSearch(['search' => 'test']);

        $this->assertSame(1, count($repository->getCriteriaStack()));
        $this->assertInstanceOf(SearchCriteria::class, $repository->getCriteriaStack()->first());
    }
}

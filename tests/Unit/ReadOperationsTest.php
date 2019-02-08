<?php

namespace ArgentCrusade\Repository\Tests\Unit;

use ArgentCrusade\Repository\Tests\Fakes\FirstLevelDomainCriteria;
use ArgentCrusade\Repository\Tests\Fakes\Project;
use ArgentCrusade\Repository\Tests\Fakes\ProjectsRepository;
use ArgentCrusade\Repository\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReadOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function testItShouldFindModelsByPrimaryKey()
    {
        $project = factory(Project::class)->create();
        /** @var ProjectsRepository $repository */
        $repository = app(ProjectsRepository::class);
        $foundProject = $repository->find($project->id);

        $this->assertInstanceOf(Project::class, $foundProject);
        $this->assertSame($project->id, $foundProject->id);
    }

    public function testItShouldFindModelsByCriterias()
    {
        $first = factory(Project::class)->create(['name' => 'example.org']);
        $second = factory(Project::class)->create(['name' => 'example.com']);

        /** @var Collection $orgItems */
        $orgItems = app(ProjectsRepository::class)->pushCriteria(new FirstLevelDomainCriteria('.org'))->get();
        $this->assertInstanceOf(Collection::class, $orgItems);
        $this->assertSame(1, count($orgItems));
        $this->assertSame($first->id, $orgItems->first()->id);

        /** @var Collection $comItems */
        $comItems = app(ProjectsRepository::class)->pushCriteria(new FirstLevelDomainCriteria('.com'))->get();
        $this->assertInstanceOf(Collection::class, $comItems);
        $this->assertSame(1, count($comItems));
        $this->assertSame($second->id, $comItems->first()->id);
    }

    public function testItShouldIgnoreCriteriasInTheAllMethod()
    {
        factory(Project::class)->create(['name' => 'example.org']);
        factory(Project::class)->create(['name' => 'example.com']);

        $items = app(ProjectsRepository::class)->pushCriteria(new FirstLevelDomainCriteria('.org'))->all();
        $this->assertInstanceOf(Collection::class, $items);
        $this->assertSame(2, count($items));
    }

    public function testItShouldPaginateModels()
    {
        factory(Project::class, 25)->create();

        /** @var ProjectsRepository $repository */
        $repository = app(ProjectsRepository::class);
        /** @var LengthAwarePaginator $paginator */
        $paginator = $repository->paginate(15);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertSame(15, $paginator->count());
        $this->assertSame(25, $paginator->total());
        $this->assertSame(1, $paginator->currentPage());
        $this->assertSame(true, $paginator->hasMorePages());
    }

    public function testItShouldUseCacheForTheSameQueries()
    {
        $project = factory(Project::class)->create();

        $queriesCount = 0;

        DB::listen(function () use (&$queriesCount) {
            ++$queriesCount;
        });

        /** @var ProjectsRepository $repository */
        $repository = app(ProjectsRepository::class);
        $this->assertTrue($repository->cacheEnabled());
        $this->assertFalse($repository->cacheSkipped());

        $foundProject = $repository->find($project->id);
        $this->assertInstanceOf(Project::class, $foundProject);
        $this->assertSame($project->id, $foundProject->id);

        $foundProject = $repository->find($project->id);
        $this->assertInstanceOf(Project::class, $foundProject);
        $this->assertSame($project->id, $foundProject->id);

        $this->assertSame(1, $queriesCount);
    }

    public function testItShouldUseCacheForCriterias()
    {
        factory(Project::class)->create(['name' => 'example.org']);
        factory(Project::class)->create(['name' => 'example.com']);

        $queriesCount = 0;

        DB::listen(function () use (&$queriesCount) {
            ++$queriesCount;
        });

        app(ProjectsRepository::class)->pushCriteria(new FirstLevelDomainCriteria('.org'))->get();
        app(ProjectsRepository::class)->pushCriteria(new FirstLevelDomainCriteria('.com'))->get();

        app(ProjectsRepository::class)->pushCriteria(new FirstLevelDomainCriteria('.org'))->get();
        app(ProjectsRepository::class)->pushCriteria(new FirstLevelDomainCriteria('.com'))->get();

        app(ProjectsRepository::class)->pushCriteria(new FirstLevelDomainCriteria('.org'))->get();
        app(ProjectsRepository::class)->pushCriteria(new FirstLevelDomainCriteria('.com'))->get();

        $this->assertSame(2, $queriesCount); // (1 for select) * 2
    }

    public function testItShouldUseCacheForGetByCriteria()
    {
        factory(Project::class)->create(['name' => 'example.org']);
        factory(Project::class)->create(['name' => 'example.com']);

        $queriesCount = 0;

        DB::listen(function () use (&$queriesCount) {
            ++$queriesCount;
        });

        app(ProjectsRepository::class)->getByCriteria(new FirstLevelDomainCriteria('.org'));
        app(ProjectsRepository::class)->getByCriteria(new FirstLevelDomainCriteria('.com'));

        app(ProjectsRepository::class)->getByCriteria(new FirstLevelDomainCriteria('.org'));
        app(ProjectsRepository::class)->getByCriteria(new FirstLevelDomainCriteria('.com'));

        app(ProjectsRepository::class)->getByCriteria(new FirstLevelDomainCriteria('.org'));
        app(ProjectsRepository::class)->getByCriteria(new FirstLevelDomainCriteria('.com'));

        $this->assertSame(2, $queriesCount); // (1 for select) * 2
    }

    public function testItShouldFlushCacheAfterCreateOperation()
    {
        $project = factory(Project::class)->create();

        $queriesCount = 0;

        DB::listen(function () use (&$queriesCount) {
            ++$queriesCount;
        });

        /** @var ProjectsRepository $repository */
        $repository = app(ProjectsRepository::class);
        $this->assertTrue($repository->cacheEnabled());
        $this->assertFalse($repository->cacheSkipped());

        $foundProject = $repository->find($project->id);
        $this->assertInstanceOf(Project::class, $foundProject);
        $this->assertSame($project->id, $foundProject->id);

        /** @var Project $newProject */
        $newProject = $repository->create(['name' => 'Hello', 'counter' => 1]);
        $this->assertInstanceOf(Project::class, $newProject);

        $foundProject = $repository->find($project->id);
        $this->assertInstanceOf(Project::class, $foundProject);
        $this->assertSame($project->id, $foundProject->id);

        $this->assertSame(3, $queriesCount); // 1 for first select, 1 for insert, 1 for new select.
    }

    public function testItShouldFlushCacheAfterUpdateOperation()
    {
        $project = factory(Project::class)->create(['name' => 'Hello']);
        $this->assertSame('Hello', $project->name);

        $queriesCount = 0;

        DB::listen(function () use (&$queriesCount) {
            ++$queriesCount;
        });

        /** @var ProjectsRepository $repository */
        $repository = app(ProjectsRepository::class);
        $this->assertTrue($repository->cacheEnabled());
        $this->assertFalse($repository->cacheSkipped());

        $foundProject = $repository->find($project->id);
        $this->assertInstanceOf(Project::class, $foundProject);
        $this->assertSame($project->id, $foundProject->id);
        $this->assertSame($project->name, $foundProject->name);

        /** @var Project $updatedProject */
        $updatedProject = $repository->update(['name' => 'Hello, World!'], $foundProject->id);
        $this->assertInstanceOf(Project::class, $updatedProject);
        $this->assertSame('Hello, World!', $updatedProject->name);

        $foundProject = $repository->find($project->id);
        $this->assertInstanceOf(Project::class, $foundProject);
        $this->assertSame($project->id, $foundProject->id);
        $this->assertSame($updatedProject->name, $foundProject->name);

        $this->assertSame(4, $queriesCount); // 1 for first select, 1 for select before update, 1 for update, 1 for new select.
    }

    public function testItShouldFlushCacheAfterDeleteOperation()
    {
        $project = factory(Project::class)->create();

        $queriesCount = 0;

        DB::listen(function () use (&$queriesCount) {
            ++$queriesCount;
        });

        /** @var ProjectsRepository $repository */
        $repository = app(ProjectsRepository::class);
        $this->assertTrue($repository->cacheEnabled());
        $this->assertFalse($repository->cacheSkipped());

        $foundProject = $repository->find($project->id);
        $this->assertInstanceOf(Project::class, $foundProject);
        $this->assertSame($project->id, $foundProject->id);

        /** @var Project $newProject */
        $newProject = $repository->create(['name' => 'Hello', 'counter' => 1]);
        $this->assertInstanceOf(Project::class, $newProject);

        $repository->delete($newProject->id);

        $foundProject = $repository->find($project->id);
        $this->assertInstanceOf(Project::class, $foundProject);
        $this->assertSame($project->id, $foundProject->id);

        $this->assertSame(5, $queriesCount); // 1 for first select, 1 for insert, 1 for select before delete, 1 for delete, 1 for new select.

        $this->assertNull(Project::find($newProject->id));
    }

    public function testItShouldNotUseSameCacheEntriesForDifferentPages()
    {
        factory(Project::class, 25)->create();

        $queriesCount = 0;

        DB::listen(function () use (&$queriesCount) {
            ++$queriesCount;
        });

        /** @var ProjectsRepository $repository */
        $repository = app(ProjectsRepository::class);

        $firstPaginator = $repository->paginate(15, ['*'], 'page', 1);
        $this->assertInstanceOf(LengthAwarePaginator::class, $firstPaginator);
        $this->assertSame(15, $firstPaginator->count());
        $this->assertSame(25, $firstPaginator->total());
        $this->assertSame(1, $firstPaginator->currentPage());
        $this->assertTrue($firstPaginator->hasMorePages());

        $secondPaginator = $repository->paginate(15, ['*'], 'page', 2);
        $this->assertInstanceOf(LengthAwarePaginator::class, $secondPaginator);
        $this->assertSame(10, $secondPaginator->count());
        $this->assertSame(25, $secondPaginator->total());
        $this->assertSame(2, $secondPaginator->currentPage());
        $this->assertFalse($secondPaginator->hasMorePages());

        $repository->paginate(15, ['*'], 'page', 1);
        $repository->paginate(15, ['*'], 'page', 2);
        $repository->paginate(15, ['*'], 'page', 1);
        $repository->paginate(15, ['*'], 'page', 2);

        $this->assertSame(4, $queriesCount); // (1 for aggregate, 1 for select) * 2

        $repository->create(['name' => 'Hello', 'counter' => 1]);

        $firstPaginator = $repository->paginate(15, ['*'], 'page', 1);
        $this->assertInstanceOf(LengthAwarePaginator::class, $firstPaginator);
        $this->assertSame(15, $firstPaginator->count());
        $this->assertSame(26, $firstPaginator->total());
        $this->assertSame(1, $firstPaginator->currentPage());
        $this->assertTrue($firstPaginator->hasMorePages());

        $secondPaginator = $repository->paginate(15, ['*'], 'page', 2);
        $this->assertInstanceOf(LengthAwarePaginator::class, $secondPaginator);
        $this->assertSame(11, $secondPaginator->count());
        $this->assertSame(26, $secondPaginator->total());
        $this->assertSame(2, $secondPaginator->currentPage());
        $this->assertFalse($secondPaginator->hasMorePages());

        $repository->paginate(15, ['*'], 'page', 1);
        $repository->paginate(15, ['*'], 'page', 2);

        $this->assertSame(9, $queriesCount); // 4 + ((1 for aggregate, 1 for select) * 2) + 1 for insert
    }
}

<?php

declare(strict_types=1);

namespace ArgentCrusade\Repository\Tests\Unit;

use ArgentCrusade\Repository\Helpers\CacheableEloquentRepository;
use ArgentCrusade\Repository\Tests\Fakes\FirstLevelDomainCriteria;
use ArgentCrusade\Repository\Tests\Fakes\ProjectsRepository;
use ArgentCrusade\Repository\Tests\TestCase;
use Illuminate\Contracts\Container\Container;
use Illuminate\Http\Request;

class CacheableEloquentRepositoryTest extends TestCase
{
    public function testItShouldGenerateSameKeysForSameCriteriaStacks()
    {
        $repository = new CacheableEloquentRepository(app(Container::class), ProjectsRepository::class);
        $firstStack = collect([new FirstLevelDomainCriteria('example.org')]);
        $secondStack = collect([new FirstLevelDomainCriteria('example.org')]);

        $this->mockRequest();

        $this->assertSame(
            $repository->generateCacheKey($firstStack, 'example'),
            $repository->generateCacheKey($secondStack, 'example')
        );
    }

    public function testItShouldGenerateDifferentKeysForDifferentCriteriaStacks()
    {
        $repository = new CacheableEloquentRepository(app(Container::class), ProjectsRepository::class);
        $firstStack = collect([new FirstLevelDomainCriteria('example.org')]);
        $secondStack = collect([new FirstLevelDomainCriteria('example.com')]);

        $this->mockRequest();

        $this->assertNotSame(
            $repository->generateCacheKey($firstStack, 'example'),
            $repository->generateCacheKey($secondStack, 'example')
        );
    }

    private function mockRequest()
    {
        $request = \Mockery::mock(Request::class);
        $request->shouldReceive('fullUrl')->andReturn('https://example.org');
        app()->instance(Request::class, $request);
    }
}

<?php

namespace ArgentCrusade\Repository\Tests;

use ArgentCrusade\Repository\Tests\Migrations\CreateProjectsTable;
use ArgentCrusade\Repository\Tests\Fakes\Project;
use Faker\Generator;
use Illuminate\Database\Eloquent\Factory;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        (new CreateProjectsTable())->up();

        $factory = app(Factory::class);
        $factory->define(Project::class, function (Generator $faker) {
            return [
                'name' => $faker->company,
                'counter' => rand(),
            ];
        });
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }
}

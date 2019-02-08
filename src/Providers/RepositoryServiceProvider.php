<?php

namespace ArgentCrusade\Repository\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $configPath = realpath(__DIR__.'/../../config/repository.php');

        $this->publishes([
            $configPath => config_path('repository.php'),
        ]);

        $this->mergeConfigFrom($configPath, 'repository');
    }
}

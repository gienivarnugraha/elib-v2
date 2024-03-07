<?php

namespace Tests\Traits;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

trait CreateApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        $this->registerRepositories($app);

        $this->afterApplicationCreated(function () {
            $this->configureFactoryResolver();
        });

        return $app;
    }

    /**
     * Register the test repositories
     *
     * @param  Application  $app
     * @return void
     */
    private function registerRepositories($app)
    {
        // $app->bind(
        //     \Tests\Fixtures\EventRepository::class,
        //     \Tests\Fixtures\EventRepositoryEloquent::class
        // );
    }

    /**
     * Configure tests factory resolver
     *
     * @return void
     */
    protected function configureFactoryResolver()
    {
        Factory::guessFactoryNamesUsing(function ($modelName) {
            $appNamespace = 'App\\';
            $testNameSpace = __NAMESPACE__.'\\';
            $laravelFactoriesNamespace = 'Database\\Factories\\';
            $testsFactoriesNamespace = 'Tests\\Factories\\';

            if (Str::startsWith($modelName, $testNameSpace.'Fixtures\\')) {
                return $testsFactoriesNamespace.Str::after($modelName, $testNameSpace.'Fixtures\\').'Factory';
            }

            if (Str::startsWith($modelName, $appNamespace.'Models\\')) {
                $modelName = Str::after($modelName, $appNamespace.'Models\\');
            } else {
                $modelName = Str::after($modelName, $appNamespace);
            }

            return $laravelFactoriesNamespace.$modelName.'Factory';
        });
    }
}

<?php

namespace Seatplus\BroadcastHub\Tests;

use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Inertia;
use Inertia\ServiceProvider as InertiaServiceProviderAlias;
use Orchestra\Testbench\Foundation\Application;
use Orchestra\Testbench\Foundation\Http\Kernel;
use Orchestra\Testbench\TestCase as Orchestra;
use Seatplus\Auth\AuthenticationServiceProvider;
use Seatplus\Auth\Models\User;
use Seatplus\BroadcastHub\BroadcastHubServiceProvider;
use Seatplus\Connector\ConnectorServiceProvider;
use Seatplus\Eveapi\EveapiServiceProvider;
use Seatplus\Web\Http\Middleware\Authenticate;
use Seatplus\Web\WebServiceProvider;

class TestCase extends Orchestra
{
    use LazilyRefreshDatabase;
    //use RefreshDatabase;

    //protected $loadEnvironmentVariables = false;
    //protected $enablesPackageDiscoveries = true;

    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => match (true) {
                str_starts_with($modelName, 'Seatplus\Auth') => 'Seatplus\\Auth\\Database\\Factories\\'.class_basename($modelName).'Factory',
                str_starts_with($modelName, 'Seatplus\Eveapi') => 'Seatplus\\Eveapi\\Database\\Factories\\'.class_basename($modelName).'Factory',
                str_starts_with($modelName, 'Seatplus\Tribe') => 'Seatplus\\Tribe\\Database\\Factories\\'.class_basename($modelName).'Factory',
                str_starts_with($modelName, 'Seatplus\BroadcastHub') => 'Seatplus\\BroadcastHub\\Database\\Factories\\'.class_basename($modelName).'Factory',
                default => dd('no match for '.$modelName)
            },
        );

        Inertia::setRootView('web::app');
        $this->withoutVite();
    }

    protected function getPackageProviders($app): array
    {
        return [
            WebServiceProvider::class,
            ConnectorServiceProvider::class,
            BroadcastHubServiceProvider::class,
            AuthenticationServiceProvider::class,
            EveapiServiceProvider::class,
            InertiaServiceProviderAlias::class,
        ];
    }

    protected function defineEnvironment($app)
    {

        $app['router']->aliasMiddleware('auth', Authenticate::class);

        tap($app->make('config'), function (Repository $config) {
            $config->set('database.default', 'testing');

            $mysql_config = $config->get('database.connections.mysql');

            $config->set('database.connections.testing', $mysql_config);

            $config->set('app.debug', true);
            $config->set('app.env', 'testing');
            $config->set('cache.prefix', 'seatplus_tests---');

            //Setup Inertia for package development
            $config->set('inertia.testing.page_paths', array_merge(
                $config->get('inertia.testing.page_paths', []),
                [
                    realpath(__DIR__.'/../resources/js/Pages'),
                ],
            ));
        });

        /*$app['config']->set('app.debug', true);

        // Use test User model for users provider
        $app['config']->set('auth.providers.users.model', User::class);

        $app['config']->set('cache.prefix', 'seatplus_tests---');

        //Setup Inertia for package development
        config()->set('inertia.testing.page_paths', array_merge(
            config()->get('inertia.testing.page_paths', []),
            [
                realpath(__DIR__ . '/../src/resources/js/Pages'),
            ],
        ));*/
    }

    /**
     * Resolve application HTTP Kernel implementation.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function resolveApplicationHttpKernel($app)
    {
        $app->singleton('Illuminate\Contracts\Http\Kernel', \Seatplus\BroadcastHub\Tests\Stubs\Kernel::class);
    }
}

<?php

namespace Seatplus\BroadcastHub;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Seatplus\BroadcastHub\Commands\BroadcastAllCommand;
use Seatplus\BroadcastHub\Commands\CorporationTracking\CorporationTrackingCommand;
use Seatplus\BroadcastHub\Commands\CorporationTracking\NewCorporationMemberCommand;
use Seatplus\BroadcastHub\Events\NotificationFailed;
use Seatplus\BroadcastHub\Listeners\NotificationFailedListener;

class BroadcastHubServiceProvider extends ServiceProvider
{
    /**
     * Register bindings in the container.
     *
     * @return void
     */
    public function boot()
    {
        // Publish the JS & CSS,
        $this->addPublications();

        // Add routes
        $this->loadRoutesFrom(__DIR__.'/../routes/broadcast_hub.php');

        //Add Migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/');

        // Add translations
        //$this->loadTranslationsFrom(__DIR__ . '/resources/lang', 'web');

        // Add commands
        $this->addCommands();

        // Add Event Listeners
        Event::listen(NotificationFailed::class, NotificationFailedListener::class.'@handle');

    }

    public function register()
    {
        $this->mergeConfigurations();

        $this->app->singleton(BroadcastRepository::class, function ($app) {
            return new BroadcastRepository();
        });
    }

    private function mergeConfigurations()
    {

        $this->mergeConfigFrom(
            __DIR__.'/../config/sidebar.php', 'package.sidebar.connector'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../config/permissions.php',
            'web.permissions'
        );

        $this->mergeConfigFrom(
            __DIR__.'/../config/jobs.php',
            'seatplus.updateJobs'
        );
    }

    private function addPublications()
    {
        /*
         * to publish assets one can run:
         * php artisan vendor:publish --tag=web --force
         * or use Laravel Mix to copy the folder to public repo of core.
         */
        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js'),
        ], 'web');
    }

    private function addCommands()
    {

        $this->commands([
            BroadcastAllCommand::class,
            CorporationTrackingCommand::class,
            NewCorporationMemberCommand::class,
        ]);

    }
}

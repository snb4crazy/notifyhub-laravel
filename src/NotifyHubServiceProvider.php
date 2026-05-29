<?php

namespace NotifyHub\LaravelClient;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\ServiceProvider;
use NotifyHub\LaravelClient\Contracts\NotifyHubClientInterface;
use NotifyHub\LaravelClient\Listeners\ReportExceptionToNotifyHub;

class NotifyHubServiceProvider extends ServiceProvider
{
    /**
     * Register the NotifyHub client into the service container.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/notifyhub.php', 'notifyhub');

        $this->app->singleton(NotifyHubClientInterface::class, function (Application $app) {
            return new NotifyHubClient($app->make('Illuminate\Http\Client\Factory'));
        });

        // Allow callers to resolve by class name as well
        $this->app->alias(NotifyHubClientInterface::class, NotifyHubClient::class);
    }

    /**
     * Bootstrap the package: publish config and optionally register the
     * global exception listener.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/notifyhub.php' => config_path('notifyhub.php'),
            ], 'notifyhub-config');
        }

        // Auto-report exceptions when NOTIFYHUB_AUTO_REPORT=true
        if (config('notifyhub.auto_report_exceptions', false)) {
            $this->callAfterResolving('events', function ($events) {
                $events->listen(
                    MessageLogged::class,
                    ReportExceptionToNotifyHub::class,
                );
            });
        }
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return [
            NotifyHubClientInterface::class,
            NotifyHubClient::class,
        ];
    }
}

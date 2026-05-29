<?php

namespace NotifyHub\LaravelClient\Tests;

use NotifyHub\LaravelClient\Facades\NotifyHub;
use NotifyHub\LaravelClient\NotifyHubServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            NotifyHubServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'NotifyHub' => NotifyHub::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('notifyhub.enabled', true);
        $app['config']->set('notifyhub.url', 'http://notifyhub.test');
        $app['config']->set('notifyhub.ingest_key', 'test_ingest_key');
        $app['config']->set('notifyhub.timeout', 5);
        $app['config']->set('notifyhub.retry_times', 0);
    }
}

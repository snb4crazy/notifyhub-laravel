<?php

namespace NotifyHub\LaravelClient\Facades;

use Illuminate\Support\Facades\Facade;
use NotifyHub\LaravelClient\Contracts\NotifyHubClientInterface;
use NotifyHub\LaravelClient\Data\EventPayload;
use NotifyHub\LaravelClient\NotifyHubClient;

/**
 * @method static void send(EventPayload $payload)
 * @method static void sendException(\Throwable $exception, array $context = [])
 * @method static void sendRaw(array $data)
 *
 * @see NotifyHubClient
 */
class NotifyHub extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return NotifyHubClientInterface::class;
    }
}

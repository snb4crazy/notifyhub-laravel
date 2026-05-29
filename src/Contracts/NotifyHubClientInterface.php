<?php

namespace NotifyHub\LaravelClient\Contracts;

use NotifyHub\LaravelClient\Data\EventPayload;

interface NotifyHubClientInterface
{
    /**
     * Send a raw normalized event payload to the NotifyHub server.
     */
    public function send(EventPayload $payload): void;

    /**
     * Send a Throwable as a structured NotifyHub event.
     *
     * @param  array<string, mixed>  $context
     */
    public function sendException(\Throwable $exception, array $context = []): void;

    /**
     * Send any custom structured event.
     *
     * @param  array<string, mixed>  $data
     */
    public function sendRaw(array $data): void;
}

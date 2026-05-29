<?php

namespace NotifyHub\LaravelClient\Listeners;

use Illuminate\Log\Events\MessageLogged;
use NotifyHub\LaravelClient\Contracts\NotifyHubClientInterface;
use NotifyHub\LaravelClient\Data\EventPayload;

/**
 * Optional listener that automatically forwards logged exceptions to NotifyHub.
 *
 * Enable by setting NOTIFYHUB_AUTO_REPORT=true in the sending app.
 * Only events logged at `error` level or above with an attached exception are forwarded.
 */
class ReportExceptionToNotifyHub
{
    public function __construct(protected NotifyHubClientInterface $client) {}

    /**
     * Forward the exception to NotifyHub when the log level meets the threshold.
     */
    public function handle(MessageLogged $event): void
    {
        if (! isset($event->context['exception'])) {
            return;
        }

        $exception = $event->context['exception'];

        if (! ($exception instanceof \Throwable)) {
            return;
        }

        $minLevel = strtolower((string) config('notifyhub.auto_report_min_level', 'error'));
        $levels = ['debug' => 0, 'info' => 1, 'notice' => 2, 'warning' => 3, 'error' => 4, 'critical' => 5, 'alert' => 6, 'emergency' => 7];

        if (($levels[$event->level] ?? 4) < ($levels[$minLevel] ?? 4)) {
            return;
        }

        rescue(fn () => $this->client->send(EventPayload::fromException($exception)));
    }
}

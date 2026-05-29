<?php

namespace NotifyHub\LaravelClient;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use NotifyHub\LaravelClient\Contracts\NotifyHubClientInterface;
use NotifyHub\LaravelClient\Data\EventPayload;
use NotifyHub\LaravelClient\Exceptions\NotifyHubException;

class NotifyHubClient implements NotifyHubClientInterface
{
    public function __construct(protected readonly HttpFactory $http) {}

    /**
     * Send a normalized EventPayload to the NotifyHub intake endpoint.
     *
     * @throws NotifyHubException when the server rejects the request or is unreachable.
     */
    public function send(EventPayload $payload): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        try {
            $this->buildRequest()
                ->post('/api/v1/events', $payload->toArray())
                ->throw();
        } catch (\Throwable $e) {
            throw NotifyHubException::fromHttpException($e);
        }
    }

    /**
     * Build a payload from a Throwable and send it.
     *
     * @param  array<string, mixed>  $context
     *
     * @throws NotifyHubException
     */
    public function sendException(\Throwable $exception, array $context = []): void
    {
        $this->send(EventPayload::fromException($exception, 'critical', $context));
    }

    /**
     * Send a custom free-form array that matches the NotifyHub intake contract.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws NotifyHubException
     */
    public function sendRaw(array $data): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        try {
            $this->buildRequest()
                ->post('/api/v1/events', $data)
                ->throw();
        } catch (\Throwable $e) {
            throw NotifyHubException::fromHttpException($e);
        }
    }

    /**
     * Build an authenticated HTTP client pointed at the configured NotifyHub URL.
     */
    protected function buildRequest(): PendingRequest
    {
        return $this->http
            ->baseUrl((string) config('notifyhub.url'))
            ->withHeaders([
                'X-Project-Key' => (string) config('notifyhub.ingest_key'),
                'Accept' => 'application/json',
            ])
            ->timeout((int) config('notifyhub.timeout', 5))
            ->retry((int) config('notifyhub.retry_times', 2), (int) config('notifyhub.retry_sleep_ms', 200));
    }

    /**
     * Guard all send methods: if the package is disabled, do nothing.
     */
    protected function isEnabled(): bool
    {
        return (bool) config('notifyhub.enabled', true);
    }
}

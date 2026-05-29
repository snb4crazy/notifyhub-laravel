<?php

namespace NotifyHub\LaravelClient\Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use NotifyHub\LaravelClient\Contracts\NotifyHubClientInterface;
use NotifyHub\LaravelClient\Data\EventPayload;
use NotifyHub\LaravelClient\Exceptions\NotifyHubException;
use NotifyHub\LaravelClient\Facades\NotifyHub;
use NotifyHub\LaravelClient\Tests\TestCase;

class NotifyHubClientTest extends TestCase
{
    public function test_send_posts_payload_to_the_intake_endpoint(): void
    {
        Http::fake([
            'notifyhub.test/api/v1/events' => Http::response(['status' => 'accepted'], 202),
        ]);

        $payload = new EventPayload(
            title: 'Payment failed',
            message: 'Stripe returned a card error',
            severity: 'error',
            eventType: 'payment.failed',
            application: 'billing-api',
            environment: 'production',
        );

        $this->app->make(NotifyHubClientInterface::class)->send($payload);

        Http::assertSent(function (Request $request) {
            return $request->url() === 'http://notifyhub.test/api/v1/events'
                && $request->header('X-Project-Key')[0] === 'test_ingest_key'
                && $request['title'] === 'Payment failed'
                && $request['severity'] === 'error'
                && $request['event_type'] === 'payment.failed';
        });
    }

    public function test_facade_resolves_to_the_same_instance(): void
    {
        Http::fake([
            'notifyhub.test/api/v1/events' => Http::response(['status' => 'accepted'], 202),
        ]);

        NotifyHub::sendRaw([
            'title' => 'Raw test',
            'message' => 'via facade',
            'severity' => 'info',
        ]);

        Http::assertSentCount(1);
    }

    public function test_send_is_skipped_when_package_is_disabled(): void
    {
        Http::fake();
        config(['notifyhub.enabled' => false]);

        $this->app->make(NotifyHubClientInterface::class)->sendRaw([
            'title' => 'Should not be sent',
            'message' => 'noop',
            'severity' => 'info',
        ]);

        Http::assertNothingSent();
    }

    public function test_send_exception_builds_and_dispatches_payload(): void
    {
        Http::fake([
            'notifyhub.test/api/v1/events' => Http::response(['status' => 'accepted'], 202),
        ]);

        $exception = new \RuntimeException('Disk full');

        $this->app->make(NotifyHubClientInterface::class)->sendException($exception);

        Http::assertSent(function (Request $request) {
            return str_contains($request['title'], 'RuntimeException')
                && $request['severity'] === 'critical'
                && $request['event_type'] === 'laravel.exception'
                && isset($request['sensitive_context']['trace']);
        });
    }

    public function test_send_wraps_http_failure_in_notifyhub_exception(): void
    {
        Http::fake([
            'notifyhub.test/api/v1/events' => Http::response('Server Error', 500),
        ]);

        $this->expectException(NotifyHubException::class);

        $this->app->make(NotifyHubClientInterface::class)->sendRaw([
            'title' => 'T',
            'message' => 'M',
            'severity' => 'error',
        ]);
    }
}

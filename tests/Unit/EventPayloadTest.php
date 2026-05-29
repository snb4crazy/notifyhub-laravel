<?php

namespace NotifyHub\LaravelClient\Tests\Unit;

use NotifyHub\LaravelClient\Data\EventPayload;
use NotifyHub\LaravelClient\Tests\TestCase;

class EventPayloadTest extends TestCase
{
    public function test_minimal_payload_serializes_correctly(): void
    {
        $payload = new EventPayload(
            title: 'Something went wrong',
            message: 'A detailed message',
            severity: 'error',
        );

        $array = $payload->toArray();

        $this->assertSame('Something went wrong', $array['title']);
        $this->assertSame('A detailed message', $array['message']);
        $this->assertSame('error', $array['severity']);
        $this->assertArrayNotHasKey('event_type', $array);
        $this->assertArrayNotHasKey('fingerprint', $array);
    }

    public function test_from_exception_builds_structured_payload(): void
    {
        $exception = new \RuntimeException('DB connection lost');

        $payload = EventPayload::fromException($exception, 'critical', ['user_id' => 42]);
        $array = $payload->toArray();

        $this->assertSame('laravel.exception', $array['event_type']);
        $this->assertSame('critical', $array['severity']);
        $this->assertSame('DB connection lost', $array['message']);
        $this->assertStringContainsString('RuntimeException', $array['title']);
        $this->assertSame(42, $array['context']['user_id']);
        $this->assertArrayHasKey('file', $array['sensitive_context']);
        $this->assertArrayHasKey('line', $array['sensitive_context']);
        $this->assertArrayHasKey('trace', $array['sensitive_context']);
        $this->assertStringContainsString('RuntimeException', $array['fingerprint'] ?? '');
    }

    public function test_from_failed_job_uses_queue_event_type(): void
    {
        $exception = new \RuntimeException('timeout');
        $payload = EventPayload::fromFailedJob('App\\Jobs\\SendEmail', $exception);
        $array = $payload->toArray();

        $this->assertSame('queue.failed', $array['event_type']);
        $this->assertSame('error', $array['severity']);
        $this->assertSame('App\\Jobs\\SendEmail', $array['context']['job_class']);
    }

    public function test_from_failed_cron_uses_cron_event_type(): void
    {
        $payload = EventPayload::fromFailedCron('nightly-sync', 'exit code 1');
        $array = $payload->toArray();

        $this->assertSame('cron.failed', $array['event_type']);
        $this->assertSame('warning', $array['severity']);
        $this->assertSame('nightly-sync', $array['context']['command']);
    }

    public function test_null_fields_are_omitted_from_to_array(): void
    {
        $payload = new EventPayload(title: 'T', message: 'M', severity: 'info');
        $array = $payload->toArray();

        $this->assertArrayNotHasKey('environment', $array);
        $this->assertArrayNotHasKey('sensitive_context', $array);
        $this->assertArrayNotHasKey('occurred_at', $array);
    }
}

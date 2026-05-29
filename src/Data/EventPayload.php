<?php

namespace NotifyHub\LaravelClient\Data;

/**
 * Structured event payload for the NotifyHub intake endpoint.
 *
 * All fields map directly to the POST /api/v1/events contract.
 * Use EventPayload::fromException() for the common Laravel exception case.
 */
class EventPayload
{
    public function __construct(
        /** Required: short human-readable title (max 140 chars). */
        public readonly string $title,

        /** Required: full operator-facing description (max 5000 chars). */
        public readonly string $message,

        /** Required: one of info | warning | error | critical. */
        public readonly string $severity,

        /**
         * Logical category for routing and future filtering.
         * Examples: laravel.exception, cron.failed, queue.failed, payment.failed.
         */
        public readonly ?string $eventType = null,

        /** The application or service that generated the event. */
        public readonly ?string $application = null,

        /** The runtime environment: production, staging, local, etc. */
        public readonly ?string $environment = null,

        /**
         * Non-sensitive structured context (request metadata, IDs, tags).
         * This is visible to all project members.
         *
         * @var array<string, mixed>|null
         */
        public readonly ?array $context = null,

        /**
         * Sensitive diagnostic data: stack traces, file paths, internal state.
         * Redacted from viewers who do not have the sensitive-context permission.
         *
         * @var array<string, mixed>|null
         */
        public readonly ?array $sensitiveContext = null,

        /**
         * Stable fingerprint for grouping repeated identical incidents.
         * Recommended format: "{app}:{env}:{exception_class}" or similar.
         */
        public readonly ?string $fingerprint = null,

        /** When the incident actually occurred, if known. */
        public readonly ?\DateTimeInterface $occurredAt = null,
    ) {}

    /**
     * Build a payload from a caught exception with recommended defaults.
     *
     * @param  array<string, mixed>  $context
     */
    public static function fromException(
        \Throwable $exception,
        string $severity = 'critical',
        array $context = [],
    ): self {
        $app = config('app.name', 'app');
        $env = app()->environment();

        return new self(
            title: 'Unhandled exception: '.$exception::class,
            message: $exception->getMessage(),
            severity: $severity,
            eventType: 'laravel.exception',
            application: $app,
            environment: $env,
            context: array_merge([
                'exception_class' => $exception::class,
                'url' => request()?->fullUrl(),
                'method' => request()?->method(),
            ], $context),
            sensitiveContext: [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => collect($exception->getTrace())->take(15)->values()->all(),
                'previous' => $exception->getPrevious()?->getMessage(),
            ],
            fingerprint: sprintf('%s:%s:%s', $app, $env, $exception::class),
            occurredAt: new \DateTimeImmutable,
        );
    }

    /**
     * Build a payload for a failed queue job.
     *
     * @param  array<string, mixed>  $context
     */
    public static function fromFailedJob(string $jobClass, \Throwable $exception, array $context = []): self
    {
        $app = config('app.name', 'app');
        $env = app()->environment();

        return new self(
            title: 'Queue job failed: '.$jobClass,
            message: $exception->getMessage(),
            severity: 'error',
            eventType: 'queue.failed',
            application: $app,
            environment: $env,
            context: array_merge(['job_class' => $jobClass], $context),
            sensitiveContext: [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => collect($exception->getTrace())->take(15)->values()->all(),
            ],
            fingerprint: sprintf('%s:%s:queue.failed:%s', $app, $env, $jobClass),
        );
    }

    /**
     * Build a payload for a scheduled command that did not complete.
     *
     * @param  array<string, mixed>  $context
     */
    public static function fromFailedCron(string $commandName, string $message, array $context = []): self
    {
        $app = config('app.name', 'app');
        $env = app()->environment();

        return new self(
            title: 'Scheduled command failed: '.$commandName,
            message: $message,
            severity: 'warning',
            eventType: 'cron.failed',
            application: $app,
            environment: $env,
            context: array_merge(['command' => $commandName], $context),
            fingerprint: sprintf('%s:%s:cron.failed:%s', $app, $env, $commandName),
        );
    }

    /**
     * Serialize the payload to the array shape expected by POST /api/v1/events.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'event_type' => $this->eventType,
            'title' => $this->title,
            'message' => $this->message,
            'severity' => $this->severity,
            'application' => $this->application,
            'environment' => $this->environment,
            'context' => $this->context,
            'sensitive_context' => $this->sensitiveContext,
            'fingerprint' => $this->fingerprint,
            'occurred_at' => $this->occurredAt?->format(\DateTimeInterface::ATOM),
        ], fn ($value) => $value !== null);
    }
}

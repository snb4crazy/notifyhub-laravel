# notifyhub-laravel

[![Tests](https://github.com/snb4crazy/notifyhub-laravel/actions/workflows/tests.yml/badge.svg)](https://github.com/snb4crazy/notifyhub-laravel/actions/workflows/tests.yml)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/snb4crazy/notifyhub-laravel.svg)](https://packagist.org/packages/snb4crazy/notifyhub-laravel)
[![License](https://img.shields.io/packagist/l/snb4crazy/notifyhub-laravel.svg)](LICENSE)

Laravel client package for sending events to a [NotifyHub](https://github.com/snb4crazy/NotifyHub) server.

## Requirements

| Dependency | Version         |
|------------|-----------------|
| PHP        | 8.2+            |
| Laravel    | 10, 11, 12, 13  |

For Laravel 13, PHP 8.3+ is required by Laravel itself.

The package test matrix is validated on PHP 8.2 with Laravel 10–12, and PHP 8.3 / 8.4 with Laravel 13.

## Installation

```bash
composer require snb4crazy/notifyhub-laravel
```

Publish the config file:

```bash
php artisan vendor:publish --tag=notifyhub-config
```

## Configuration

Add to `.env` in the **sending** app:

```env
NOTIFYHUB_ENABLED=true
NOTIFYHUB_URL=https://your-notifyhub-server.example.com
NOTIFYHUB_INGEST_KEY=your_project_ingest_key
NOTIFYHUB_TIMEOUT=5
NOTIFYHUB_RETRY_TIMES=2
NOTIFYHUB_RETRY_SLEEP_MS=200
```

Optional auto-reporting of logged exceptions:

```env
NOTIFYHUB_AUTO_REPORT=true
NOTIFYHUB_AUTO_REPORT_MIN_LEVEL=error
```

## Usage

### Send a plain event

```php
use NotifyHub\LaravelClient\Facades\NotifyHub;
use NotifyHub\LaravelClient\Data\EventPayload;

NotifyHub::send(new EventPayload(
    title: 'Payment declined',
    message: 'Stripe returned error code card_declined',
    severity: 'error',
    eventType: 'payment.failed',
    application: config('app.name'),
    environment: app()->environment(),
    context: ['order_id' => $order->id],
));
```

### Report an exception

```php
try {
    $this->chargeCard($order);
} catch (\Throwable $e) {
    NotifyHub::sendException($e, ['order_id' => $order->id]);
    throw $e;
}
```

Or use the `EventPayload` factories directly:

```php
NotifyHub::send(EventPayload::fromException($exception));
NotifyHub::send(EventPayload::fromFailedJob(SendEmail::class, $exception));
NotifyHub::send(EventPayload::fromFailedCron('nightly-sync', 'exit 1'));
```

### Use the DI interface

```php
use NotifyHub\LaravelClient\Contracts\NotifyHubClientInterface;

class MyService
{
    public function __construct(private NotifyHubClientInterface $notifyHub) {}

    public function doWork(): void
    {
        try {
            // ...
        } catch (\Throwable $e) {
            $this->notifyHub->sendException($e);
        }
    }
}
```

### Send a raw array

```php
NotifyHub::sendRaw([
    'title'    => 'Custom alert',
    'message'  => 'Something interesting happened',
    'severity' => 'info',
    'event_type' => 'custom.event',
]);
```

### Integration in the Laravel exception handler

Add to `bootstrap/app.php` (Laravel 11+) or `App\Exceptions\Handler` (Laravel 10):

```php
// bootstrap/app.php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->report(function (\Throwable $e) {
        rescue(fn () => app(\NotifyHub\LaravelClient\Contracts\NotifyHubClientInterface::class)
            ->sendException($e));
    });
})
```

Or in Laravel 10's `app/Exceptions/Handler.php`:

```php
public function register(): void
{
    $this->reportable(function (\Throwable $e) {
        rescue(fn () => app(\NotifyHub\LaravelClient\Contracts\NotifyHubClientInterface::class)
            ->sendException($e));
    });
}
```

## Event payload contract

| Field              | Type      | Required | Notes                                              |
|--------------------|-----------|----------|----------------------------------------------------|
| `title`            | string    | ✓        | Max 140 chars                                      |
| `message`          | string    | ✓        | Max 5000 chars                                     |
| `severity`         | string    | ✓        | `info`, `warning`, `error`, `critical`             |
| `event_type`       | string    | –        | e.g. `laravel.exception`, `queue.failed`           |
| `application`      | string    | –        | Identifies the sending app                         |
| `environment`      | string    | –        | `production`, `staging`, `local`                   |
| `context`          | object    | –        | Non-sensitive metadata (visible to all members)    |
| `sensitive_context`| object    | –        | Stack traces, file paths (role-redacted on server) |
| `fingerprint`      | string    | –        | For future deduplication/grouping                  |
| `occurred_at`      | ISO 8601  | –        | When the incident happened                         |

## Testing

```bash
composer test
```

## License

MIT — see [LICENSE](LICENSE).


# Changelog

All notable changes to this package will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).

## [Unreleased]

### Added
- `EventPayload` data class with `fromException()`, `fromFailedJob()`, and `fromFailedCron()` factories.
- `NotifyHubClient` implementing `NotifyHubClientInterface` with `send()`, `sendException()`, and `sendRaw()` methods.
- `NotifyHub` Facade.
- `NotifyHubServiceProvider` with auto-discovery and config publishing.
- `ReportExceptionToNotifyHub` listener for optional auto-reporting via `NOTIFYHUB_AUTO_REPORT`.
- `NotifyHubException` with `fromHttpException()` factory.
- Publishable `config/notifyhub.php`.
- Unit and feature tests with Orchestra Testbench.
- GitHub Actions matrix across PHP 8.2–8.4 and Laravel 10–12.


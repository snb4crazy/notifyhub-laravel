# NotifyHub Laravel Client v1.0.0

First stable release of the Laravel client for sending events and exceptions to a centralized NotifyHub server.

## Highlights

- Laravel package with auto-discovery support.
- Typed `EventPayload` value object with helpers for exceptions, failed jobs, and failed cron commands.
- `NotifyHubClient` service with `send()`, `sendException()`, and `sendRaw()` methods.
- Optional automatic forwarding of logged exceptions through `NOTIFYHUB_AUTO_REPORT`.
- Packagist-ready metadata and tests.

## Supported versions

- PHP `^8.2`
- Laravel `^10.0 | ^11.0 | ^12.0 | ^13.0`

## Notes

- Laravel 13 requires PHP 8.3+ in practice.
- CI validates a reduced support matrix to keep checks fast and meaningful.

## Packagist submission checklist

- [ ] Confirm `composer.json` metadata is final (`name`, `description`, `license`, `homepage`, `support`).
- [ ] Tag the release as `v1.0.0`.
- [ ] Push the tag to GitHub.
- [ ] Create the GitHub Release using the notes below.
- [ ] Submit the GitHub repository URL to Packagist.
- [ ] Enable/verify the Packagist webhook for automatic updates.
- [ ] Confirm the badge URLs in `README.md` point to the public repository.
- [ ] Optionally publish the release note content in the GitHub Release body.

## GitHub Release body

```markdown
# NotifyHub Laravel Client v1.0.0

First stable release of the Laravel client for sending events and exceptions to a centralized NotifyHub server.

## Highlights

- Laravel package with auto-discovery support.
- Typed `EventPayload` value object with helpers for exceptions, failed jobs, and failed cron commands.
- `NotifyHubClient` service with `send()`, `sendException()`, and `sendRaw()` methods.
- Optional automatic forwarding of logged exceptions through `NOTIFYHUB_AUTO_REPORT`.
- Packagist-ready metadata and tests.

## Supported versions

- PHP `^8.2`
- Laravel `^10.0 | ^11.0 | ^12.0 | ^13.0`

## Notes

- Laravel 13 requires PHP 8.3+ in practice.
- CI validates a reduced support matrix to keep checks fast and meaningful.
```


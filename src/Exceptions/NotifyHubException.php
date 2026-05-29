<?php

namespace NotifyHub\LaravelClient\Exceptions;

use RuntimeException;

/**
 * Thrown when the NotifyHub server responds with an unexpected status code or
 * when the request could not be dispatched (connection error, timeout, etc.).
 *
 * Use NotifyHubException::fromResponse() so the HTTP details are always captured.
 */
class NotifyHubException extends RuntimeException
{
    public function __construct(
        string $message,
        int $code = 0,
        ?\Throwable $previous = null,
        /** @var array<string, mixed>|null */
        public readonly ?array $responseBody = null,
        public readonly ?int $httpStatus = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Build from a Guzzle/Illuminate HTTP client exception.
     */
    public static function fromHttpException(\Throwable $e, ?int $httpStatus = null): self
    {
        // Try to extract the real HTTP status from an Illuminate RequestException.
        if ($httpStatus === null && $e instanceof \Illuminate\Http\Client\RequestException) {
            $httpStatus = $e->response->status();
        }

        return new self(
            message: 'NotifyHub request failed: '.$e->getMessage(),
            code: $httpStatus ?? (int) $e->getCode(),
            previous: $e,
            httpStatus: $httpStatus,
        );
    }
}

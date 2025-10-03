<?php

namespace App\Exceptions\V1;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

abstract class BaseApiException extends Exception
{
    protected int $statusCode = 500;
    protected string $errorType = 'server_error';
    protected array $context = [];

    public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => [
                'type'       => $this->getErrorType(),
                'message'    => $this->getMessage(),
                'code'       => $this->getCode(),
                'context'    => $this->getContext(),
                'request_id' => $request->header('X-Request-ID')
            ],
        ], $this->getStatusCode());
    }


    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode(int $statusCode): static
    {
        $this->statusCode = $statusCode;

        return $this;
    }

    public function getErrorType(): string
    {
        return $this->errorType;
    }

    public function setErrorType(string $errorType): static
    {
        $this->errorType = $errorType;

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): static
    {
        $this->context = $context;

        return $this;
    }

    public function addContext(string $key, mixed $value): static
    {
        $this->context[$key] = $value;

        return $this;
    }

    public function report(): void
    {
        logger()->error($this->getMessage(), [
            'exception'   => get_class($this),
            'code'        => $this->getCode(),
            'status_code' => $this->getStatusCode(),
            'context'     => $this->getContext(),
            'trace'       => $this->getTraceAsString(),
        ]);
    }
}

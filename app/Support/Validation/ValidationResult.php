<?php

namespace App\Support\Validation;

class ValidationResult
{

    public function __construct(
        private readonly bool $valid,
        private readonly ?string $message = null,
        private readonly ?array $errors = null,
    ){}

    public static function valid(?string $message = null): self
    {
        return new self(true, $message);
    }

    public static function invalid(?string $message = null, ?array $errors = null): self
    {
        return new self(false, $message, $errors);
    }

    public function isValid(): bool
    {
        return $this->valid;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }

}

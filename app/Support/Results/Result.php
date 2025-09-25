<?php

namespace App\Support\Results;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

abstract class Result implements Arrayable, Jsonable
{

    public function __construct(
        public bool     $success {
            get {
                return $this->success;
            }
        },
        public ?string  $message = null {
            get {
                return $this->message;
            }
        },
        public array    $errors = [] {
            get {
                return $this->errors;
            }
        },
        protected mixed $data = null {
            get {
                return $this->data;
            }
        }
    ){}

    public static function success(string $message, mixed $data = null): static
    {
        return new static(true, $message, [], $data);
    }

    public static function failure(string $message, array $errors = []): static
    {
        return new static(false, $message, $errors);
    }

    public function isFailure(): bool
    {
        return ! $this->success;
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'errors'  => $this->errors,
            'data'    => $this->data,
        ];
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

}

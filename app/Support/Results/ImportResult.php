<?php

namespace App\Support\Results;

use Countable;
use JsonSerializable;
use Throwable;

class ImportResult implements JsonSerializable, Countable
{
    private ?string $importedFile;
    private int $total = 0;
    private int $imported = 0;
    private int $skipped = 0;
    private array $errors = [];
    private array $results = [];
    private bool $success = true;
    private ?string $message;
    private ?int $processTimeMs = null;
    private array $metadata = [];
    private int $maxStoredResults = 1000;

    public function __construct(?string $importedFile = null, ?string $message = null)
    {
        $this->importedFile = $importedFile;
        $this->message = $message;
    }

    public function incrementTotal(int $count = 1): self
    {
        $this->total += $count;
        return $this;
    }

    public function incrementImported(int $count = 1): self
    {
        $this->imported += $count;
        return $this;
    }

    public function incrementSkipped(int $count = 1): self
    {
        $this->skipped += $count;
        return $this;
    }

    public function addError(string $message, mixed $context = null, ?int $rowIndex = null, ?string $column = null): self
    {
        $this->errors[] = [
            'message' => $message,
            'context' => $context,
            'row'     => $rowIndex,
            'column'  => $column
        ];

        if ($this->success && count($this->errors) > 0) {
            $this->success = false;
        }

        return $this;
    }

    public function addResult(string $type, mixed $data): self
    {
        if (count($this->results) < $this->maxStoredResults) {
            $this->results[] = [
                'type'      => $type,
                'data'      => $data,
                'timestamp' => now()->toDateTimeString()
            ];
        }

        return $this;
    }

    public function setSuccess(bool $success): self
    {
        $this->success = $success;
        return $this;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function setImportedFile(string $filePath): self
    {
        $this->importedFile = $filePath;
        return $this;
    }

    public function setProcessTimeMs(int $milliseconds): self
    {
        $this->processTimeMs = $milliseconds;
        return $this;
    }

    public function addMetadata(string $key, mixed $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    public function markAsSuccess(?string $message = null, array $results = []): self
    {
        $this->success = true;

        if ($message !== null) {
            $this->message = $message;
        }

        if (count($results) > 0) {
            foreach ($results as $result) {
                if (is_array($result)) {
                    $type = $result['type'] ?? 'info';
                    $data = $result['data'] ?? null;
                    $this->addResult($type, $data);
                } else {
                    $this->addResult('info', $result);
                }
            }
        }

        return $this;
    }

    public function markAsFailed(?string $message = null, array $errors = []): self
    {
        $this->success = false;

        if ($message !== null) {
            $this->message = $message;
        }

        if (count($errors) > 0) {
            foreach ($errors as $err) {
                if (is_array($err)) {
                    $msg = $err['message'] ?? 'Unknown error';
                    $ctx = $err['context'] ?? null;
                    $row = $err['row'] ?? null;
                    $col = $err['column'] ?? null;
                    $this->addError($msg, $ctx, $row, $col);
                } else {
                    try {
                        $this->addError((string)$err);
                    } catch (Throwable $e) {
                        $this->addError('Unknown error: ' . $e->getMessage());
                    }
                }
            }
        }

        return $this;
    }

    public function getMetadata(): array { return $this->metadata; }
    public function getImportedFile(): ?string { return $this->importedFile; }
    public function getProcessTimeMs(): ?int { return $this->processTimeMs; }
    public function getResults(): array { return $this->results; }
    public function getErrors(): array { return $this->errors; }
    public function getMessage(): ?string { return $this->message; }
    public function getTotal(): int { return $this->total; }
    public function getImported(): int { return $this->imported; }
    public function getSkipped(): int { return $this->skipped; }
    public function isSuccess(): bool { return $this->success; }
    public function getErrorCount(): int { return count($this->errors); }

    public function jsonSerialize(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'stats' => [
                'total'       => $this->total,
                'imported'    => $this->imported,
                'skipped'     => $this->skipped,
                'error_count' => count($this->errors),
            ],
            'metadata'        => $this->metadata,
            'process_time_ms' => $this->processTimeMs,
            'errors'          => $this->errors,
            'results'         => $this->results,
        ];
    }

    public function count(): int
    {
        return $this->total;
    }

    public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT);
    }
}

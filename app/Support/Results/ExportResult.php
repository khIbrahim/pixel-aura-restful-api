<?php

namespace App\Support\Results;

class ExportResult extends Result
{

    public function __construct(
        bool $success,
        string $message,
        public readonly string $filePath,
        public readonly string $format,
        public readonly int $recordCount = 0,
        public readonly int $processedTimeMs = 0,
        public readonly ?string $jobId = null,
        public readonly ?string $downloadUrl = null,
        array $errors = [],
    ){
        parent::__construct($success, $message, $errors);
    }

    public static function success(string $message, mixed $data = null): static
    {
        $filePath    = $data['filePath'] ?? '';
        $format      = $data['format'] ?? 'csv';
        $recordCount = $data['recordCount'] ?? 0;
        $timeMs      = $data['processedTimeMs'] ?? 0;
        $jobId       = $data['jobId'] ?? null;
        $downloadUrl = $data['downloadUrl'] ?? null;
        return new static(true, $message, $filePath, $format, $recordCount, $timeMs, $jobId, $downloadUrl);
    }

    public static function error(string $message, string $format, array $errors = []): static
    {
        return new static(false, $message, '', $format, 0, 0, null, null, $errors);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'file_path'         => $this->filePath,
            'format'            => $this->format,
            'record_count'      => $this->recordCount,
            'processed_time_ms' => $this->processedTimeMs,
            'job_id'            => $this->jobId,
            'download_url'      => $this->downloadUrl,
        ]);
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

}

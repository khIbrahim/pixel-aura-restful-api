<?php

namespace App\DTO\V1\StoreMember;

final readonly class ImportStoreMemberDTO
{

    public function __construct(
        public string $importFilePath,
        public bool $async        = false,
        public int $batchSize     = 100,
        public bool $useBatchMode = false,
        public int $priority      = 1,
        public array $options     = []
    ){}

    public static function fromRequest(array $data): self
    {
        return new self(
            importFilePath: $data['file']->getRealPath(),
            async: $data['async'] ?? false,
            batchSize: $data['options']['batch_size'] ?? 100,
            useBatchMode: $data['options']['use_batch_mode'] ?? false,
            priority: $data['options']['priority'] ?? 0,
            options: $data['options'] ?? []
        );
    }

}

<?php

namespace App\Contracts\V1\Shared;

interface SkuGeneratorServiceInterface
{
    public function generate(string $name, string $modelClass, ?string $scope = null, ?array $context = []): string;

    public function generateForVariant(string $parentName, string $variantName, string $modelClass, ?string $scope = null, ?array $context = []): string;
}

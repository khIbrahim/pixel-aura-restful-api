<?php

namespace App\Exceptions\V1\Category;

use App\Exceptions\V1\BaseApiException;

class PositionDuplicateException extends BaseApiException
{
    protected string $errorType = 'POSITION_DUPLICATE_ERROR';
    protected $code             = 500;

    public static function withPosition(int $position): self
    {
        return new self("Une catégorie avec la position '$position' existe déjà dans ce magasin.");
    }

    public static function withPositions(array $positions): self
    {
        return new self("Des catégories avec les positions suivantes existent déjà dans ce magasin: " . implode(", ", $positions) . ".");
    }

    public static function default(): self
    {
        return new self("Une catégorie avec cette position existe déjà dans ce magasin.");
    }

}

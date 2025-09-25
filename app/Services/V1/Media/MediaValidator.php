<?php

namespace App\Services\V1\Media;

use App\Support\Validation\ValidationResult;
use Illuminate\Http\UploadedFile;

class MediaValidator
{

    public function validate(UploadedFile|string $file): ValidationResult
    {
        $config = config('media-management.validation');

        if ($file instanceof UploadedFile){
            return $this->validateUploadedFile($file, $config);
        }

        return ValidationResult::valid();
    }

    private function validateUploadedFile(UploadedFile $file, array $config): ValidationResult
    {
        $maxSize = $config['max_size'] ?? 5 * 1024;
        if ($file->getSize() > $maxSize * 1024){
            return ValidationResult::invalid("Fichier trop volumineux. Taille maximale autorisée : $maxSize KB.", [
                'file' => 'Fichier trop volumineux',
            ]);
        }

        $allowedMimeTypes = $config['mime_types'] ?? ['jpg', 'jpeg', 'png', 'webp'];
        if(! in_array($file->extension(), $allowedMimeTypes)){
            return ValidationResult::invalid('Type de fichier non autorisé. Types autorisés : ' . implode(', ', $allowedMimeTypes) . '.', [
                'file' => 'Type de fichier non autorisé',
            ]);
        }

        $dimensions = getimagesize($file->path());
        if ($dimensions){
            [$width, $height] = $dimensions;

            $validatedDimension = $this->validateDimensions($width, $height, $config);
            if(! $validatedDimension->isValid()){
                return $validatedDimension;
            }
        }

        return ValidationResult::valid();
    }

    private function validateDimensions(int $width, int $height, array $config): ValidationResult
    {
        if ($width < $config['dimensions']['min_width'] ?? 100){
            return ValidationResult::invalid('Largeur minimale non respectée. Largeur minimale : ' . ($config['dimensions']['min_width'] ?? 100) . 'px.', [
                'width' => 'Largeur minimale non respectée',
            ]);
        }

        if ($height < $config['dimensions']['min_height'] ?? 100){
            return ValidationResult::invalid('Hauteur minimale non respectée. Hauteur minimale : ' . ($config['dimensions']['min_height'] ?? 100) . 'px.', [
                'height' => 'Hauteur minimale non respectée',
            ]);
        }

        if ($width > $config['dimensions']['max_width'] ?? 5000){
            return ValidationResult::invalid('Largeur maximale non respectée. Largeur maximale : ' . ($config['dimensions']['max_width'] ?? 5000) . 'px.', [
                'width' => 'Largeur maximale non respectée',
            ]);
        }

        if ($height > $config['dimensions']['max_height'] ?? 5000){
            return ValidationResult::invalid('Hauteur maximale non respectée. Hauteur maximale : ' . ($config['dimensions']['max_height'] ?? 5000) . 'px.', [
                'height' => 'Hauteur maximale non respectée',
            ]);
        }

        return ValidationResult::valid();
    }

}

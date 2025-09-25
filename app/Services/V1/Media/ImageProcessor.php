<?php

namespace App\Services\V1\Media;

use App\Exceptions\V1\Media\UrlImageException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Intervention\Image\Image as InterventionImage;
use Throwable;

class ImageProcessor
{
    public function __construct(
        private readonly UrlImageDownloader $urlImageDownloader,
    ) {}

    /**
     * @throws Throwable
     */
    public function process(UploadedFile|string $file): UploadedFile|string
    {
        try {
            if (is_string($file) && filter_var($file, FILTER_VALIDATE_URL)) {
                return $this->processFromUrl($file);
            }

            if ($file instanceof UploadedFile) {
                return $this->processUploadedFile($file);
            }

            return $file;
        } catch (Throwable $e) {
            Log::warning("Erreur lors du traitement de l'image", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * @throws Throwable
     */
    private function processUploadedFile(UploadedFile $file): UploadedFile
    {
        try {
            $image = $this->processImageFile($file->path());
            $image->save($file->path(), 85);

            return $file;
        } catch (Throwable $e) {
            Log::error('Échec du traitement du fichier', [
                'file'  => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * @throws UrlImageException
     */
    private function processFromUrl(string $url): string
    {
        Log::info("Téléchargement et traitement de l'image depuis l'URL", [
            'url' => $url,
        ]);

        try {
            $tempFile = $this->urlImageDownloader->download($url);

            Log::info("Image téléchargée avec succès depuis l'URL", [
                'url'      => $url,
                'tempFile' => $tempFile,
            ]);

            $processedImage    = $this->processImageFile($tempFile);
            $processedFilePath = $this->saveProcessedImage($processedImage, $tempFile);

            Log::info("Image traitée avec succès", [
                'url'            => $url,
                'processed_path' => $processedFilePath,
            ]);

            return $processedFilePath;
        } catch (Throwable $e) {
            Log::error("Erreur lors du traitement de l'image", [
                'url'   => $url,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new UrlImageException("Erreur de traitement: {$e->getMessage()}");
        }
    }

    private function processImageFile(string $filePath): InterventionImage
    {
        $image = Image::make($filePath);

        $image->orientate();

        if ($image->width() > 1920 || $image->height() > 1920) {
            $image->resize(1920, 1920, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $image->sharpen(5);

        if (config('media-management.processing.auto_enhance', false)) {
            $this->enhanceImage($image);
        }

        return $image;
    }

    private function enhanceImage(InterventionImage $image): void
    {
        $image->contrast(5);

        $averageBrightness = $this->calculateAverageBrightness($image);
        if ($averageBrightness < 80) {
            $image->brightness(10);
        }
    }

    private function calculateAverageBrightness(InterventionImage $image): float
    {
        $sample = clone $image;
        $sample->resize(10, 10);

        return 128;
    }

    private function saveProcessedImage(InterventionImage $image, string $originalPath): string
    {
        $pathInfo      = pathinfo($originalPath);
        $processedPath = $pathInfo['dirname'] . '/processed_' . $pathInfo['basename'];

        $image->save($processedPath, 85);

        return $processedPath;
    }
}

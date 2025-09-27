<?php

namespace App\Services\V1\Media;

use App\Contracts\V1\Media\MediaManagerInterface;
use App\DTO\V1\Media\MediaUploadOptions;
use App\Support\Results\MediaResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

readonly class MediaManager implements MediaManagerInterface
{

    public function __construct(
        private MediaValidator    $mediaValidator,
        private ImageProcessor    $imageProcessor,
    ){}

    public function uploadImage(Model $model, string|UploadedFile $file, MediaUploadOptions $options): MediaResult
    {
        try {
            $validationResult = $this->mediaValidator->validate($file);
            if(! $validationResult->isValid()){
                return MediaResult::failure($validationResult->getMessage(), $validationResult->getErrors());
            }

            $processedFile = $this->imageProcessor->process($file);

            assert($model instanceof HasMedia);
            $mediaAdder = $model->addMedia($processedFile)
                ->withCustomProperties(array_merge($options->customProperties, ['visibility' => 'public']));

            $mediaAdder->toMediaCollection($options->collection, $options->disk ?? 's3');

            if (is_string($processedFile) && file_exists($processedFile)) {
                @unlink($processedFile);
            }

            /** @var Media $media */
            $media = $model->getMedia($options->collection)->last();

            Log::info("Image uploadé avec succès", [
                'model_type' => get_class($model),
                'model_id'   => $model->getKey(),
                'collection' => $options->collection,
                'media_id'   => $media->id,
            ]);

            return MediaResult::successUpload($media, "Image uploadé avec succès", $model->getImagesAttribute());
        } catch (Throwable $e){
            Log::error("Erreur lors de l'upload de l'image", [
                'model_type' => get_class($model),
                'model_id'   => $model->getKey(),
                'collection' => $options->collection,
                'error'      => $e->getMessage(),
            ]);

            return MediaResult::failure("Une erreur est survenue lors de l'upload de l'image.", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function replaceImage(Model $model, UploadedFile|string $file, MediaUploadOptions $options): MediaResult
    {
        try {
            if (config('media-management.storage.cleanup_old_media')) {
                $this->deleteImage($model, $options->collection);
            }

            return $this->uploadImage($model, $file, $options);

        } catch (Throwable $e) {
            return MediaResult::failure("Erreur lors du remplacement: " . $e->getMessage());
        }
    }

    public function deleteImage(Model $model, string $collection, ?int $mediaId = null): MediaResult
    {
        try {
            assert($model instanceof HasMedia);
            if ($mediaId) {
                /** @var Media $media */
                $media   = $model->getMedia($collection)->find($mediaId);
                $deleted = $media?->delete();
                if ($deleted) {
                    Log::info("Image supprimée avec succès", [
                        'model_type' => get_class($model),
                        'model_id'   => $model->getKey(),
                        'collection' => $collection,
                        'media_id'   => $mediaId,
                    ]);
                    return MediaResult::successDelete("Image supprimée avec succès", $model->getImagesAttribute());
                } else {
                    return MediaResult::failure("Image non trouvée ou déjà supprimée.");
                }
            }

            $model->clearMediaCollection($collection);
            Log::info("Toutes les images de la collection '$collection' ont été supprimées avec succès", [
                'model_type' => get_class($model),
                'model_id'   => $model->getKey(),
                'collection' => $collection,
            ]);

            return MediaResult::successDelete("Toutes les images de la collection '$collection' ont été supprimées avec succès", $model->getImagesAttribute());
        } catch (Throwable $e) {
            Log::error("La suppression de l'image a échoué", ['error' => $e->getMessage()]);

            return MediaResult::failure("La suppression de l'image a échoué: " . $e->getMessage(), [
                'error' => $e->getMessage(),
            ]);
        }
    }
}

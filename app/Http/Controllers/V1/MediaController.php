<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Media\MediaManagerInterface;
use App\DTO\V1\Media\MediaUploadOptions;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\Media\StoreMediaRequest;
use App\Http\Resources\V1\MediaResource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;

class MediaController extends Controller
{

    public function __construct(
        private readonly MediaManagerInterface $mediaManager
    ){}

    public function showMain(string $type, Model $model): JsonResponse
    {
        $image = $model->getMainImage();
        if (! $image) {
            return response()->json([
                'message' => 'Aucune image principale trouvée.',
            ], 404);
        }

        return response()->json([
            'data' => new MediaResource($image)
        ]);
    }

    public function storeMain(StoreMediaRequest $request, string $type, Model $model): JsonResponse
    {
        $data  = $request->validated();
        $image = $data['image'] ?? $data['image_url'];

        $mediaResult = $this->mediaManager->replaceImage($model, $image, MediaUploadOptions::main());
        if($mediaResult->isFailure()){
            return response()->json([
                'message' => 'Échec du téléchargement de l\'image principale.',
                'errors'  => $mediaResult->errors,
            ], 500);
        }

        return response()->json([
            'message' => 'Image principale téléchargée avec succès.',
            'data'    => new MediaResource($mediaResult->getMedia()),
            'urls'    => $mediaResult->toArray()
        ]);
    }

    public function destroyMain(string $type, Model $model): JsonResponse
    {
        $result = $this->mediaManager->deleteImage($model, 'main_image');
        if($result->isFailure() ) {
            return response()->json([
                'message' => $result->message,
                'errors'  => $result->errors,
            ], 500);
        }

        return response()->json([
            'message' => $result->message ?? "Image principale supprimée avec succès.",
            'urls'    => $result->toArray()
        ]);
    }

    public function indexGallery(string $type, Model $model): JsonResponse
    {
        $gallery = $model->getGalleryImages();
        return response()->json([
            'data' => MediaResource::collection($gallery)
        ]);
    }

    public function storeGallery(StoreMediaRequest $request, string $type, Model $model): JsonResponse
    {
        $data  = $request->validated();
        $image = $data['image'] ?? $data['image_url'];

        $mediaResult = $this->mediaManager->uploadImage($model, $image, MediaUploadOptions::gallery());
        if($mediaResult->isFailure()){
            return response()->json([
                'message' => 'Échec du téléchargement de l\'image de la galerie.',
                'errors'  => $mediaResult->errors,
            ], 500);
        }

        return response()->json([
            'message' => 'Image de la galerie téléchargée avec succès.',
            'data'    => new MediaResource($mediaResult->getMedia()),
            'urls'    => $mediaResult->toArray()
        ]);
    }

    public function destroyGallery(string $type, Model $model, int $media): JsonResponse
    {
        $deleted = $this->mediaManager->deleteImage($model, 'gallery', $media);
        if($deleted->isFailure() ) {
            return response()->json([
                'message' => $deleted->message,
                'errors'  => $deleted->errors,
            ], 500);
        }

        return response()->json([
            'message' => $deleted->message ?? "Image de la galerie supprimée avec succès.",
            'urls'    => $deleted->toArray()
        ]);
    }

}

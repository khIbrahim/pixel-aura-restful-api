<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaController extends Controller
{
    /**
     * Affiche une image média avec possibilité de conversion
     *
     * @param Media $media
     * @param Request $request
     * @return Response
     */
    public function show(Media $media, Request $request): Response
    {
        $conversion = $request->query('conversion');

        if ($conversion && in_array($conversion, ['thumbnail', 'banner', 'icon'])) {
            $path = $media->getPath($conversion);
        } else {
            $path = $media->getPath();
        }

        return response()->file($path);
    }
}

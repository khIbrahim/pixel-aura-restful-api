<?php

use App\Exceptions\V1\Media\UrlImageException;
use App\Http\Controllers\V1\MediaController;
use App\Http\Controllers\V1\MeController;
use App\Models\V1\Item;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

Route::prefix('v1')
    ->middleware(['auth:sanctum', 'device.ctx', 'device.throttle:per-device', 'correlate'])
    ->group(function () {
        Route::get('/me', [MeController::class, 'me']);

    });

Route::get('v1/media/{media}', [MediaController::class, 'show'])
    ->name('media.show');

Route::get('/test', function(){
    $url = "https://media.istockphoto.com/id/1312807779/photo/real-neapolitan-italian-pizza-called-margherita-pizza-just-out-of-the-oven.jpg?s=2048x2048&w=is&k=20&c=hMOI6F5JAXnJTxjnOHqdh3QtHwNxJOkK2wS7eucvBP0=";

    $options = new \App\DTO\V1\Media\MediaUploadOptions(
        collection: "test",
        disk: "s3",
        preserveOriginal: false,
        generateConversions: true,
        generateResponsive: false,
        customProperties: [
            'foo' => 'bar'
        ]
    );

    $validator = Validator::make(['url' => $url], [
        'url' => ['required', 'url', 'active_url']
    ]);

    if ($validator->fails()) {
        throw UrlImageException::invalidUrl($url);
    }

    $parsedUrl = parse_url($url);
    if (! in_array($parsedUrl['scheme'] ?? '', ['http', 'https'])) {
        throw UrlImageException::invalidUrl($url);
    }

    $response = Http::withOptions([
        'verify'  => true,
        'timeout' => 10,
        'allow_redirects' => [
            'max'             => 5,
            'strict'          => true,
            'referer'         => true,
            'track_redirects' => true
        ]
    ])->withHeaders([
        'User-Agent'    => 'PixelAura/1.0 (+https://pixelaura.com)',
        'Accept'        => 'image/*',
        'Cache-Control' => 'no-cache',
    ])->get($url);

    $contentType = $response->header('Content-Type');
    $extension   = match($contentType) {
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
        'image/bmp' => 'bmp',
        default => 'jpg'
    };

    $fileName = 'url_' . Str::uuid() . '.' . $extension;
    $tempPath = 'temp/url-images/' . $fileName;
    Storage::disk('local')->put($tempPath, $response->body());

    $path = Storage::disk('local')->path($tempPath);

    $image = Image::make($path);
    $image->orientate();
    $image->resize(300, 300, function ($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
    });
    $image->sharpen(5);
    $image->contrast(5);

    $pathInfo      = pathinfo($path);
    $processedPath = $pathInfo['dirname'] . '/processed_' . $pathInfo['basename'];

    $image->save($processedPath, 80);

    var_dump($processedPath);

    $baseName = 'external-image';

    $model = Item::query()->find(25);
    $mediaAdder = $model->addMedia($processedPath)
        ->withCustomProperties($options->customProperties);

    $mediaAdder->toMediaCollection('test', 's3');
    $media = $model->getMedia($options->collection)->last();

    return 'send ok ' . $media->id;
});

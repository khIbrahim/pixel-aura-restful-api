<?php

namespace App\Http\Middleware\V1\Media;

use Closure;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\HasMedia;
use Symfony\Component\HttpFoundation\Response;

class HasMediaMiddleware
{

    public function handle(Request $request, Closure $next, ...$args): Response
    {
        $type  = $request->route('type');
        $model = $request->route('modelBinding');

        if(! $model instanceof HasMedia){
            return response()->json([
                'message' => "Le modèle de type $type ne gère pas les images.",
            ], 400);
        }

        foreach ($args as $arg) {
            if ($arg === 'main' && ! method_exists($model, 'getMainImage')) {
                return response()->json([
                    'message' => "Le modèle de type $type ne gère pas les images principales.",
                ], 400);
            } elseif($arg === 'gallery' && ! method_exists($model, 'getGalleryImages')) {
                return response()->json([
                    'message' => "Le modèle de type $type ne gère pas la galerie d'images.",
                ], 400);
            }
        }

        return $next($request);
    }
}

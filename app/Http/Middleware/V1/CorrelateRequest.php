<?php

namespace App\Http\Middleware\V1;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CorrelateRequest
{

    public function handle(Request $request, Closure $next): Response
    {
        $rid = $request->header('X-Request-Id') ?: (string) Str::uuid();

        Log::withContext([
            'request_id' => $rid,
            'store_id'   => $request->attributes->get('store')?->id,
            'device_id'  => $request->attributes->get('device')?->id,
        ]);

        /** @var Response $response */
        $response = $next($request);
        $response->headers->set('X-Request-Id', $rid);

        return $response;
    }
}

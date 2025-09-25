<?php

namespace App\Http\Middleware\V1;

use App\Models\V1\Device;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureDeviceContext
{

    public function handle(Request $request, Closure $next): Response
    {
        $store  = $request->attributes->get('store');
        /** @var Device $device */
        $device = $request->attributes->get('device');

        if(! $store || ! $device){
            return response()->json([
                'message' => "Le store ou device est manquant/invalide"
            ], 422);
        }

        $ip = $request->ip();
        if($device->allowed_ip_ranges && ! $device->isIpAllowed($ip)){
            Log::warning("Ip bloqué par la black list (! allowed_ip_ranges)", [
                'ip'        => $ip,
                'store_id'  => $store->id,
                'device_id' => $device->id
            ]);

            return response()->json([
                'message' => "Votre IP n'est pas autorisé"
            ], 403);
        }

        Request::macro('store', fn() => $request->attributes->get('store'));
        Request::macro('device', fn() => $request->attributes->get('device'));

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware\V1;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreMember
{

    public function handle(Request $request, Closure $next): Response
    {
        $storeMember = $request->attributes->get('store_member');
        if(! $storeMember){
            return response()->json([
                'message' => 'Le store member est manquant/invalide',
            ], 403);
        }

        return $next($request);
    }
}

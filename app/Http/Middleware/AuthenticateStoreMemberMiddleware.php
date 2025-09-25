<?php

namespace App\Http\Middleware;

use App\Contracts\V1\Auth\StoreMemberAuthServiceInterface;
use App\Models\V1\Auth\PersonalAccessToken;
use App\Models\V1\Device;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class AuthenticateStoreMemberMiddleware
{

    public function __construct(
        private StoreMemberAuthServiceInterface $authService
    ) {
    }

    public function handle(Request $request, Closure $next, ?string $ability = null): Response
    {
        $token = $request->bearerToken();
        if (! $token) {
            return response()->json(['message' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
        }

        /** @var PersonalAccessToken $personalAccessToken */
        $personalAccessToken = PersonalAccessToken::findToken($token);
        if (! $personalAccessToken || $personalAccessToken->revoked) {
            return response()->json(['message' => 'Token invalide'], Response::HTTP_UNAUTHORIZED);
        }

        if (! $personalAccessToken->store_member_id) {
            return response()->json(['message' => 'Aucun membre du magasin connecté'], Response::HTTP_UNAUTHORIZED);
        }

        $storeMember = $personalAccessToken->storeMember;
        $device      = $personalAccessToken->tokenable;

        if (! $storeMember || ! $device instanceof Device) {
            return response()->json(['message' => 'Données d\'authentification invalides'], Response::HTTP_UNAUTHORIZED);
        }

        if (! $storeMember->isActive()) {
            return response()->json(['message' => 'Compte désactivé'], Response::HTTP_FORBIDDEN);
        }

        if ($ability && ! $this->authService->hasPermission($storeMember, $ability)) {
            return response()->json(['message' => 'Permission refusée'], Response::HTTP_FORBIDDEN);
        }

        $request->attributes->set('store_member', $storeMember);
        $request->attributes->set('device', $device);

        return $next($request);
    }
}

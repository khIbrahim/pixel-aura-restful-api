<?php

namespace App\Http\Middleware\V1;

use App\Models\V1\Auth\PersonalAccessToken;
use App\Models\V1\Device;
use App\Models\V1\StoreMember;
use App\Services\V1\Auth\AbilityManager;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

readonly class CheckAbility
{

    public function __construct(
        private AbilityManager $abilityManager,
    ){}

    public function handle(Request $request, Closure $next, ...$abilities): Response
    {
        if (empty($abilities)){
            return $next($request);
        }

        /** @var Device $device */
        $device = $request->attributes->get('device');

        $token = PersonalAccessToken::findToken($request->bearerToken());
        if(! $token || ! $device){
            return $this->unauthorizedResponse();
        }

        foreach ($abilities as $ability){
            if(! $token->can($ability)){
                return $this->forbiddenResponse(
                    'Permissions insuffisantes',
                    ['missing_token_ability' => $ability]
                );
            }
        }

        /** @var StoreMember $storeMember */
        $storeMember = $this->getStoreMemberFromRequest($request);
        if(! $storeMember){
            return $next($request);
        }

        if(! $storeMember->isActive()){
            return $this->forbiddenResponse('Compte désactivé');
        }

        $hasPermission = $this->abilityManager->hasAllAbilities($storeMember, $abilities);

        if(! $hasPermission){
            return $this->forbiddenResponse(
                'Permissions insuffisantes',
                $this->getMissingAbilitiesContext($storeMember, $abilities)
            );
        }

        $request->attributes->set('authorized_abilities', $abilities);

        return $next($request);
    }

    private function getStoreMemberFromRequest(Request $request): ?StoreMember
    {
        $storeMember = $request->attributes->get('store_member');
        if ($storeMember instanceof StoreMember) {
            return $storeMember;
        }

        $token = $request->bearerToken();
        if (! $token) {
            return null;
        }

        $personalAccessToken = PersonalAccessToken::findToken($token);
        if (! $personalAccessToken || $personalAccessToken->revoked) {
            return null;
        }

        return $personalAccessToken->storeMember;
    }

    private function getMissingAbilitiesContext(StoreMember $storeMember, array $requiredAbilities): array
    {
        $missingAbilities = $this->abilityManager->getMissingAbilities($storeMember, $requiredAbilities);

        return [
            'required_abilities' => $requiredAbilities,
            'missing_abilities'  => $missingAbilities->toArray(),
            'message'            => 'Permissions manquantes: ' . $missingAbilities->implode(', ')
        ];
    }

    private function unauthorizedResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Token invalide ou absent',
            'error'   => 'unauthorized'
        ], Response::HTTP_UNAUTHORIZED);
    }

    private function forbiddenResponse(string $message, array $context = []): JsonResponse
    {
        $response = [
            'message' => $message,
            'error' => 'forbidden'
        ];

        if (! empty($context)) {
            $response['context'] = $context;
        }

        return response()->json($response, Response::HTTP_FORBIDDEN);
    }
}

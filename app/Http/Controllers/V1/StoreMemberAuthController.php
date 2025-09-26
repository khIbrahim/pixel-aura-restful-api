<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Auth\StoreMemberAuthServiceInterface;
use App\DTO\V1\StoreMember\AuthenticateStoreMemberDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreMember\AuthenticateStoreMemberRequest;
use App\Http\Resources\V1\StoreMemberResource;
use App\Models\V1\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class StoreMemberAuthController extends Controller
{

    public function __construct(
        private readonly StoreMemberAuthServiceInterface $storeMemberAuthService,
    ){}

    /**
     * GET /api/v1/members/me
     */
    public function me(Request $request): JsonResponse
    {
        $storeMember = $request->attributes->get('store_member');

        if (! $storeMember) {
            return response()->json([
                'success' => false,
                'message' => 'Pas de membre authentifié'
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'store_member' => new StoreMemberResource($storeMember)
            ]
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * POST /api/v1/stores/{store}/members/{store_member}/authenticate
     */
    public function authenticate(Store $store, AuthenticateStoreMemberRequest $request): JsonResponse
    {
        $data    = AuthenticateStoreMemberDTO::fromRequest($request->validated());
        $device = $request->attributes->get('device');
        $result = $this->storeMemberAuthService->authenticate($store->id, $data->code, $data->pin, $device);

        if($result->isFailure()){
            return response()->json([
                'message' => $result->message,
                'errors'  => $result->errors
            ], ResponseAlias::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'message' => $result->message,
            'data'    => new StoreMemberResource($result->storeMember),
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * POST /api/v1/members/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $storeMember = $request->attributes->get('store_member');
        $device      = $request->attributes->get('device');
        $logout      = $this->storeMemberAuthService->logout($storeMember, $device);

        if ($logout->success){
            return response()->json([
                'message' => $logout->message,
                'data'    => new StoreMemberResource($storeMember),
            ], ResponseAlias::HTTP_OK);
        } else {
            return response()->json([
                'message' => $logout->message,
                'errors'  => $logout->errors
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}

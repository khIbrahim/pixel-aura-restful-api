<?php

namespace App\Http\Controllers\V1;

use App\Actions\StoreMember\V1\AuthenticateStoreMember;
use App\Actions\StoreMember\V1\LogoutStoreMember;
use App\DTO\V1\StoreMember\AuthenticateStoreMemberDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreMember\AuthenticateStoreMemberRequest;
use App\Http\Resources\V1\StoreMemberResource;
use App\Models\V1\Store;
use App\Models\V1\StoreMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * @group Store Member Authentication
 */
class StoreMemberAuthController extends Controller
{

    /**
     * GET /api/v1/members/me
     * Route: members.me
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
     * Route: store-members.authenticate
     */
    public function authenticate(Store $store, StoreMember $storeMember, AuthenticateStoreMemberRequest $request, AuthenticateStoreMember $action): JsonResponse
    {
        $dto    = AuthenticateStoreMemberDTO::fromRequest($request->validated());
        $device = $request->attributes->get('device');
        $result = $action($store, $storeMember, $dto, $device);

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
     * Route: members.logout
     */
    public function logout(Request $request, LogoutStoreMember $action): JsonResponse
    {
        $storeMember = $request->attributes->get('store_member');
        $device      = $request->attributes->get('device');
        $logout      = $action($storeMember, $device);

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

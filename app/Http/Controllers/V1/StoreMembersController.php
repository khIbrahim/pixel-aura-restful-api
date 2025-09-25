<?php

namespace App\Http\Controllers\V1;

use App\Actions\StoreMember\V1\CreateStoreMember;
use App\Actions\StoreMember\V1\UpdateStoreMember;
use App\Contracts\V1\Export\StoreMemberExportServiceInterface;
use App\DTO\V1\StoreMember\CreateStoreMemberDTO;
use App\DTO\V1\StoreMember\ExportStoreMembersDTO;
use App\DTO\V1\StoreMember\ImportStoreMemberDTO;
use App\DTO\V1\StoreMember\UpdateStoreMemberDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreMember\ExportStoreMembersRequest;
use App\Http\Requests\V1\StoreMember\ImportStoreMemberRequest;
use App\Http\Requests\V1\StoreMember\StoreStoreMemberRequest;
use App\Http\Requests\V1\StoreMember\UpdateStoreMemberRequest;
use App\Http\Resources\V1\StoreMemberResource;
use App\Models\V1\Store;
use App\Models\V1\StoreMember;
use App\Services\V1\Auth\AbilityManager;
use App\Services\V1\StoreMember\StoreMemberImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class StoreMembersController extends Controller
{

    /**
     * GET /stores/{store}/members
     * Route: store.members.index
     */
    public function index(Store $store): AnonymousResourceCollection
    {
        $members = $store->storeMembers()
            ->with('user')
            ->orderBy('role')
            ->paginate(20);

        return StoreMemberResource::collection($members);
    }

    public function store(StoreStoreMemberRequest $request, Store $store, CreateStoreMember $action): JsonResponse
    {
        try {
            $storeMemberDTO = CreateStoreMemberDTO::fromRequest($store, $request->validated());
            $storeMember    = $action($storeMemberDTO);

            return response()->json([
                'message' => 'Store member créé avec succès',
                'data' => new StoreMemberResource($storeMember),
            ], 201);
        }
        catch (Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Erreur lors de la création du store member',
                'code'    => 'STORE_MEMBER_CREATE_FAILED',
            ], 500);
        }
    }

    /**
     * GET /api/v1/members/{store_member}
     * Route: members.show
     */
    public function show(StoreMember $storeMember): StoreMemberResource
    {
        $storeMember->load('user', 'store');
        return new StoreMemberResource($storeMember);
    }

    /**
     * PUT/PATCH /api/v1/members/{store_member}
     * Route: members.update
     */
    public function update(UpdateStoreMemberRequest $request, StoreMember $storeMember, UpdateStoreMember $action): JsonResponse
    {
        try {
            $updateStoreMemberDTO = UpdateStoreMemberDTO::fromRequest($request->validated());
            $updatedMember        = $action($storeMember, $updateStoreMemberDTO);

            return response()->json([
                'message' => 'Store member mis à jour avec succès',
                'data' => new StoreMemberResource($updatedMember),
            ], 201);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Erreur lors de la mise à jour du store member',
                'error'   => 'UPDATE_STORE_MEMBER',
            ], 500);
        }
    }

    /**
     * DELETE /api/v1/store-members/{store_member}
     */
    public function destroy(StoreMember $storeMember): Response|JsonResponse
    {
        if ($storeMember->isActive()) {
            return response()->json([
                'error'   => 'ACTIVE_SHIFT',
                'message' => 'Impossible de supprimer un membre avec un shift en cours',
            ], 409);
        }

        $storeMember->delete();

        return response()->noContent();
    }

    /**
     * DELETE api/v1/store-members/{id}/force-destroy
     * Route: members.force-destroy
     */
    public function forceDestroy(string $id): Response
    {
        $member = StoreMember::withTrashed()->findOrFail($id);
        $member->forceDelete();

        return response()->noContent();
    }

    /**
     * POST /api/v1/store-members/{id}/restore
     */
    public function restore(string $id): JsonResponse
    {
        $member = StoreMember::withTrashed()->findOrFail($id);

        if ($member->trashed()) {
            $member->restore();
            $member->refresh();
        }

        return new StoreMemberResource($member)
            ->response()
            ->setStatusCode(200);
    }

    /**
     * GET /api/v1/store-members/{store_member}/abilities
     * Route: store-members.abilities
     */
    public function listAbilities(StoreMember $storeMember, AbilityManager $abilityManager): JsonResponse
    {
        try {
            return response()->json([
                'data' => $abilityManager->getPermissionReport($storeMember),
            ]);
        } catch (Throwable $e) {
            report($e);
            return response()->json([
                'message' => 'Erreur lors de la récupération des permissions',
                'code'    => 'LIST_STORE_MEMBER_ABILITIES_FAILED',
            ], 500);
        }
    }

    /**
     * POST /api/v1/stores/{store}/members/impor
     * Route: store-members.import
     */
    public function import(Store $store, ImportStoreMemberRequest $request, StoreMemberImportService $service): JsonResponse
    {
        $data = ImportStoreMemberDTO::fromRequest($request->validated());
        $file = $request->file('file');

        if (! $data->async){
            $result = $service->importSync(
                file: $file,
                storeId: $store->id,
                useBatchMode: $data->useBatchMode,
                batchSize: $data->batchSize,
            );

            return response()->json([
                'message' => 'Import terminé',
                'data'    => $result->jsonSerialize(),
            ]);
        } else {
            $result = $service->importAsync($file, $store->id, $data->useBatchMode, $data->batchSize, $data->priority);
            return response()->json($result, 202);
        }
    }

    /**
     * GET /api/v1/stores/{store}/members/export
     * Route: store-members.export
     */
    public function export(Store $store, ExportStoreMembersRequest $request, StoreMemberExportServiceInterface $service): JsonResponse
    {
        $data = ExportStoreMembersDTO::fromRequest($request->validated());
        if (! $data->async){
            $result = $service->exportSync($store->id, $data);

            if ($result->isSuccess()){
                return response()->json([
                    'message' => $result->message,
                    'data'    => $result->toArray(),
                ]);
            } else {
                return response()->json([
                    'message' => $result->message,
                    'data'    => $result->toArray(),
                ], 500);
            }
        } else {
            $result = $service->exportAsync($store->id, $data, $data->priority);
            return response()->json($result, 202);
        }
    }

    /**
     * GET /api/v1/stores/{store}/exports/{jobId}/download
     * Route: store-members.exports.download
     */
    public function download(string $jobId, StoreMemberExportServiceInterface $service): BinaryFileResponse|JsonResponse
    {
        $exportResult = $service->getExportResult($jobId);

        if(! $exportResult || ! file_exists($exportResult->filePath)){
            return response()->json([
                'message' => 'Le fichier demandé est introuvable ou l\'export n\'a pas encore été généré',
                'code'    => 'EXPORT_NOT_FOUND',
            ], 404);
        }

        return new BinaryFileResponse(
            file: $exportResult->filePath,
            status: 200,
            headers: [
                'Content-Type'        => $this->getContentType($exportResult->format),
                'Content-Disposition' => 'attachment; filename="' . basename($exportResult->filePath) . '"',
            ]
        );
    }

    private function getContentType(string $format): string
    {
        return match ($format) {
            'csv'   => 'text/csv',
            'xlsx'  => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'json'  => 'application/json',
            default => 'text/plain',
        };
    }

    /**
     * GET /api/v1/store-members/{store_member}/audit
     */
    public function audit(StoreMember $storeMember): JsonResponse
    {
        return response()->json([
            'message' => 'Fonction d\'audit non encore implémentée',
            'code'    => 'AUDIT_NOT_IMPLEMENTED',
        ], 501);
//        try {
//            // On charge les données d'audit avec pagination
//            $auditLogs = $storeMember->audits()
//                ->with('user')
//                ->latest()
//                ->paginate(15);
//
//            return response()->json([
//                'data' => $auditLogs,
//                'meta' => [
//                    'member_id' => $storeMember->id,
//                    'member_name' => $storeMember->fullName,
//                ]
//            ]);
//        } catch (Throwable $e) {
//            report($e);
//            return response()->json([
//                'message' => 'Erreur lors de la récupération des données d\'audit',
//                'code'    => 'AUDIT_LOGS_RETRIEVAL_FAILED',
//            ], 500);
//        }
    }

    /**
     * GET /api/v1/store-members/{store_member}/stats
     * Récupère les statistiques d'un membre de magasin
     */
    public function stats(StoreMember $storeMember): JsonResponse
    {
        return response()->json([
            'message' => 'Fonction de statistiques non encore implémentée',
            'code'    => 'STATS_NOT_IMPLEMENTED',
        ], 501);
//        try {
//            $stats = [
//                'total_shifts' => $storeMember->shifts()->count(),
//                'total_hours'  => $storeMember->shifts()->sum('duration_minutes') / 60,
//                'last_login'   => $storeMember->last_login_at,
//                'created_at'   => $storeMember->created_at,
//                // Ajoutez d'autres statistiques pertinentes ici
//            ];
//
//            return response()->json([
//                'data' => $stats,
//                'meta' => [
//                    'member_id' => $storeMember->id,
//                    'member_name' => $storeMember->fullName,
//                ]
//            ]);
//        } catch (Throwable $e) {
//            report($e);
//            return response()->json([
//                'message' => 'Erreur lors de la récupération des statistiques',
//                'code'    => 'STATS_RETRIEVAL_FAILED',
//            ], 500);
//        }
    }

    /**
     * GET /api/v1/stores/{store}/members/search
     * Recherche avancée de membres dans un magasin
     */
    public function search(Store $store, Request $request): AnonymousResourceCollection
    {
        $query = $store->storeMembers()->with('user');

        if ($request->has('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->has('q')) {
            $searchTerm = $request->input('q');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('first_name', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('code', 'like', "%{$searchTerm}%");
            });
        }

        if ($request->has('active')) {
            $isActive = filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN);
            if ($isActive) {
                $query->whereNull('deleted_at');
            } else {
                $query->onlyTrashed();
            }
        }

        $sortField = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $allowedSortFields = ['first_name', 'last_name', 'email', 'role', 'created_at', 'updated_at'];

        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDir === 'asc' ? 'asc' : 'desc');
        }

        $perPage = min(max((int)$request->input('per_page', 15), 5), 50);
        $members = $query->paginate($perPage);

        return StoreMemberResource::collection($members);
    }
}

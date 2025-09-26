<?php

namespace App\Http\Controllers\V1;

use App\Contracts\V1\Export\StoreMemberExportServiceInterface;
use App\Contracts\V1\StoreMember\StoreMemberServiceInterface;
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
use App\Services\V1\StoreMember\StoreMemberImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class StoreMembersController extends Controller
{

    public function __construct(
        private readonly StoreMemberServiceInterface $storeMemberService
    ){}

    /**
     * GET /stores/{store}/members
     */
    public function index(Request $request, Store $store): JsonResponse
    {
        $filters = $request->only(['is_active', 'search', 'role', 'locked']);
        $perPage = (int) $request->get('per_page', 25);

        $storeMembers = $this->storeMemberService->list($store->id, $filters, $perPage);

        return response()->json([
            'data' => StoreMemberResource::collection($storeMembers),
            'meta' => [
                'current_page' => $storeMembers->currentPage(),
                'per_page'     => $storeMembers->perPage(),
                'total'        => $storeMembers->total(),
                'last_page'    => $storeMembers->lastPage(),
            ]
        ]);
    }

    public function store(StoreStoreMemberRequest $request, Store $store): JsonResponse
    {
        try {
            $data        = CreateStoreMemberDTO::fromRequest($store, $request->validated());
            $storeMember = $this->storeMemberService->create($data);

            Log::info("Store member créé", [
                'store_member_id' => $storeMember->id,
                'store_id'        => $store->id,
                'name'            => $storeMember->name,
                'role'            => $storeMember->role,
            ]);

            return response()->json([
                'message' => 'Store member créé avec succès',
                'data'    => new StoreMemberResource($storeMember),
            ], 201);
        }
        catch (Throwable $e) {
            Log::error("Erreur lors de la création du store member", [
                'store_id' => $store->id,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erreur lors de la création du store member',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/v1/members/{store_member}
     */
    public function show(StoreMember $storeMember): StoreMemberResource
    {
        $storeMember->load('user', 'store');
        return new StoreMemberResource($storeMember);
    }

    /**
     * PUT/PATCH /api/v1/members/{store_member}
     */
    public function update(UpdateStoreMemberRequest $request, StoreMember $storeMember): JsonResponse
    {
        try {
            $data          = UpdateStoreMemberDTO::fromRequest($request->validated());
            $updatedMember = $this->storeMemberService->update($storeMember, $data);

            Log::info("Store member mis à jour", [
                'store_member_id' => $updatedMember->id,
                'store_id'        => $updatedMember->store_id,
                'name'            => $updatedMember->name,
                'role'            => $updatedMember->role,
            ]);

            return response()->json([
                'message' => 'Store member mis à jour avec succès',
                'data'    => new StoreMemberResource($updatedMember),
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de la mise à jour du store member", [
                'store_member_id' => $storeMember->id,
                'store_id'        => $storeMember->store_id,
                'error'           => $e->getMessage(),
                'trace'           => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Erreur lors de la mise à jour du store member',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * DELETE /api/v1/store-members/{store_member}
     */
    public function destroy(StoreMember $storeMember): Response|JsonResponse
    {
        $deleted = $this->storeMemberService->delete($storeMember);

        if ($deleted) {
            Log::info("Store member supprimé", [
                'store_member_id' => $storeMember->id,
                'store_id'        => $storeMember->store_id,
                'name'            => $storeMember->name,
            ]);

            return response()->noContent();
        } else {
            Log::error("Erreur lors de la suppression du store member", [
                'store_member_id' => $storeMember->id,
                'store_id'        => $storeMember->store_id,
            ]);

            return response()->json([
                'message' => 'Erreur lors de la suppression du store member',
            ], 500);
        }
    }

    /**
     * DELETE api/v1/store-members/{id}/force-destroy
     */
    public function forceDestroy(string $id): JsonResponse
    {
        $deleted = $this->storeMemberService->forceDelete($id);

        if ($deleted) {
            Log::info("Store member définitivement supprimé", [
                'store_member_id' => $id,
            ]);

            return response()->json([
                'message' => 'Store member définitivement supprimé avec succès',
            ]);
        } else {
            Log::error("Erreur lors de la suppression définitive du store member", [
                'store_member_id' => $id,
            ]);

            return response()->json([
                'message' => 'Erreur lors de la suppression définitive du store member',
            ], 500);
        }
    }

    /**
     * POST /api/v1/store-members/{id}/restore
     */
    public function restore(string $id): JsonResponse
    {
        $restored = $this->storeMemberService->restore($id);

        if ($restored) {
            Log::info("Store member restauré", [
                'store_member_id' => $id,
            ]);

            return response()->json([
                'message' => 'Store member restauré avec succès',
            ]);
        } else {
            Log::error("Erreur lors de la restauration du store member", [
                'store_member_id' => $id,
            ]);

            return response()->json([
                'message' => 'Erreur lors de la restauration du store member',
            ], 500);
        }
    }

    /**
     * POST /api/v1/stores/{store}/members/impor
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

}

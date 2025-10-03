<?php

namespace App\Support\Import\StoreMember;

use App\Constants\V1\Defaults;
use App\Enum\V1\StoreMemberRole;
use App\Events\V1\Import\ImportCompleted;
use App\Models\V1\StoreMember;
use App\Services\V1\StoreMember\StoreMemberCodeService;
use App\Services\V1\StoreMember\StoreMemberPermissionsService;
use App\Support\Import\BaseImporter;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreMemberSyncImport extends BaseImporter
{

    public function __construct(
        string                                          $filePath,
        private readonly int                            $storeId,
        private readonly ?StoreMemberCodeService        $codeService = null,
        private readonly ?StoreMemberPermissionsService $permissionsService = null,
        array                                           $options = []
    ){
        if (isset($options['useBatchMode'])){
            $this->useBatchMode = (bool) $options['useBatchMode'];
        }
        if (isset($options['batchSize']) && is_int($options['batchSize']) && $options['batchSize'] > 0){
            $this->batchSize = $options['batchSize'];
        }
        parent::__construct($filePath);
    }

    protected function validateRow(Collection $row, int $rowIndex): void
    {

    }

    protected function prepareData(Collection $row): array
    {
        $role = StoreMemberRole::from((string) $row['role']);

        return [
            'store_id'            => (int) $row['store_id'],
            'name'                => (string) $row['name'],
            'role'                => $role,
            'is_active'           => $row['is_active'] ?? true,
            'meta'                => isset($row['meta']) ? json_decode($row['meta'], true) : null,
            'permissions'         => $row['permissions'] ?? $this->permissionsService->getByRole($role),
            'pin_hash'            => bcrypt($row['pin'] ?? Defaults::PIN),
            'code_number'         => $row['code_number'] ?? $this->codeService->next((int) $row['store_id'], $role),
            'pin_last_changed_at' => now()
        ];
    }

    protected function getErrorContext(array $data): string
    {
        return "Store Member: {$data['name']} (Store ID: {$data['store_id']})";
    }

    protected function processEntity(array $data, Collection $row, int $rowIndex): void
    {
        $storeMember = StoreMember::query()
            ->updateOrCreate([
                'name'     => $data['name'],
                'role'     => $data['role'],
                'store_id' => $data['store_id']
            ], $data);

        $this->importResult->addResult('store_member', $storeMember->only(['id', 'name', 'role', 'code_number']));
    }

    public function registerEvents(): array
    {
        return [
            new ImportCompleted($this->importResult, $this->storeId)
        ];
    }

    public function rules(): array
    {
        return [
            '*.store_id' => ['required', 'integer', Rule::exists('stores', 'id')],
            '*.name'     => ['bail', 'required', 'string', 'max:120'],
            '*.role'     => ['bail', 'required', new Enum(StoreMemberRole::class)],
            '*.pin'      => [
                'required',
                'integer',
                'regex:/^\d{4,8}$/',
                Rule::requiredIf(fn() => in_array($this->role ?? StoreMemberRole::Cashier, [StoreMemberRole::Owner, StoreMemberRole::Manager], true)),
            ],
            '*.is_active' => ['sometimes', 'boolean'],
            '*.meta'      => ['nullable', 'string', 'json'],
        ];
    }
}

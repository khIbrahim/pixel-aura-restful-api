<?php

namespace App\Support\Export\StoreMember;

use App\Constants\V1\Defaults;
use App\Models\V1\StoreMember;
use App\Support\Export\BaseExporter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class StoreMemberSyncExport extends BaseExporter
{

    public function __construct(
        private readonly int $storeId,
        private readonly array $filters = []
    ){
        parent::__construct();
    }

    /** @return Collection<StoreMember> */
    public function collection(): Collection
    {
        $query = StoreMember::query()
            ->where('store_id', $this->storeId);

        $query = $this->applyFilters($query);

        $collection        = $query->get();
        $this->recordCount = $collection->count();

        return $collection;
    }

    private function applyFilters(Builder $query): Builder
    {
        if (isset($this->filters['role'])) {
            $query->where('role', $this->filters['role']);
        }
        if (isset($this->filters['is_active'])) {
            $query->where('is_active', (bool) $this->filters['is_active']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'store_id',
            'name',
            'role',
            'is_active',
            'meta',
            'permissions',
            'pin',
        ];
    }

    /**
     * @param StoreMember $row
     * @return array
     */
    public function map($row): array
    {
        return [
            'store_id'    => $row->store_id,
            'name'        => $row->name,
            'role'        => $row->role->value,
            'is_active'   => $row->is_active,
            'meta'        => json_encode($row->meta),
            'permissions' => json_encode($row->permissions),
            'pin'         => Defaults::PIN,
        ];
    }

    public function title(): string
    {
        return 'Store Members';
    }
}

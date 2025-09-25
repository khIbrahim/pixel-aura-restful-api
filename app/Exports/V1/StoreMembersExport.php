<?php

namespace App\Exports\V1;

use App\Models\V1\StoreMember;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;

class StoreMembersExport extends DefaultValueBinder implements
class StoreMembersExport implements FromCollection
    use Exportable;

    public function __construct(
        private readonly int $storeId,
        private readonly bool $includePin = false,
        private readonly bool $includePermissions = true,
        private readonly bool $includeMeta = false,
    ){}

    /**
     * @return Collection<StoreMember>
     */
    public function collection(): Collection
        return StoreMember::query()
            ->where('store_id', $this->storeId)
            ->orderBy('role')
            ->orderBy('name')
            ->get();
    }

    /**
     * Define headings for the Excel file
     *
    {
        $headings = [
            'store_id',
    /** @return Collection<StoreMember> */
            'is_active',
        ];

        if ($this->includePin) {
            $headings[] = 'pin';
        }

        }

        if ($this->includeMeta) {
            $headings[] = 'meta';
}

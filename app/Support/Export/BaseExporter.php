<?php

namespace App\Support\Export;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\DefaultValueBinder;

abstract class BaseExporter extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithTitle
{

    protected int $recordCount = 0;
    protected float $startTime;

    public function __construct()
    {
        $this->startTime = microtime(true);
    }

    public function getProcessTimeMs(): int
    {
        return (int)((microtime(true) - $this->startTime) * 1000);
    }

    public function getRecordCount(): int
    {
        return $this->recordCount;
    }

    public function title(): string
    {
        return 'Export';
    }

    abstract public function collection();
    abstract public function headings(): array;
    abstract public function map($row): array;

}

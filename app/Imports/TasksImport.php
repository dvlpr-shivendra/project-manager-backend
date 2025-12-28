<?php

namespace App\Imports;

use App\Services\TaskImportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TasksImport implements ToCollection, WithHeadingRow
{
    public function __construct(private int $projectId) {}

    public function collection(Collection $rows): void
    {
        $rows->each(fn ($row) =>
            app(TaskImportService::class)
                ->handle($row, $this->projectId)
        );
    }
}

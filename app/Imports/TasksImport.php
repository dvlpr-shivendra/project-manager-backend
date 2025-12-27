<?php

namespace App\imports;

use App\Services\TaskImportService;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class TasksImport implements ToCollection
{
    public function __construct(private int $projectId) {}

    public function collection(Collection $rows)
    {
        $rows->skip(1)->each(fn ($row) =>
            app(TaskImportService::class)->handle($row, $this->projectId)
        );
    }
}


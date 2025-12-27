<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;

class TasksExport implements FromCollection
{
    public function collection()
    {
        return Task::with(['creator', 'assignee'])->get()->map(fn ($t) => [
            'title' => $t->title,
            'description' => $t->description,
            'creator_email' => $t->creator->email,
            'assignee_email' => optional($t->assignee)->email,
            'project_id' => $t->project_id,
            'deadline' => $t->deadline,
            'time_estimate' => $t->time_estimate,
            'is_complete' => $t->is_complete,
        ]);
    }
}

<?php

namespace App\Exports;

use App\Models\Task;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TasksExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Task::with(['creator', 'assignee'])->get()->map(fn ($t) => [
            'title' => $t->title,
            'description' => $t->description,
            'creator_email' => $t->creator->email,
            'assignee_email' => optional($t->assignee)->email,
            'deadline' => $t->deadline,
            'time_estimate' => $t->time_estimate,
            'is_complete' => $t->is_complete,
        ]);
    }

    public function headings(): array
    {
        return [
            'title',
            'description',
            'creator_email',
            'assignee_email',
            'deadline',
            'time_estimate',
            'is_complete',
        ];
    }
}
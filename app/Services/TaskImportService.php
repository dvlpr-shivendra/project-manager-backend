<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;

class TaskImportService
{
    public function handle($row, $projectId)
    {
        Task::updateOrCreate(
            [
                'project_id' => $projectId,
                'title' => $row[0],
            ],
            [
                'description' => $row[1],
                'creator_id' => User::whereEmail($row[2])->firstOrFail()->id,
                'assignee_id' => User::whereEmail($row[3])->value('id'),
                'deadline' => empty($row[4]) ? null : $row[4],
                'time_estimate' => is_numeric($row[5]) ? (int) $row[5] : null,
                'is_complete' => filter_var($row[6], FILTER_VALIDATE_BOOLEAN),
            ]
        );
    }
}

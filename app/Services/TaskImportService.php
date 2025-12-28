<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

class TaskImportService
{
    public function handle(Collection $row, int $projectId): ?Task
    {
        if (empty($row['title']) || empty($row['creator_email'])) {
            return null;
        }

        $creatorId = User::whereEmail($row['creator_email'])->value('id');
        if (!$creatorId) {
            return null;
        }

        $assigneeId = !empty($row['assignee_email'])
            ? User::whereEmail($row['assignee_email'])->value('id')
            : null;

        return Task::updateOrCreate(
            [
                'project_id' => $projectId,
                'title' => $row['title'],
            ],
            [
                'description' => $row['description'] ?? null,
                'creator_id' => $creatorId,
                'assignee_id' => $assigneeId,
                'deadline' => empty($row['deadline']) ? null : $row['deadline'],
                'time_estimate' => is_numeric($row['time_estimate'] ?? null)
                    ? (int) $row['time_estimate']
                    : null,
                'is_complete' => filter_var($row['is_complete'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]
        );
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;

class SearchController extends Controller
{
    public function __invoke()
    {
        if (env('DB_CONNECTION') === 'pgsql') return $this->postgresSearchProjectsAndTasks();
        else if (env('DB_CONNECTION') === 'mysql') return $this->mysqlSearchProjectsAndTasks();
        else [];
    }

    public function postgresSearchProjectsAndTasks()
    {
        $search = request()->searchQuery;

        $projects = Project::selectRaw("
            id,
            name,
            ts_rank(
                to_tsvector('english', coalesce(name,'') || ' ' || coalesce(description,'')),
                plainto_tsquery('english', ?)
            ) as rank
        ", [$search])
            ->whereRaw("
            to_tsvector('english', coalesce(name,'') || ' ' || coalesce(description,''))
            @@ plainto_tsquery('english', ?)
        ", [$search])
            ->orderByDesc('rank')
            ->take(10)
            ->get();

        $tasks = Task::selectRaw("
            id,
            title,
            project_id,
            ts_rank(
                to_tsvector('english', coalesce(title,'') || ' ' || coalesce(description,'')),
                plainto_tsquery('english', ?)
            ) as rank
        ", [$search])
            ->whereRaw("
            to_tsvector('english', coalesce(title,'') || ' ' || coalesce(description,''))
            @@ plainto_tsquery('english', ?)
        ", [$search])
            ->with('project:id,name')
            ->orderByDesc('rank')
            ->take(10)
            ->get();

        return [
            'projects' => $projects,
            'tasks' => $tasks,
        ];
    }

    public function mysqlSearchProjectsAndTasks()
    {
        return [];
    }
}

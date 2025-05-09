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
        $query = preg_replace('!\s+!', ':*|', request()->searchQuery); // replace spaces with :*|

        $query .= ':*';

        $projects = Project::whereRaw("name @@ to_tsquery('$query') OR description @@ to_tsquery('$query')")
            ->take(10)
            ->get(['id', 'name']);

        $tasks = Task::whereRaw("title @@ to_tsquery('$query') OR description @@ to_tsquery('$query')")
            ->with('project:id,name')->take(10)
            ->get(['id', 'title', 'project_id']);

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

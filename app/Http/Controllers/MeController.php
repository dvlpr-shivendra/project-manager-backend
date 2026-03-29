<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class MeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
            'stats' => [
                'projects_count' => Project::count(),
                'open_tasks_count' => Task::where('is_complete', false)->count(),
                'completed_tasks_count' => Task::where('is_complete', true)->count(),
                'overdue_tasks_count' => Task::where('is_complete', false)->where('deadline', '<', now())->count(),
            ]
        ];
    }
}

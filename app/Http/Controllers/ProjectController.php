<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

use App\Exports\TasksExport;
use App\imports\TasksImport;

use Maatwebsite\Excel\Facades\Excel;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Project::query()->withCount(['tasks', 'completedTasks']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query->paginate(20);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        return Project::create(
            array_merge(
                $request->validate([
                    'name' => ['required', 'string'],
                    'description' => ['string'],
                ]),
                ['user_id' => $request->user()->id]
            )
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {
        return $project;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Project $project)
    {
        $project->update($request->all());

        return $project;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Project  $project
     * @return \Illuminate\Http\Response
     */
    public function destroy(Project $project)
    {
        return $project->delete();
    }

    public function export()
    {
        return Excel::download(new TasksExport, 'tasks.xlsx');
    }

    public function import(Request $request, int $id)
    {   
        // TODO: Add validation
        // $request->validate([
        //     'file' => ['required', 'file', 'mimes:xlsx,csv'],
        // ]);

        Excel::import(new TasksImport($id), $request->file('file'));
        
        return response()->noContent();
    }
}

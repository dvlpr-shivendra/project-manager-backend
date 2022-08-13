<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Task::where('project_id', request()->project_id)
            ->orderByDesc('updated_at')
            ->paginate(100);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $task = Task::create(array_merge(
            $request->validate([
                'title' => ['required'],
                'project_id' => 'required|exists:projects,id',
            ]),
            [
                'creator_id' => $request->user()->id,
                'assignee_id' => $request->user()->id,
            ]
        ));

        return $task->loadMissing(['assignee', 'tags']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        return $task;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        // validate if task title is unique inside project
        $request->validate([
            'title' => ['required', 'unique:tasks,title,NULL,id,project_id,' . $task->project_id],
        ]);

        $task->update($request->all());

        return $task;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        return $task->delete();
    }

    public function addTag(Task $task, $tagId)
    {
        return $task->tags()->attach($tagId) > 0;
    }

    public function destroyTag(Task $task, $tagId)
    {
        return $task->tags()->detach($tagId) > 0;
    }
}

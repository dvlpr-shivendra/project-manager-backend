<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;

class TaskCommentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Task $task)
    {
        return $task->comments;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Task  $task
     * @return \Illuminate\Http\Response
     */
    public function store(Task $task)
    {
        $attributes = request()->validate([
            'body' => ['required']
        ]);

        $attributes['user_id'] = request()->user()->id;

        return $task->comments()->create($attributes);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TaskComment $comment
     * @return \Illuminate\Http\Response
     */
    public function show(TaskComment $comment)
    {
        return $comment;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TaskComment $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TaskComment $comment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TaskComment $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy(TaskComment $comment)
    {
        $comment->delete();

        return response()->status(204);
    }
}

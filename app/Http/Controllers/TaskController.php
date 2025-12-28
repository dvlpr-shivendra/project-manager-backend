<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tasks = Task::where('project_id', request()->project_id)
            ->orderByDesc('updated_at');

        if (request()->tag) {
            $tasks->whereRelation('tags', 'tags.name', 'ILIKE', '%' . request()->tag . '%');
        }

        if (request()->assignee) {
            $tasks->whereRelation('assignee', 'users.name', 'ILIKE', '%' . request()->assignee . '%');
        }

        return $tasks->paginate(100);
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

        return $task->loadMissing(['assignee', 'tags', 'attachments', 'followers']);
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
        if ($request->title && Task::where('title', $request->title)->where('id', '!=', $task->id)->where('project_id', $task->project_id)->exists()) {
            return response()->json(['message' => 'Task with this title already exists'], 422);
        }

        $task->update($request->all());

        return $task;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function bulkDelete(Request $request)
    {
        $ids = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:tasks,id'],
        ])['ids'];

        Task::whereIn('id', $ids)->delete();

        return response()->noContent();
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
        $task->tags()->attach($tagId);

        return ['message' => 'Tag added successfully'];
    }

    public function destroyTag(Task $task, $tagId)
    {
        $task->tags()->detach($tagId);

        return ['message' => 'Tag removed successfully'];
    }

    public function addAttachment(Task $task, Request $request)
    {
        $request->validate([
            'file' => 'required|max:2048',
        ]);

        $path = $request->file('file')->store('attachments');
        $size = $request->file('file')->getSize();
        $url = Storage::url($path);

        $attachment = $task->attachments()->create([
            'path' => $path,
            'size' => $size,
            'url' => $url,
            'name' => $request->file('file')->getClientOriginalName(),
        ]);

        return $attachment;
    }

    public function destroyAttachment(Task $task, $attachmentId)
    {
        $attachment = $task->attachments()->find($attachmentId);

        if (!$attachment) {
            return response()->json(['message' => 'Attachment not found'], 404);
        }

        $attachment->delete();

        return ['message' => 'Attachment deleted successfully'];
    }

    public function addFollower(Task $task, $userId)
    {
        $task->followers()->attach($userId);

        return ['message' => 'Follower added successfully'];
    }

    public function destroyFollower(Task $task, $userId)
    {
        $task->followers()->detach($userId);

        return ['message' => 'Follower removed successfully'];
    }
}

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
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'project_id' => 'required|exists:projects,id',
            'assignee_id' => 'nullable|exists:users,id',
            'tags' => 'nullable|array',
        ]);

        $data['creator_id'] = $request->user()->id;

        if (!isset($data['assignee_id'])) {
            $data['assignee_id'] = $request->user()->id;
        }

        $task = Task::create($data);

        if ($request->has('tags')) {
            $this->syncTags($task, $request->input('tags'));
        }

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
        $task->update($request->all());

        if ($request->has('tags')) {
            $this->syncTags($task, $request->input('tags'));
        }

        if ($request->has('add_tags')) {
            $this->attachTags($task, $request->input('add_tags'));
        }

        if ($request->has('remove_tags')) {
            $this->detachTags($task, $request->input('remove_tags'));
        }

        return $task->loadMissing(['assignee', 'tags', 'attachments', 'followers']);
    }

    private function resolveTagIds(array $tags): array
    {
        $tagIds = [];
        foreach ($tags as $tagName) {
            $tag = \App\Models\Tag::firstOrCreate(
                ['name' => $tagName],
                [
                    'color' => '#ffffff',
                    'background_color' => '#3b82f6' // Default blue
                ]
            );
            $tagIds[] = $tag->id;
        }
        return $tagIds;
    }

    private function syncTags(Task $task, array $tags)
    {
        $task->tags()->sync($this->resolveTagIds($tags));
    }

    private function attachTags(Task $task, array $tags)
    {
        $task->tags()->syncWithoutDetaching($this->resolveTagIds($tags));
    }

    private function detachTags(Task $task, array $tags)
    {
        $task->tags()->detach($this->resolveTagIds($tags));
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

    public function downloadAttachment(Task $task, $attachmentId)
    {
        $attachment = $task->attachments()->findOrFail($attachmentId);

        if (!Storage::exists($attachment->path)) {
            abort(404, 'File not found');
        }

        return Storage::download($attachment->path, $attachment->name);
    }
}

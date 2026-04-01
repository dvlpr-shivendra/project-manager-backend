<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use function Laravel\Ai\agent;

class ChatController extends Controller
{
    public function handle(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        // Fetch context to inject into prompt
        $projects = Project::select('id', 'name')->get();
        $users    = User::select('id', 'name')->get();
        $tags     = \App\Models\Tag::select('id', 'name')->get();
        $me       = Auth::user();

        $prompt = $this->buildPrompt(
            message: $request->input('message'),
            projects: $projects,
            users: $users,
            tags: $tags,
            currentUserId: $me->id,
        );

        $raw = (string) agent()->prompt($prompt);

        // Strip possible markdown code fences from model output
        $json = preg_replace('/^```json\s*|\s*```$/m', '', trim($raw));

        $intent = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('ChatController: failed to parse AI response', ['raw' => $raw]);
            return response()->json([
                'error' => 'The AI returned an unexpected response. Please try again.',
            ], 422);
        }

        return response()->json($intent);
    }

    // -------------------------------------------------------------------------

    private function buildPrompt(
        string $message,
        $projects,
        $users,
        $tags,
        int $currentUserId,
    ): string {
        $today        = Carbon::now()->toDateTimeString();
        $projectsList = $projects->map(fn($p) => "  - id: {$p->id}, name: \"{$p->name}\"")->implode("\n");
        $usersList    = $users->map(fn($u) => "  - id: {$u->id}, name: \"{$u->name}\"")->implode("\n");
        $tagsList     = $tags->map(fn($t) => "  - id: {$t->id}, name: \"{$t->name}\"")->implode("\n");

return <<<PROMPT
You are a project and task management assistant. Extract the user's intent from their message and return ONLY a valid JSON object — no explanation, no markdown, no code fences.

Today is: {$today}
Current user id (creator): {$currentUserId}

Available projects:
{$projectsList}

Available users:
{$usersList}

Available tags:
{$tagsList}

Return this exact JSON structure:
{
  "action": "create" | "update" | "delete" | "list" | "clarify",
  "resource_type": "task" | "project",
  "task_id": number | null,
  "project_id": number | null,
  "data": {
    "title": string | null,        // tasks
    "name": string | null,         // projects
    "description": string | null,
    "assignee_id": number | null,  // tasks
    "project_id": number | null,   // tasks
    "deadline": "YYYY-MM-DD HH:MM:SS" | null,
    "time_estimate": number | null,
    "is_complete": boolean | null,
    "tags": string[] | null,       // for creation - list of tag names
    "add_tags": string[] | null,    // for updates - tags to add
    "remove_tags": string[] | null  // for updates - tags to remove
  },
  "filters": {
    "assignee_id": number | null,
    "project_id": number | null,
    "is_complete": boolean | null,
    "deadline_before": "YYYY-MM-DD HH:MM:SS" | null,
    "search": string | null,
    "tag": string | null
  },
  "confirmation_message": string,
  "question": string | null
}

Rules:
- Default "resource_type" is "task" unless the user explicitly mentions "project".
- For "create": title and project_id are required.
- data.tags (create) / data.add_tags (update): Extract tag names. Prefer names from "Available tags" if they match, otherwise use the new names.
- Resolve relative dates ("tomorrow", "next Friday") against today.
- confirmation_message: a short, friendly summary of the action.

Title and Description rules:
- Never include action phrases like "create a task" in title/name.
- Generate a concise 3-8 word title/name from the core subject.

User message: "{$message}"
PROMPT;
    }
}
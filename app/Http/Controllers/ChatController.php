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
        $me       = Auth::user();

        $prompt = $this->buildPrompt(
            message: $request->input('message'),
            projects: $projects,
            users: $users,
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
        int $currentUserId,
    ): string {
        $today        = Carbon::now()->toDateTimeString();
        $projectsList = $projects->map(fn($p) => "  - id: {$p->id}, name: \"{$p->name}\"")->implode("\n");
        $usersList    = $users->map(fn($u) => "  - id: {$u->id}, name: \"{$u->name}\"")->implode("\n");

return <<<PROMPT
You are a task management assistant. Extract the user's intent from their message and return ONLY a valid JSON object — no explanation, no markdown, no code fences.

Today is: {$today}
Current user id (creator): {$currentUserId}

Available projects:
{$projectsList}

Available users (for assignment):
{$usersList}

Return this exact JSON structure:
{
  "action": "create" | "update" | "delete" | "list" | "clarify",
  "task_id": number | null,
  "data": {
    "title": string | null,
    "description": string | null,
    "assignee_id": number | null,
    "project_id": number | null,
    "deadline": "YYYY-MM-DD HH:MM:SS" | null,
    "time_estimate": number | null,
    "is_complete": boolean | null
  },
  "filters": {
    "assignee_id": number | null,
    "project_id": number | null,
    "is_complete": boolean | null,
    "deadline_before": "YYYY-MM-DD HH:MM:SS" | null
  },
  "confirmation_message": string,
  "question": string | null
}

Rules:
- For "create": title and project_id are required. If project_id cannot be determined, set action to "clarify".
- For "update" and "delete": task_id must be present. If not mentioned, set action to "clarify".
- For "list": populate filters with whatever the user specified, leave others null.
- For "clarify": set question to what you need to know, leave data empty.
- assignee_id must come from the users list above — never invent one.
- project_id must come from the projects list above — never invent one.
- If only one project exists, use it automatically for "create".
- Resolve relative dates ("tomorrow", "next Friday", "end of month") against today's date.
- time_estimate must be in minutes (e.g. "2 hours" → 120).
- confirmation_message should be a short, friendly human-readable summary of what will happen.
- Only populate fields relevant to the action — set everything else to null.

Title and description rules:
- Never include action phrases like "create a task", "add a task", "make a task" in either title or description.
- Short message (e.g. "Create a task to call John") → strip the action prefix, use the core intent as title (e.g. "Call John"), description null.
- Detailed message (e.g. "Create a task to integrate Stripe, handle webhooks and failed payment emails...") → strip the action prefix, generate a concise 3-6 word title from the core subject (e.g. "Integrate Stripe Payment Flow"), use the remaining detail as description.
- If the user explicitly provides "title: X" and/or "description: Y" → use exactly as provided, do not modify.
- Description should only contain the actual task detail — never the action intent ("create", "add", "make") that triggered the request.

User message: "{$message}"
PROMPT;
    }
}
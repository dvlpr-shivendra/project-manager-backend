<?php

namespace App\Http\Controllers;

use App\Ai\Agents\ProjectAssistant;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ChatController extends Controller
{
    public function handle(Request $request)
    {
        $request->validate([
            'message'         => 'required|string|max:1000',
            'conversation_id' => 'nullable|uuid',
        ]);

        // Fetch context to inject into instructions
        $projects = Project::select('id', 'name')->get();
        $users    = User::select('id', 'name')->get();
        $tags     = \App\Models\Tag::select('id', 'name')->get();
        $me       = Auth::user();

        $relevantContext = $this->findRelevantContext($request->input('message'), $me->id);

        $instructions = $this->buildInstructions(
            projects: $projects,
            users: $users,
            tags: $tags,
            currentUserId: $me->id,
            relevantContext: $relevantContext,
        );

        $agent = new ProjectAssistant($instructions);

        if ($request->filled('conversation_id')) {
            $agent->continue($request->input('conversation_id'), as: $me);
        } else {
            $agent->forUser($me);
        }

        /** @var \Laravel\Ai\Responses\Response $response */
        $response = $agent->prompt($request->input('message'));
        $intent = (array) $response->structured;

        if (empty($intent) || empty($intent['action']) || empty($intent['confirmation_message'])) {
            Log::error('ChatController: AI failed to return valid structured data', ['raw' => (string) $response]);
            return response()->json([
                'error' => 'The AI returned an unexpected response format. Please try again.',
            ], 422);
        }

        // Include conversation_id in the response for the frontend to track
        $intent['conversation_id'] = $response->conversationId;

        return response()->json($intent);
    }

    // -------------------------------------------------------------------------

    private function buildInstructions(
        $projects,
        $users,
        $tags,
        int $currentUserId,
        string $relevantContext = ""
    ): string {
        $today        = Carbon::now()->toDateTimeString();
        $projectsList = $projects->map(fn($p) => "  - id: {$p->id}, name: \"{$p->name}\"")->implode("\n");
        $usersList    = $users->map(fn($u) => "  - id: {$u->id}, name: \"{$u->name}\"")->implode("\n");
        $tagsList     = $tags->map(fn($t) => "  - id: {$t->id}, name: \"{$t->name}\"")->implode("\n");

        return <<<PROMPT
You are a project and task management assistant. Extract the user's intent.

Today is: {$today}
Current user id (creator): {$currentUserId}

Available projects:
{$projectsList}

Available users:
{$usersList}

Available tags:
{$tagsList}

### RELEVANT DATA (USE THESE IDs IMMEDIATELY)
{$relevantContext}

Rules:
- Default "resource_type" is "task" unless the user explicitly mentions "project".
- For "create" (task): title and data.project_id EXCEPTIONAL MANDATORY.
- For "update"/"delete": task_id (if task) or project_id (if project) is MANDATORY.
- TAGS RULE: For 'tags', 'add_tags', 'remove_tags', ALWAYS use the tag NAME (string), NEVER use the ID. 
- Mapping Rule: Always put properties into the "data" object for create/update and into "filters" for list.
- Resolve names to IDs: ALWAYS check the "RELEVANT DATA" block ABOVE before asking the user for an ID. if an ID is there, USE IT immediately. You are FORBIDDEN from asking the user for an ID if it exists in the 'RELEVANT DATA' section above.
- NEVER leave data.project_id empty if you mention the project name in confirmation_message.
- Resolve relative dates ("tomorrow", "next Friday") against today.
- If your previous message was a question (action: "clarify"), interpret the user's next message primarily as an answer to that question.

Task/Project Title creation rules:
- Never include action phrases like "create a task" in title/name.
- Generate a concise 3-8 word title/name from the core subject. 
PROMPT;
    }

    private function findRelevantContext(string $message, int $userId): string
    {
        $context = [];

        // 1. Always fetch recent activity to handle "the one I just created"
        $recentTasks = \App\Models\Task::where('creator_id', $userId)
            ->orWhere('assignee_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->limit(15)
            ->get();
        
        if ($recentTasks->isNotEmpty()) {
            $context[] = "#### YOUR RECENT ACTIVITY (USE THESE FOR 'RECENT' OR 'THIS' REFERENCES)";
            foreach ($recentTasks as $task) {
                $context[] = "Task: \"{$task->title}\" id: {$task->id} (Status: " . ($task->is_complete ? 'Complete' : 'Pending') . ")";
            }
        }

        // 2. Search for project names mentioned in message
        $projects = Project::select('id', 'name')->get();
        $foundMatches = false;
        foreach ($projects as $project) {
            if (stripos($message, $project->name) !== false) {
                if (!$foundMatches) { $context[] = "\n#### MATCHES FOR YOUR MESSAGE"; $foundMatches = true; }
                $context[] = "Project: \"{$project->name}\" id: {$project->id}";
            }
        }

        // 3. Search for task titles mentioned in message
        $tasks = \App\Models\Task::orderBy('id', 'desc')->limit(100)->get(); 
        foreach ($tasks as $task) {
            if (stripos($message, $task->title) !== false) {
                if (!$foundMatches) { $context[] = "\n#### MATCHES FOR YOUR MESSAGE"; $foundMatches = true; }
                $context[] = "Task: \"{$task->title}\" id: {$task->id} (Project ID: {$task->project_id})";
            }
        }

        if (empty($context)) {
            return "No specific resources found in message scan.";
        }

        return "\n" . implode("\n", array_unique($context));
    }
}
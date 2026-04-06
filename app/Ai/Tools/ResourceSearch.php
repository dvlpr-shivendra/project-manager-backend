<?php

namespace App\Ai\Tools;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\Types\Type;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ResourceSearch implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search for tasks or projects by their title or name to find their ID.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = $request['query'];
        $type = $request['type'] ?? 'all';

        $results = [];

        if ($type === 'all' || $type === 'project') {
            $projects = Project::where('name', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get(['id', 'name']);
            
            foreach ($projects as $project) {
                $results[] = "Project ID: {$project->id}, Name: \"{$project->name}\"";
            }
        }

        if ($type === 'all' || $type === 'task') {
            $tasks = Task::where('title', 'LIKE', "%{$query}%")
                ->limit(5)
                ->get(['id', 'title']);
            
            foreach ($tasks as $task) {
                $results[] = "Task ID: {$task->id}, Title: \"{$task->title}\"";
            }
        }

        if (empty($results)) {
            return "No resources found matching \"{$query}\".";
        }

        return "Search results for \"{$query}\":\n" . implode("\n", $results);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('The search query (task title or project name)'),
            'type' => $schema->string()->enum(['all', 'task', 'project'])->description('The type of resource to search for')->default('all'),
        ];
    }
}

<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Illuminate\Contracts\JsonSchema\JsonSchema;

class ProjectAssistant implements Agent, Conversational, HasStructuredOutput
{
    use Promptable, RemembersConversations;

    protected string $instructions;

    public function __construct(string $instructions)
    {
        $this->instructions = $instructions;
    }

    public function instructions(): string
    {
        return $this->instructions;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'action' => $schema->string()->enum(['create', 'update', 'delete', 'list', 'clarify'])->description('The action being performed'),
            'resource_type' => $schema->string()->enum(['task', 'project'])->description('The type of resource'),
            'task_id' => $schema->integer()->nullable()->description('Existing task ID (required for update/delete)'),
            'project_id' => $schema->integer()->nullable()->description('Existing project ID (required for update/delete/list)'),
            'data' => $schema->object([
                'title' => $schema->string()->nullable(),
                'name' => $schema->string()->nullable(),
                'description' => $schema->string()->nullable(),
                'assignee_id' => $schema->integer()->nullable(),
                'project_id' => $schema->integer()->nullable(),
                'deadline' => $schema->string()->nullable(),
                'time_estimate' => $schema->integer()->nullable(),
                'is_complete' => $schema->boolean()->nullable(),
                'tags' => $schema->array($schema->string())->nullable()->description('List of tag NAMES (e.g. ["urgent"]), never IDs!'),
                'add_tags' => $schema->array($schema->string())->nullable()->description('List of tag NAMES to add'),
                'remove_tags' => $schema->array($schema->string())->nullable()->description('List of tag NAMES to remove'),
            ])->nullable()->description('Data for specific resource actions'),
            'filters' => $schema->object([
                'assignee_id' => $schema->integer()->nullable(),
                'project_id' => $schema->integer()->nullable(),
                'is_complete' => $schema->boolean()->nullable(),
                'deadline_before' => $schema->string()->nullable(),
                'search' => $schema->string()->nullable(),
                'tag' => $schema->string()->nullable(),
            ])->nullable(),
            'confirmation_message' => $schema->string(),
            'question' => $schema->string()->nullable(),
        ];
    }
}

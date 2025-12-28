<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Project;
use Maatwebsite\Excel\Facades\Excel;

// To run this seeder: php artisan db:seed --class="Database\Seeders\GenerateTestTasksSeeder"
class GenerateTestTasksSeeder extends Seeder
{
    public function run()
    {
        $rows = 1000;

        $creator = User::first();
        $assignee = User::skip(1)->first() ?? $creator;
        $project = Project::first();

        $tasks = collect();

        for ($i = 1; $i <= $rows; $i++) {
            $tasks->push([
                'title' => "Task $i",
                'description' => "Description for task $i",
                'creator_email' => $creator->email,
                'assignee_email' => $assignee->email,
                'deadline' => now()->addDays(rand(0, 30)),
                'time_estimate' => rand(30, 240),
                'is_complete' => rand(0, 1),
                'project_id' => $project->id,
            ]);
        }

        Excel::store(new class($tasks) implements 
            \Maatwebsite\Excel\Concerns\FromCollection, 
            \Maatwebsite\Excel\Concerns\WithHeadings {
                private $tasks;
                public function __construct($tasks) { $this->tasks = $tasks; }
                public function collection() { return $this->tasks; }
                public function headings(): array { 
                    return ['title','description','creator_email','assignee_email','deadline','time_estimate','is_complete']; 
                }
            }, 'public/large_tasks.xlsx');

        echo "Generated large_tasks.xlsx with $rows rows.\n";
    }
}

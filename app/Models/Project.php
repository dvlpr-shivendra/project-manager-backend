<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    
    public function incompleteTasks()
    {
        return $this->hasMany(Task::class)->where('is_complete', false);
    }
    
    public function completedTasks()
    {
        return $this->hasMany(Task::class)->where('is_complete', true);
    }
}

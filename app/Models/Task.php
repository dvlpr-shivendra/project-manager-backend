<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'creator_id',
        'assignee_id',
        'project_id',
        'deadline',
        'is_complete',
    ];

    protected $with = [
        'assignee:id,name,email',
        'followers:id,name,email',
        'tags:id,name,color,background_color',
        'attachments',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
    
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function screenshots()
    {
        return $this->hasMany(Screenshot::class);
    }
    
    public function progresses()
    {
        return $this->hasMany(Progress::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
    
    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'task_followers');
    }
}

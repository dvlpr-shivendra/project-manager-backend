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
        'tags:id,name,color,background_color',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
    ];

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

    public function increaseTimeSpent($timeInSeconds)
    {
        $this->time_spent += $timeInSeconds;
        $this->save();
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}

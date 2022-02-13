<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = ['user', 'tags'];

    protected $hidden = ['user_id'];

    protected $casts = [
        'is_complete' => 'boolean',
    ];

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}

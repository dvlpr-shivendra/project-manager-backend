<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskComment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = [
        'user:id,name,email',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

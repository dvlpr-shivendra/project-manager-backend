<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'path',
        'size',
        'task_id',
    ];

    public function delete()
    {
        Storage::delete($this->path);
        parent::delete();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function increaseDuration($seconds)
    {
        $this->duration += $seconds;
        $this->save();
    }

    public function screenshots()
    {
        return $this->hasMany(Screenshot::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Enums\ObjectiveStatus;

class Objective extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => ObjectiveStatus::class,
    ];

    public function tasks(){
        return $this->hasMany(Task::class);
    }

    public function project(){
        return $this->belongsTo(Project::class);
    }
}

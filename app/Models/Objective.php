<?php

namespace App\Models;

use App\Observers\ObjectiveObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Enums\ObjectiveStatus;

#[ObservedBy([ObjectiveObserver::class])]
class Objective extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => ObjectiveStatus::class,
    ];

    protected $fillable = [
        'status'
    ];

    public function tasks(){
        return $this->hasMany(Task::class);
    }

    public function project(){
        return $this->belongsTo(Project::class);
    }
}

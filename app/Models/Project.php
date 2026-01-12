<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    public function users(){
        return $this->morphToMany(User::class, 'model', 'memberships')
            ->withPivot('role_id');
    }

    public function creator(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function team(){
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function tasks(){
        return $this->hasMany(Task::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ProjectStatus;
use App\Enums\DisputeStatus;


class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $casts = [
        'status' => ProjectStatus::class,
    ];

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

    public function objectives(){
        return $this->hasMany(Objective::class);
    }

    public function disputes(){
        return $this->hasMany(ProjectDispute::class);
    }

    public function hasOpenDispute(){
        return $this->disputes()->where('status', DisputeStatus::Open)->exists();
    }

    //Scopes
    public function scopeCompleted($query){
        return $query->where('status', ProjectStatus::Completed);
    }


    // public function scopeActiveDisputes($query){
    //     return $query->where('status', ProjectStatus::CancelInProgress)
    //                  ->whereHas('disputes', function($q){
    //                      $q->where('status', DisputeStatus::Open);
    //                  });
    // }
}

<?php

namespace App\Models;

use App\Observers\ProjectObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ProjectStatus;
use App\Enums\DisputeStatus;


#[ObservedBy([ProjectObserver::class])]
class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    protected $casts = [
        'status' => ProjectStatus::class,
    ];

    protected $fillable = [
        'name',
        'description',
        'team_id',
        'user_id',
        'status'
    ];

    public function users()
    {
        return $this->morphToMany(User::class, 'model', 'memberships')
            ->using(Membership::class)
            ->withPivot('role_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function objectives()
    {
        return $this->hasMany(Objective::class);
    }

    public function disputes()
    {
        return $this->hasMany(ProjectDispute::class);
    }

    public function hasOpenDispute()
    {
        return $this->disputes()->where('status', DisputeStatus::Open)->exists();
    }

    public function members()
    {
        return $this->morphToMany(User::class, 'model', 'memberships')
            ->using(Membership::class)
            ->withPivot('role_id');
    }

    //Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', ProjectStatus::Completed);
    }

}

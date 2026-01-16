<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function teams(){
        return $this->morphedByMany(Team::class, 'model', 'memberships')
            ->withPivot('role_id');
    }

    public function projects(){
        return $this->morphedByMany(Project::class, 'model', 'memberships')
            ->withPivot('role_id');
    }

    public function createdProjects(){
        return $this->hasMany(Project::class, 'user_id');
    }


    // public function roles(){
    //     return $this->belongsToMany(Role::class, 'memberships', 'user_id', 'role_id')
    //         ->withPivot('model_id', 'model_type');
    // }

    // public function users(){
    //     return $this->
    // }
            
}

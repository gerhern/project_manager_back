<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasApiTokens;

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
            ->using(Membership::class)
            ->withPivot('role_id');
    }

    public function projects(){
        return $this->morphedByMany(Project::class, 'model', 'memberships')
            ->using(Membership::class)
            ->withPivot('role_id');
    }

    public function createdProjects(){
        return $this->hasMany(Project::class, 'user_id');
    }

    public function hasProjectRole(Project $project, $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];
        return \DB::table('memberships')
            ->where('user_id', $this->id)
            ->where('model_id', $project->id)
            ->where('model_type', Project::class)
            ->whereExists(function ($query) use ($roles) {
                $query->select(\DB::raw(1))
                    ->from('roles')
                    ->whereColumn('roles.id', 'memberships.role_id')
                    ->whereIn('roles.name', $roles);
            })
            ->exists();
    }

    public function hasTeamRole(Team $team, $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return \DB::table('memberships')
            ->where('user_id', $this->id)
            ->where('model_id', $team->id)
            ->where('model_type', Team::class)
            ->whereExists(function ($query) use ($roles) {
                $query->select(\DB::raw(1))
                    ->from('roles')
                    ->whereColumn('roles.id', 'memberships.role_id')
                    ->whereIn('roles.name', $roles);
            })
            ->exists();
    }
            
}

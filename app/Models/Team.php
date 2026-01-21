<?php

namespace App\Models;

use App\Enums\TeamStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status'
    ];

    protected $casts = [
        'status' => TeamStatus::class,
    ];

    public function members()
    {
        return $this->morphToMany(User::class, 'model', 'memberships')
            ->using(Membership::class)
            ->withPivot('role_id')
            ->withTimestamps();
    }

//     public function roles(){
//         return $this->morphedByMany()
//     }
}

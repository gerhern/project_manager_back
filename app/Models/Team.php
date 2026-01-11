<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    /** @use HasFactory<\Database\Factories\TeamFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
    ];

    public function members(){
        return $this->belongsToMany(User::class, 'memberships', 'model_id', 'user_id')
            ->where('model_type', Team::class)
            ->withPivot('role_id');
    }
}

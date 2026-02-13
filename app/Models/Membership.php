<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Spatie\Permission\Models\Role;

class Membership extends MorphPivot
{
    protected $table = 'memberships';
    
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}

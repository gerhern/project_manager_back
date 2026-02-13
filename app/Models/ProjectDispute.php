<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\DisputeStatus;

class ProjectDispute extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectDisputeFactory> */
    use HasFactory;

    

    protected $casts = [
        'status' => DisputeStatus::class
    ];

    protected $fillable = [
        'project_id',
        'user_id',
        'expired_at',
        'status'
    ];

    public function project(){
        return $this->belongsTo(Project::class);
    }

    //Scopes
    public function scopeActiveDisputes($query){
        return $query->where('status', DisputeStatus::Open);
    }
}

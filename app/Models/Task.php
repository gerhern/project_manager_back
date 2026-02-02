<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Enums\TaskStatus;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;

    protected $casts = [
        'status' => TaskStatus::class,
    ];

    protected $fillable = [
        'title',
        'description',
        'due_date',
        'objective_id'
    ];

    public function objective(){
        return $this->belongsTo(Objective::class);
    }
}

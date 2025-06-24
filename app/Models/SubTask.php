<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SubTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'title',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
    ];

    /**
     * Get the task that owns the sub-task.
     */
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Complete the sub-task.
     */
    public function complete(): void
    {
        $this->is_completed = true;
        $this->completed_at = now();
        $this->save();
    }
}

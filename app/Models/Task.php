<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'difficulty',
        'deadline',
        'is_completed',
        'penalty_applied',
        'completed_at',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
        'penalty_applied' => 'boolean',
    ];

    /**
     * Get the user that owns the task.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sub-tasks for the task.
     */
    public function subTasks()
    {
        return $this->hasMany(SubTask::class);
    }

    /**
     * Get XP reward based on difficulty.
     */
    public function getXpReward(): int
    {
        return match ($this->difficulty) {
            'EASY' => 10,
            'MEDIUM' => 25,
            'HARD' => 50,
            'VERY_HARD' => 100,
            default => 25,
        };
    }

    /**
     * Get HP penalty based on difficulty.
     */
    public function getHpPenalty(): int
    {
        return match ($this->difficulty) {
            'EASY' => 5,
            'MEDIUM' => 10,
            'HARD' => 20,
            'VERY_HARD' => 35,
            default => 10,
        };
    }

    /**
     * Complete the task and award XP.
     */
    public function complete(): void
    {
        $this->is_completed = true;
        $this->completed_at = now();
        $this->save();

        // Award XP to user
        $this->user->addXp($this->getXpReward());
    }

    /**
     * Apply penalty for missed deadline.
     */
    public function applyPenalty(): void
    {
        if (!$this->penalty_applied && $this->deadline && now()->greaterThan($this->deadline) && !$this->is_completed) {
            $this->penalty_applied = true;
            $this->save();

            // Reduce user HP
            $this->user->reduceHp($this->getHpPenalty());
        }
    }
}

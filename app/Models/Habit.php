<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Habit extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'difficulty',
        'current_streak',
        'last_completed_date',
        'schedule_type',
        'is_on_monday',
        'is_on_tuesday',
        'is_on_wednesday',
        'is_on_thursday',
        'is_on_friday',
        'is_on_saturday',
        'is_on_sunday',
        'is_active',
    ];

    protected $casts = [
        'last_completed_date' => 'datetime',
        'is_on_monday' => 'boolean',
        'is_on_tuesday' => 'boolean',
        'is_on_wednesday' => 'boolean',
        'is_on_thursday' => 'boolean',
        'is_on_friday' => 'boolean',
        'is_on_saturday' => 'boolean',
        'is_on_sunday' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the habit.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the habit completions.
     */
    public function completions()
    {
        return $this->hasMany(HabitCompletion::class);
    }

    /**
     * Get XP reward based on difficulty.
     */
    public function getXpReward(): int
    {
        return match ($this->difficulty) {
            'EASY' => 5,
            'MEDIUM' => 15,
            'HARD' => 30,
            'VERY_HARD' => 60,
            default => 15,
        };
    }

    /**
     * Check if habit is scheduled for today.
     */
    public function isScheduledForToday(): bool
    {
        $today = Carbon::today();
        $dayOfWeek = strtolower($today->format('l'));

        return match ($this->schedule_type) {
            'DAILY' => true,
            'WEEKLY' => true, // Will need additional logic for weekly scheduling
            'SPECIFIC_DAYS' => $this->{"is_on_" . $dayOfWeek},
            default => false,
        };
    }

    /**
     * Check if habit is completed today.
     */
    public function isCompletedToday(): bool
    {
        return $this->completions()
            ->where('completed_date', Carbon::today()->toDateString())
            ->exists();
    }

    /**
     * Complete the habit for today.
     */
    public function completeToday(): HabitCompletion
    {
        $today = Carbon::today()->toDateString();

        // Check if already completed today
        if ($this->isCompletedToday()) {
            return $this->completions()->where('completed_date', $today)->first();
        }

        // Update streak
        $this->updateStreak();

        // Create completion record
        $completion = $this->completions()->create([
            'user_id' => $this->user_id,
            'completed_date' => $today,
            'xp_earned' => $this->getXpReward(),
        ]);

        // Award XP and restore HP
        $this->user->addXp($this->getXpReward());
        $this->user->restoreHp(5); // Restore 5 HP for habit completion

        $this->last_completed_date = Carbon::today();
        $this->save();

        return $completion;
    }

    /**
     * Update the habit streak.
     */
    protected function updateStreak(): void
    {
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $today = Carbon::today()->format('Y-m-d');
        $lastCompleted = $this->last_completed_date?->format('Y-m-d');

        if ($lastCompleted === $yesterday) {
            // Continue streak
            $this->current_streak++;
        } elseif ($lastCompleted === $today) {
            // Already completed today, don't change streak
        } else {
            // Restart streak
            $this->current_streak = 1;
        }
    }

    /**
     * Get habits due for today for a user.
     */
    public static function getDueForToday(int $userId)
    {
        return static::where('user_id', $userId)
            ->where('is_active', true)
            ->get()
            ->filter(function ($habit) {
                return $habit->isScheduledForToday() && !$habit->isCompletedToday();
            });
    }
}

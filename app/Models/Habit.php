<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
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
     * Get XP reward based on difficulty with level bonus.
     */
    public function getXpReward(): int
    {
        $baseXp = match ($this->difficulty) {
            'EASY' => 5,
            'MEDIUM' => 15,
            'HARD' => 30,
            'VERY_HARD' => 60,
            default => 15,
        };

        // Apply level bonus
        return $this->user->calculateXpWithBonus($baseXp);
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
        return DB::transaction(function () {
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
        });
    }

    /**
     * Update the habit streak.
     */
    protected function updateStreak(): void
    {
        $today = Carbon::today();
        $lastCompleted = $this->last_completed_date;

        if (!$lastCompleted) {
            // First time completing
            $this->current_streak = 1;
            return;
        }

        if ($this->schedule_type === 'SPECIFIC_DAYS') {
            // For specific days, check if we're continuing from the last valid day
            $this->updateStreakForSpecificDays($today, $lastCompleted);
        } else {
            // For DAILY and WEEKLY, check consecutive days
            $yesterday = Carbon::yesterday();

            if ($lastCompleted->format('Y-m-d') === $yesterday->format('Y-m-d')) {
                // Continue streak
                $this->current_streak++;
            } elseif ($lastCompleted->format('Y-m-d') === $today->format('Y-m-d')) {
                // Already completed today, don't change streak
            } else {
                // Restart streak
                $this->current_streak = 1;
            }
        }
    }

    /**
     * Update streak for habits with SPECIFIC_DAYS schedule.
     */
    protected function updateStreakForSpecificDays(Carbon $today, Carbon $lastCompleted): void
    {
        // Get the last valid day before today
        $lastValidDay = $this->getLastValidDayBefore($today);

        if ($lastValidDay && $lastCompleted->format('Y-m-d') === $lastValidDay->format('Y-m-d')) {
            // Continue streak - completed on the last valid day
            $this->current_streak++;
        } elseif ($lastCompleted->format('Y-m-d') === $today->format('Y-m-d')) {
            // Already completed today, don't change streak
        } else {
            // Check if we missed any valid days since last completion
            $startCheck = $lastCompleted->copy()->addDay();
            $endCheck = $today->copy()->subDay();
            $missedValidDays = $this->countValidDaysBetween($startCheck, $endCheck);

            if ($missedValidDays > 0) {
                // Restart streak - missed some valid days
                $this->current_streak = 1;
            } else {
                // Continue streak - no valid days were missed
                $this->current_streak++;
            }
        }
    }

    /**
     * Get the last valid day before the given date.
     */
    protected function getLastValidDayBefore(Carbon $date): ?Carbon
    {
        $checkDate = $date->copy()->subDay();

        // Look back up to 7 days to find the last valid day
        for ($i = 0; $i < 7; $i++) {
            $dayOfWeek = strtolower($checkDate->format('l'));

            if ($this->{"is_on_" . $dayOfWeek}) {
                return $checkDate;
            }

            $checkDate->subDay();
        }

        return null;
    }

    /**
     * Count valid days between two dates (exclusive).
     */
    protected function countValidDaysBetween(Carbon $start, Carbon $end): int
    {
        if ($start->gte($end)) {
            return 0;
        }

        $count = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            $dayOfWeek = strtolower($current->format('l'));

            if ($this->{"is_on_" . $dayOfWeek}) {
                $count++;
            }

            $current->addDay();
        }

        return $count;
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

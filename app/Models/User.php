<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'level',
        'xp',
        'xp_to_next_level',
        'hp',
        'title',
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

    /**
     * Get the tasks for the user.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the habits for the user.
     */
    public function habits()
    {
        return $this->hasMany(Habit::class);
    }

    /**
     * Get the habit completions for the user.
     */
    public function habitCompletions()
    {
        return $this->hasMany(HabitCompletion::class);
    }

    /**
     * Add experience points and handle level up.
     */
    public function addXp(int $xp): void
    {
        DB::transaction(function () use ($xp) {
            $this->xp += $xp;

            while ($this->xp >= $this->xp_to_next_level) {
                $this->levelUp();
            }

            $this->save();
        });
    }

    /**
     * Level up the user.
     */
    protected function levelUp(): void
    {
        $this->xp -= $this->xp_to_next_level;
        $this->level++;
        $this->xp_to_next_level = $this->calculateXpToNextLevel();
        $this->title = $this->getLevelTitle();
    }

    /**
     * Calculate XP needed for next level.
     */
    protected function calculateXpToNextLevel(): int
    {
        // Reduced progression: Base 100 + 25 per level (instead of 50)
        return 100 + ($this->level * 25);
    }

    /**
     * Get XP bonus multiplier based on user level.
     */
    public function getXpBonusMultiplier(): float
    {
        return match (true) {
            $this->level >= 20 => 2.0,  // 100% bonus at level 20+
            $this->level >= 15 => 1.75, // 75% bonus at level 15-19
            $this->level >= 10 => 1.5,  // 50% bonus at level 10-14
            $this->level >= 5 => 1.25,  // 25% bonus at level 5-9
            default => 1.0,             // No bonus at level 1-4
        };
    }

    /**
     * Calculate final XP with level bonus.
     */
    public function calculateXpWithBonus(int $baseXp): int
    {
        return (int) ($baseXp * $this->getXpBonusMultiplier());
    }

    /**
     * Get title based on level.
     */
    protected function getLevelTitle(): string
    {
        return match (true) {
            $this->level >= 20 => 'Master Produktivitas',
            $this->level >= 15 => 'Ahli Kebiasaan',
            $this->level >= 10 => 'Pahlawan Produktif',
            $this->level >= 5 => 'Pembangun Kebiasaan',
            default => 'Pemula Produktif',
        };
    }

    /**
     * Reduce HP for penalties.
     */
    public function reduceHp(int $amount): void
    {
        $this->hp = max(0, $this->hp - $amount);
        $this->save();
    }

    /**
     * Restore HP for habit completion.
     */
    public function restoreHp(int $amount): void
    {
        $this->hp = min(100, $this->hp + $amount);
        $this->save();
    }

    /**
     * Get user gamification data for API response.
     */
    public function getGamificationData(): array
    {
        return [
            'level' => $this->level,
            'xp' => $this->xp,
            'xp_to_next_level' => $this->xp_to_next_level,
            'hp' => $this->hp,
            'title' => $this->title,
            'xp_bonus_multiplier' => $this->getXpBonusMultiplier(),
            'next_level_xp_requirement' => $this->calculateXpToNextLevel(),
        ];
    }
}

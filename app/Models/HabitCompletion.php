<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HabitCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'habit_id',
        'user_id',
        'completed_date',
        'xp_earned',
    ];

    protected $casts = [
        'completed_date' => 'date',
    ];

    /**
     * Get the habit that owns the completion.
     */
    public function habit()
    {
        return $this->belongsTo(Habit::class);
    }

    /**
     * Get the user that owns the completion.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

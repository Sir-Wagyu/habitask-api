<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Habit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard data.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $today = Carbon::today();

        // Get today's tasks
        $todayTasks = Task::where('user_id', $user->id)
            ->where('is_completed', false)
            ->whereDate('deadline', '<=', $today)
            ->with('subTasks')
            ->get();

        // Get habits due today
        $habitsToday = Habit::getDueForToday($user->id);

        // Get completed tasks today
        $completedToday = Task::where('user_id', $user->id)
            ->where('is_completed', true)
            ->whereDate('completed_at', $today)
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user,
                'today_tasks' => $todayTasks,
                'habits_today' => $habitsToday,
                'completed_today' => $completedToday,
                'date' => $today->format('Y-m-d')
            ]
        ]);
    }

    /**
     * Get dashboard summary.
     */
    public function summary(Request $request)
    {
        $user = $request->user();

        $summary = [
            'user_stats' => [
                'level' => $user->level,
                'xp' => $user->xp,
                'xp_to_next_level' => $user->xp_to_next_level,
                'hp' => $user->hp,
                'title' => $user->title,
            ],
            'tasks_stats' => [
                'total' => Task::where('user_id', $user->id)->count(),
                'completed' => Task::where('user_id', $user->id)->where('is_completed', true)->count(),
                'pending' => Task::where('user_id', $user->id)->where('is_completed', false)->count(),
            ],
            'habits_stats' => [
                'active' => Habit::where('user_id', $user->id)->where('is_active', true)->count(),
                'completed_today' => Habit::where('user_id', $user->id)
                    ->whereHas('completions', function ($query) {
                        $query->whereDate('completed_date', Carbon::today());
                    })->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary
            ]
        ]);
    }
}

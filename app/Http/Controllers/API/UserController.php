<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Get user profile.
     */
    public function profile(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user
            ]
        ]);
    }

    /**
     * Update user profile.
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $request->user()->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $request->user();
        $user->update($request->only(['name', 'email']));

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'user' => $user
            ]
        ]);
    }

    /**
     * Get user gamification stats.
     */
    public function stats(Request $request)
    {
        $user = $request->user();

        // Load relationships
        $user->load(['tasks', 'habits', 'habitCompletions']);

        $stats = [
            'level' => $user->level,
            'xp' => $user->xp,
            'xp_to_next_level' => $user->xp_to_next_level,
            'hp' => $user->hp,
            'title' => $user->title,
            'tasks_completed' => $user->tasks()->where('is_completed', true)->count(),
            'tasks_pending' => $user->tasks()->where('is_completed', false)->count(),
            'habits_count' => $user->habits()->where('is_active', true)->count(),
            'total_habit_completions' => $user->habitCompletions()->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => $stats
            ]
        ]);
    }
}

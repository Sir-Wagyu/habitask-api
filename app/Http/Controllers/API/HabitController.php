<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class HabitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $habits = Habit::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'habits' => $habits
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:EASY,MEDIUM,HARD,VERY_HARD',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $habit = Habit::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'difficulty' => $request->difficulty,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Habit created successfully',
            'data' => [
                'habit' => $habit
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Habit $habit)
    {
        if ($habit->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'habit' => $habit
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Habit $habit)
    {
        if ($habit->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'sometimes|required|in:EASY,MEDIUM,HARD,VERY_HARD',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $habit->update($request->only(['title', 'description', 'difficulty']));

        return response()->json([
            'success' => true,
            'message' => 'Habit updated successfully',
            'data' => [
                'habit' => $habit
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Habit $habit)
    {
        if ($habit->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $habit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Habit deleted successfully'
        ]);
    }

    /**
     * Complete the habit for today.
     */
    public function complete(Request $request, Habit $habit)
    {
        if ($habit->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($habit->isCompletedToday()) {
            return response()->json([
                'success' => false,
                'message' => 'Habit already completed today'
            ], Response::HTTP_BAD_REQUEST);
        }

        $completion = $habit->completeToday();

        return response()->json([
            'success' => true,
            'message' => 'Habit completed successfully',
            'data' => [
                'habit' => $habit->fresh(),
                'completion' => $completion,
                'xp_earned' => $completion->xp_earned
            ]
        ]);
    }

    /**
     * Get habit completions history.
     */
    public function completions(Request $request, Habit $habit)
    {
        if ($habit->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $completions = $habit->completions()
            ->orderBy('completed_date', 'desc')
            ->paginate(30);

        return response()->json([
            'success' => true,
            'data' => [
                'completions' => $completions
            ]
        ]);
    }

    /**
     * Get habits due today.
     */
    public function dueToday(Request $request)
    {
        $habits = \App\Models\Habit::getDueForToday($request->user()->id);

        return response()->json([
            'success' => true,
            'data' => [
                'habits' => $habits
            ]
        ]);
    }
}

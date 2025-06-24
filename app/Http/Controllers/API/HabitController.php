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
        // Implementation
        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Habit $habit)
    {
        // Implementation
        return response()->json(['success' => true]);
    }
}

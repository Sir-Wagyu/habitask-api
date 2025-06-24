<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\SubTask;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tasks = Task::where('user_id', $request->user()->id)
            ->with('subTasks')
            ->when($request->has('completed'), function ($query) use ($request) {
                return $query->where('is_completed', $request->boolean('completed'));
            })
            ->when($request->has('difficulty'), function ($query) use ($request) {
                return $query->where('difficulty', $request->difficulty);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'tasks' => $tasks
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
            'deadline' => 'nullable|date',
            'sub_tasks' => 'nullable|array',
            'sub_tasks.*.title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $task = Task::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'description' => $request->description,
            'difficulty' => $request->difficulty,
            'deadline' => $request->deadline,
        ]);

        // Create sub-tasks if provided
        if ($request->has('sub_tasks')) {
            foreach ($request->sub_tasks as $subTaskData) {
                $task->subTasks()->create([
                    'title' => $subTaskData['title'],
                ]);
            }
        }

        $task->load('subTasks');

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => [
                'task' => $task
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $task->load('subTasks');

        return response()->json([
            'success' => true,
            'data' => [
                'task' => $task
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'sometimes|required|in:EASY,MEDIUM,HARD,VERY_HARD',
            'deadline' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $task->update($request->only(['title', 'description', 'difficulty', 'deadline']));

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => [
                'task' => $task
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    }

    /**
     * Complete the task.
     */
    public function complete(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        if ($task->is_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Task is already completed'
            ], Response::HTTP_BAD_REQUEST);
        }

        $task->complete();
        $task->load('subTasks');

        return response()->json([
            'success' => true,
            'message' => 'Task completed successfully',
            'data' => [
                'task' => $task,
                'xp_earned' => $task->getXpReward()
            ]
        ]);
    }

    /**
     * Mark task as incomplete.
     */
    public function incomplete(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $task->update([
            'is_completed' => false,
            'completed_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task marked as incomplete',
            'data' => [
                'task' => $task
            ]
        ]);
    }

    /**
     * Store a new sub-task.
     */
    public function storeSubTask(Request $request, Task $task)
    {
        if ($task->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $subTask = $task->subTasks()->create([
            'title' => $request->title,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sub-task created successfully',
            'data' => [
                'sub_task' => $subTask
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * Update a sub-task.
     */
    public function updateSubTask(Request $request, Task $task, SubTask $subTask)
    {
        if ($task->user_id !== $request->user()->id || $subTask->task_id !== $task->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $subTask->update(['title' => $request->title]);

        return response()->json([
            'success' => true,
            'message' => 'Sub-task updated successfully',
            'data' => [
                'sub_task' => $subTask
            ]
        ]);
    }

    /**
     * Delete a sub-task.
     */
    public function destroySubTask(Request $request, Task $task, SubTask $subTask)
    {
        if ($task->user_id !== $request->user()->id || $subTask->task_id !== $task->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $subTask->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sub-task deleted successfully'
        ]);
    }

    /**
     * Complete a sub-task.
     */
    public function completeSubTask(Request $request, Task $task, SubTask $subTask)
    {
        if ($task->user_id !== $request->user()->id || $subTask->task_id !== $task->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $subTask->complete();

        return response()->json([
            'success' => true,
            'message' => 'Sub-task completed successfully',
            'data' => [
                'sub_task' => $subTask
            ]
        ]);
    }

    /**
     * Mark sub-task as incomplete.
     */
    public function incompleteSubTask(Request $request, Task $task, SubTask $subTask)
    {
        if ($task->user_id !== $request->user()->id || $subTask->task_id !== $task->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], Response::HTTP_FORBIDDEN);
        }

        $subTask->update([
            'is_completed' => false,
            'completed_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sub-task marked as incomplete',
            'data' => [
                'sub_task' => $subTask
            ]
        ]);
    }
}

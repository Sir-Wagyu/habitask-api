<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

// Create a test user
$user = \App\Models\User::create([
      'name' => 'Test User API',
      'email' => 'testapi@example.com',
      'password' => \Illuminate\Support\Facades\Hash::make('password123'),
]);

echo "User created: " . $user->email . "\n";
echo "User ID: " . $user->id . "\n";
echo "User Level: " . $user->level . "\n";
echo "User XP: " . $user->xp . "\n";
echo "User HP: " . $user->hp . "\n";

// Create a token
$token = $user->createToken('TestAPI')->plainTextToken;
echo "Token created: " . substr($token, 0, 20) . "...\n";

// Test task creation
$task = \App\Models\Task::create([
      'user_id' => $user->id,
      'title' => 'Test Task from Script',
      'description' => 'This is a test task',
      'difficulty' => 'MEDIUM',
      'deadline' => now()->addDays(7),
]);

echo "Task created: " . $task->title . "\n";
echo "Task ID: " . $task->id . "\n";
echo "Task Difficulty: " . $task->difficulty . "\n";
echo "XP Reward: " . $task->getXpReward() . "\n";

echo "\n=== TEST COMPLETED SUCCESSFULLY ===\n";
echo "You can now use this token in Postman:\n";
echo $token . "\n";

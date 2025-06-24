<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== HABITASK API DATABASE & MODEL TEST ===\n\n";

try {
      // Test database connection
      echo "1. Testing database connection...\n";
      $connection = \Illuminate\Support\Facades\DB::connection();
      $connection->getPdo();
      echo "✅ Database connected successfully!\n\n";

      // Test user creation and token generation
      echo "2. Testing User model and token creation...\n";

      $user = new \App\Models\User([
            'name' => 'Test User',
            'email' => 'test_' . time() . '@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123')
      ]);

      echo "✅ User model created\n";

      // Test createToken method
      if (method_exists($user, 'createToken')) {
            echo "✅ createToken method available\n";
      } else {
            echo "❌ createToken method not available\n";
            exit(1);
      }

      // Save user to database
      $user->save();
      echo "✅ User saved to database (ID: {$user->id})\n";

      // Test token creation
      $tokenResult = $user->createToken('TestApp');
      $token = $tokenResult->plainTextToken;
      echo "✅ Token created: " . substr($token, 0, 20) . "...\n";

      // Test gamification fields
      echo "\n3. Testing gamification system...\n";
      echo "Level: {$user->level}\n";
      echo "XP: {$user->xp}\n";
      echo "HP: {$user->hp}\n";
      echo "Title: {$user->title}\n";

      // Test XP addition
      $user->addXp(50);
      echo "✅ Added 50 XP. New XP: {$user->xp}\n";

      // Test models relationships
      echo "\n4. Testing model relationships...\n";

      // Create a task
      $task = $user->tasks()->create([
            'title' => 'Test Task',
            'description' => 'This is a test task',
            'difficulty' => 'MEDIUM',
            'deadline' => now()->addDays(1)
      ]);
      echo "✅ Task created (ID: {$task->id})\n";

      // Create sub-task
      $subTask = $task->subTasks()->create([
            'title' => 'Test Sub-task'
      ]);
      echo "✅ Sub-task created (ID: {$subTask->id})\n";

      // Create a habit
      $habit = $user->habits()->create([
            'title' => 'Test Habit',
            'description' => 'This is a test habit',
            'difficulty' => 'EASY',
            'schedule_type' => 'DAILY'
      ]);
      echo "✅ Habit created (ID: {$habit->id})\n";

      // Test habit completion
      if ($habit->isScheduledForToday()) {
            echo "✅ Habit is scheduled for today\n";
      }

      if (!$habit->isCompletedToday()) {
            echo "✅ Habit not completed today (as expected)\n";
      }

      echo "\n=== ALL TESTS PASSED! ===\n";
      echo "Your Habitask API is ready to use! 🎉\n\n";

      echo "Next steps:\n";
      echo "1. Start the server: php artisan serve\n";
      echo "2. Test endpoints with Postman or curl\n";
      echo "3. Use the token for authenticated requests\n\n";

      echo "Sample API calls:\n";
      echo "POST http://localhost:8000/api/auth/register\n";
      echo "POST http://localhost:8000/api/auth/login\n";
      echo "GET http://localhost:8000/api/tasks (with Bearer token)\n";
      echo "POST http://localhost:8000/api/tasks (with Bearer token)\n";
} catch (Exception $e) {
      echo "❌ Error: " . $e->getMessage() . "\n";
      echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

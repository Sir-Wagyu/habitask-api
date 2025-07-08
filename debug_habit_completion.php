<?php

// Test script untuk debug habit completion
echo "=== Debug Habit Completion ===\n\n";

// Test data untuk simulasi
$testUser = [
      'id' => 1,
      'level' => 5,
      'xp' => 50,
      'xp_to_next_level' => 225,
      'hp' => 100,
      'title' => 'Pembangun Kebiasaan',
];

$testHabit = [
      'id' => 1,
      'title' => 'Morning Exercise',
      'difficulty' => 'MEDIUM',
      'schedule_type' => 'DAILY',
      'current_streak' => 3,
];

echo "Test User Level: {$testUser['level']}\n";

// Simulate bonus calculation
$baseLevelBonus = match (true) {
      $testUser['level'] >= 20 => 2.0,
      $testUser['level'] >= 15 => 1.75,
      $testUser['level'] >= 10 => 1.5,
      $testUser['level'] >= 5 => 1.25,
      default => 1.0,
};

echo "XP Bonus Multiplier: {$baseLevelBonus}x\n\n";

// Simulate XP calculation
$baseXp = match ($testHabit['difficulty']) {
      'EASY' => 5,
      'MEDIUM' => 15,
      'HARD' => 30,
      'VERY_HARD' => 60,
      default => 15,
};

$finalXp = (int)($baseXp * $baseLevelBonus);

echo "Test Habit: {$testHabit['title']}\n";
echo "Difficulty: {$testHabit['difficulty']}\n";
echo "Base XP: {$baseXp}\n";
echo "Final XP with Bonus: {$finalXp}\n";
echo "Current Streak: {$testHabit['current_streak']}\n\n";

echo "=== Potential Issues to Check ===\n";
echo "1. Make sure habit has proper schedule_type set\n";
echo "2. Make sure user relationship is loaded before calculating XP\n";
echo "3. Check if habit_completions table exists and is accessible\n";
echo "4. Verify Carbon date formatting\n";
echo "5. Check database transaction support\n\n";

echo "=== Test with Postman ===\n";
echo "Method: PATCH (not POST)\n";
echo "URL: http://localhost:8000/api/habits/{id}/complete\n";
echo "Headers: Authorization: Bearer {your_token}\n";
echo "Expected Response: 200 with completion data\n\n";

echo "Debug script completed. Check actual API with corrected method.\n";

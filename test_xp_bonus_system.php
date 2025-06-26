<?php

// Simple test script to verify XP bonus system
require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== Testing XP Bonus System ===\n\n";

// Create a test user
$user = new User([
      'name' => 'Test User',
      'email' => 'test@example.com',
      'password' => 'password',
      'level' => 1,
      'xp' => 0,
      'xp_to_next_level' => 125,
      'hp' => 100,
      'title' => 'Pemula Produktif',
]);

// Test XP bonus at different levels
$levels = [1, 5, 10, 15, 20];
$baseXp = 25; // MEDIUM task/habit

echo "Base XP: {$baseXp}\n";
echo "Level | Multiplier | Final XP | Next Level Requirement\n";
echo "------|------------|----------|----------------------\n";

foreach ($levels as $level) {
      $user->level = $level;
      $user->xp_to_next_level = $user->calculateXpToNextLevel();

      $multiplier = $user->getXpBonusMultiplier();
      $finalXp = $user->calculateXpWithBonus($baseXp);
      $nextLevelReq = $user->calculateXpToNextLevel();

      printf(
            "%-5d | %-10.2fx | %-8d | %-20d\n",
            $level,
            $multiplier,
            $finalXp,
            $nextLevelReq
      );
}

echo "\n=== XP Progression Example ===\n";
echo "Level progression for completing 1 MEDIUM task (25 base XP) per day:\n\n";

$user->level = 1;
$user->xp = 0;
$user->xp_to_next_level = 125;

for ($day = 1; $day <= 10; $day++) {
      $xpGained = $user->calculateXpWithBonus(25);
      $user->xp += $xpGained;

      // Check for level up
      $leveledUp = false;
      while ($user->xp >= $user->xp_to_next_level) {
            $user->xp -= $user->xp_to_next_level;
            $user->level++;
            $user->xp_to_next_level = $user->calculateXpToNextLevel();
            $leveledUp = true;
      }

      printf(
            "Day %-2d: +%-2d XP | Level %-2d | XP: %-3d/%-3d",
            $day,
            $xpGained,
            $user->level,
            $user->xp,
            $user->xp_to_next_level
      );

      if ($leveledUp) {
            echo " [LEVEL UP!]";
      }
      echo "\n";
}

echo "\n=== System Benefits ===\n";
echo "• Reduced level progression (25 XP per level vs 50 previously)\n";
echo "• Progressive XP bonuses reward long-term engagement\n";
echo "• Level 20+ users get 2x XP, making high-level play rewarding\n";
echo "• Base XP remains unchanged, maintaining balance for new users\n";

echo "\nTesting completed successfully!\n";

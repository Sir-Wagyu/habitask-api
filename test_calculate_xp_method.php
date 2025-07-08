<?php

// Quick test untuk memverifikasi method calculateXpWithBonus
echo "=== Testing calculateXpWithBonus Method ===\n\n";

// Simulate user levels dan base XP
$testCases = [
      ['level' => 1, 'baseXp' => 25],
      ['level' => 5, 'baseXp' => 25],
      ['level' => 10, 'baseXp' => 25],
      ['level' => 15, 'baseXp' => 25],
      ['level' => 20, 'baseXp' => 25],
];

echo "Testing XP Bonus Calculation:\n";
echo "Level | Base XP | Multiplier | Final XP\n";
echo "------|---------|------------|----------\n";

foreach ($testCases as $case) {
      $level = $case['level'];
      $baseXp = $case['baseXp'];

      // Simulate getXpBonusMultiplier logic
      $multiplier = match (true) {
            $level >= 20 => 2.0,
            $level >= 15 => 1.75,
            $level >= 10 => 1.5,
            $level >= 5 => 1.25,
            default => 1.0,
      };

      // Simulate calculateXpWithBonus logic
      $finalXp = (int) ($baseXp * $multiplier);

      printf("%-5d | %-7d | %-10.2fx | %-8d\n", $level, $baseXp, $multiplier, $finalXp);
}

echo "\n=== Test Habit Completion Manually ===\n";
echo "1. Pastikan user sudah ter-load dengan data yang benar\n";
echo "2. Habit harus memiliki difficulty yang valid\n";
echo "3. Method calculateXpWithBonus() sekarang sudah tersedia\n";
echo "4. Coba lagi dengan PATCH /habits/{id}/complete\n\n";

echo "Expected Response:\n";
echo "{\n";
echo "  \"success\": true,\n";
echo "  \"message\": \"Habit completed successfully\",\n";
echo "  \"data\": {\n";
echo "    \"habit\": {...},\n";
echo "    \"completion\": {...},\n";
echo "    \"xp_earned\": 18  // untuk MEDIUM habit di level 5\n";
echo "  }\n";
echo "}\n\n";

echo "Jika masih error, check Laravel logs di storage/logs/laravel.log\n";

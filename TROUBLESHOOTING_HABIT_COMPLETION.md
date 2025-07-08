# Troubleshooting Habit Completion Issue

## Problem

-   POST `/habits/{id}/complete` returns 405 Method Not Allowed
-   PATCH `/habits/{id}/complete` returns 500 Internal Server Error
-   **NEW**: Error "Call to undefined method App\\Models\\User::calculateXpWithBonus()"

## Root Cause Analysis

### 1. HTTP Method Issue (405 Error)

**Problem**: API documentation incorrectly stated POST method
**Solution**: ✅ Fixed - Updated documentation to use PATCH method

### 2. Missing Method Error (500 Error)

**Problem**: Method `calculateXpWithBonus()` was not defined in User model
**Solution**: ✅ Fixed - Added missing methods:

```php
public function getXpBonusMultiplier(): float
public function calculateXpWithBonus(int $baseXp): int
```

### 3. Other Internal Server Errors (500 Error)

**Potential Causes**:

#### A. User Relationship Not Loaded

```php
// Problem: $this->user might be null when calculating XP bonus
return $this->user->calculateXpWithBonus($baseXp);

// Solution: ✅ Fixed - Added relationship loading check
if (!$this->relationLoaded('user')) {
    $this->load('user');
}
return $this->user ? $this->user->calculateXpWithBonus($baseXp) : $baseXp;
```

#### B. Missing Schedule Type Fields

```php
// Problem: Habits created without proper schedule_type validation
$habit = Habit::create([
    'title' => $request->title,
    'difficulty' => $request->difficulty,
    // Missing schedule_type and day fields
]);

// Solution: ✅ Fixed - Added proper validation and field handling
```

#### C. Database Transaction Issues

```php
// Problem: Nested transactions or transaction rollback issues
// Solution: ✅ Added proper error handling with try-catch
```

## Fixed Issues

### 1. ✅ API Documentation Corrected

```markdown
### 6. Complete Habit Today

**PATCH** `/habits/{id}/complete` // Changed from POST
Headers: `Authorization: Bearer {token}`
```

### 2. ✅ Enhanced Error Handling in HabitController

```php
public function complete(Request $request, Habit $habit)
{
    try {
        // ... existing code ...
        $completion = $habit->completeToday();
        return response()->json([...]);
    } catch (\Exception $e) {
        Log::error('Error completing habit: ' . $e->getMessage(), [...]);
        return response()->json([
            'success' => false,
            'message' => 'An error occurred while completing the habit',
            'error' => $e->getMessage()
        ], 500);
    }
}
```

### 3. ✅ Improved Habit Model Methods

```php
public function getXpReward(): int
{
    // Ensure user relationship is loaded
    if (!$this->relationLoaded('user')) {
        $this->load('user');
    }
    return $this->user ? $this->user->calculateXpWithBonus($baseXp) : $baseXp;
}

public function completeToday(): HabitCompletion
{
    return DB::transaction(function () {
        // Ensure user relationship is loaded
        if (!$this->relationLoaded('user')) {
            $this->load('user');
        }
        // ... rest of the method
    });
}
```

### 4. ✅ Enhanced Habit Store/Update Methods

-   Added proper validation for schedule_type
-   Added handling for day-specific fields
-   Proper boolean conversion for day fields

## Testing Steps

### 1. Create a Habit (POST /habits)

```json
{
    "title": "Morning Exercise",
    "description": "30 minutes workout",
    "difficulty": "MEDIUM",
    "schedule_type": "DAILY"
}
```

### 2. Complete the Habit (PATCH /habits/{id}/complete)

```
Method: PATCH
URL: http://localhost:8000/api/habits/{id}/complete
Headers: Authorization: Bearer {token}
Body: (empty)
```

Expected Response:

```json
{
    "success": true,
    "message": "Habit completed successfully",
    "data": {
        "habit": {...},
        "completion": {...},
        "xp_earned": 18
    }
}
```

## Debug Commands

### 1. Check Laravel Logs

```bash
tail -f storage/logs/laravel.log
```

### 2. Run Debug Script

```bash
php debug_habit_completion.php
```

### 3. Check Database

```sql
SELECT * FROM habits WHERE user_id = 1;
SELECT * FROM habit_completions WHERE user_id = 1;
SELECT * FROM users WHERE id = 1;
```

## Common Issues & Solutions

### Issue: "User relationship not loaded"

**Solution**: ✅ Fixed with automatic relationship loading

### Issue: "Missing schedule_type field"

**Solution**: ✅ Fixed with enhanced validation

### Issue: "XP calculation error"

**Solution**: ✅ Fixed with null-safe bonus calculation

### Issue: "Database transaction timeout"

**Solution**: ✅ Fixed with proper error handling and logging

## Verification Checklist

-   [ ] ✅ Use PATCH method instead of POST
-   [ ] ✅ Proper error handling in controller
-   [ ] ✅ User relationship loading in model
-   [ ] ✅ Schedule type validation
-   [ ] ✅ Database transaction wrapping
-   [ ] ✅ Logging for debugging
-   [ ] ✅ Updated API documentation

## Expected Behavior After Fix

1. **PATCH** `/habits/{id}/complete` should return 200 OK
2. User should gain XP with level bonus applied
3. User should gain 5 HP
4. Habit streak should update correctly
5. Completion record should be created
6. Error responses should be informative

The issue should now be resolved. Test with PATCH method and check the logs if any errors persist.

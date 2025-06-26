# Habitask API Documentation

## Overview

REST API backend untuk aplikasi mobile Habitask (RPG Produktivitas) yang dibangun dengan Laravel 12. API ini mendukung sistem gamifikasi dengan XP, level, HP, dan streak untuk meningkatkan motivasi produktivitas.

## Base URL

```
http://localhost:8000/api
```

## Authentication

API menggunakan Laravel Sanctum untuk autentikasi. Setelah login, gunakan token yang diterima di header `Authorization: Bearer {token}` untuk semua request yang memerlukan autentikasi.

## Response Format

Semua response menggunakan format JSON dengan struktur:

```json
{
  "success": true,
  "message": "Success message",
  "data": {...}
}
```

Untuk error:

```json
{
  "success": false,
  "message": "Error message",
  "errors": {...}
}
```

---

## Authentication Endpoints

### 1. Register

**POST** `/auth/register`

Request body:

```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Response:

```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "level": 1,
            "xp": 0,
            "xp_to_next_level": 100,
            "hp": 100,
            "title": "Pemula Produktif"
        },
        "token": "1|abc123..."
    }
}
```

### 2. Login

**POST** `/auth/login`

Request body:

```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

### 3. Logout

**POST** `/auth/logout`
Headers: `Authorization: Bearer {token}`

### 4. Get Current User

**GET** `/auth/user`
Headers: `Authorization: Bearer {token}`

---

## Task Management Endpoints

### 1. Get All Tasks

**GET** `/tasks`
Headers: `Authorization: Bearer {token}`

Query parameters:

-   `status`: `completed` | `pending` | `overdue` (optional)

### 2. Create Task

**POST** `/tasks`
Headers: `Authorization: Bearer {token}`

Request body:

```json
{
    "title": "Complete project proposal",
    "description": "Write and review project proposal for client",
    "difficulty": "MEDIUM",
    "deadline": "2024-12-31 23:59:59"
}
```

Difficulty options: `EASY`, `MEDIUM`, `HARD`, `VERY_HARD`

### 3. Get Single Task

**GET** `/tasks/{id}`
Headers: `Authorization: Bearer {token}`

### 4. Update Task

**PUT** `/tasks/{id}`
Headers: `Authorization: Bearer {token}`

### 5. Delete Task

**DELETE** `/tasks/{id}`
Headers: `Authorization: Bearer {token}`

### 6. Complete/Uncomplete Task

**PATCH** `/tasks/{id}/complete`
Headers: `Authorization: Bearer {token}`

Request body:

```json
{
    "is_completed": true
}
```

### 7. Add Sub-task

**POST** `/tasks/{id}/sub-tasks`
Headers: `Authorization: Bearer {token}`

Request body:

```json
{
    "title": "Research competitors",
    "description": "Analyze competitor products"
}
```

### 8. Update Sub-task

**PUT** `/tasks/{taskId}/sub-tasks/{subTaskId}`
Headers: `Authorization: Bearer {token}`

### 9. Delete Sub-task

**DELETE** `/tasks/{taskId}/sub-tasks/{subTaskId}`
Headers: `Authorization: Bearer {token}`

---

## Habit Management Endpoints

### 1. Get All Habits

**GET** `/habits`
Headers: `Authorization: Bearer {token}`

### 2. Create Habit

**POST** `/habits`
Headers: `Authorization: Bearer {token}`

Request body:

```json
{
    "title": "Morning Exercise",
    "description": "30 minutes of cardio",
    "difficulty": "MEDIUM",
    "schedule_type": "SPECIFIC_DAYS",
    "is_on_monday": true,
    "is_on_tuesday": false,
    "is_on_wednesday": true,
    "is_on_thursday": false,
    "is_on_friday": true,
    "is_on_saturday": false,
    "is_on_sunday": false
}
```

Schedule types: `DAILY`, `WEEKLY`, `SPECIFIC_DAYS`

### 3. Get Single Habit

**GET** `/habits/{id}`
Headers: `Authorization: Bearer {token}`

### 4. Update Habit

**PUT** `/habits/{id}`
Headers: `Authorization: Bearer {token}`

### 5. Delete Habit

**DELETE** `/habits/{id}`
Headers: `Authorization: Bearer {token}`

### 6. Complete Habit Today

**POST** `/habits/{id}/complete`
Headers: `Authorization: Bearer {token}`

Response includes XP earned and updated streak.

### 7. Get Habit Completions

**GET** `/habits/{id}/completions`
Headers: `Authorization: Bearer {token}`

Query parameters:

-   `start_date`: `YYYY-MM-DD` (optional)
-   `end_date`: `YYYY-MM-DD` (optional)

### 8. Get Habits Due Today

**GET** `/habits/due-today`
Headers: `Authorization: Bearer {token}`

Returns habits that are scheduled for today but not yet completed.

---

## User Profile Endpoints

### 1. Get User Profile

**GET** `/user/profile`
Headers: `Authorization: Bearer {token}`

### 2. Update Profile

**PUT** `/user/profile`
Headers: `Authorization: Bearer {token}`

Request body:

```json
{
    "name": "John Doe Updated",
    "email": "john.updated@example.com"
}
```

### 3. Get User Stats

**GET** `/user/stats`
Headers: `Authorization: Bearer {token}`

Response:

```json
{
    "success": true,
    "data": {
        "total_tasks": 25,
        "completed_tasks": 18,
        "pending_tasks": 7,
        "total_habits": 5,
        "active_habits": 4,
        "total_habit_completions": 45,
        "current_streaks": {
            "Morning Exercise": 7,
            "Read Books": 3
        }
    }
}
```

---

## Dashboard Endpoint

### 1. Get Dashboard Data

**GET** `/dashboard`
Headers: `Authorization: Bearer {token}`

Response:

```json
{
  "success": true,
  "data": {
    "user": {
      "name": "John Doe",
      "level": 5,
      "xp": 75,
      "xp_to_next_level": 250,
      "hp": 85,
      "title": "Pembangun Kebiasaan"
    },
    "today_summary": {
      "habits_due": 3,
      "habits_completed": 1,
      "tasks_due": 2,
      "tasks_completed": 0
    },
    "recent_tasks": [...],
    "active_habits": [...],
    "recent_completions": [...]
  }
}
```

---

## Gamification System

### XP Rewards (Base + Level Bonus)

**Base XP Rewards:**

-   **Tasks:**

    -   EASY: 10 XP
    -   MEDIUM: 25 XP
    -   HARD: 50 XP
    -   VERY_HARD: 100 XP

-   **Habits:**
    -   EASY: 5 XP
    -   MEDIUM: 15 XP
    -   HARD: 30 XP
    -   VERY_HARD: 60 XP

**Level Bonus Multipliers:**

-   Level 1-4: No bonus (1.0x)
-   Level 5-9: 25% bonus (1.25x)
-   Level 10-14: 50% bonus (1.5x)
-   Level 15-19: 75% bonus (1.75x)
-   Level 20+: 100% bonus (2.0x)

_Example: A MEDIUM task (25 base XP) at level 10 gives 37 XP (25 × 1.5)_

### HP System

-   Start with 100 HP
-   Lose HP when missing task deadlines:
    -   EASY: -5 HP
    -   MEDIUM: -10 HP
    -   HARD: -20 HP
    -   VERY_HARD: -35 HP
-   Restore 5 HP when completing habits

### Level System

-   Start at Level 1
-   **Improved level up formula: 100 + (level × 25) XP needed**
-   Progression examples:
    -   Level 1→2: 125 XP needed
    -   Level 2→3: 150 XP needed
    -   Level 5→6: 225 XP needed
    -   Level 10→11: 350 XP needed
-   Titles based on level:
    -   Level 1-4: "Pemula Produktif"
    -   Level 5-9: "Pembangun Kebiasaan"
    -   Level 10-14: "Pahlawan Produktif"
    -   Level 15-19: "Ahli Kebiasaan"
    -   Level 20+: "Master Produktivitas"

### Streak System

-   Habits track consecutive completion days
-   Smart streak calculation for SPECIFIC_DAYS habits
-   Streak continues if no valid days were missed between completions

---

## Error Codes

-   **400** - Bad Request (validation errors)
-   **401** - Unauthorized (invalid token)
-   **403** - Forbidden (access denied)
-   **404** - Not Found (resource doesn't exist)
-   **422** - Unprocessable Entity (validation failed)
-   **500** - Internal Server Error

---

## Development Notes

### Database Transactions

All operations that involve XP/HP changes use database transactions to ensure data consistency.

### XP Reward System Improvements

-   **Level-based XP Bonus**: Higher level users receive bonus XP multipliers to maintain engagement
-   **Reduced Level Progression**: XP requirement reduced from 50 per level to 25 per level for smoother progression
-   **Balanced Reward System**: Base XP remains consistent while bonus scales with user achievement

### Streak Calculation Improvements

The streak system has been enhanced for SPECIFIC_DAYS habits to be more forgiving - it maintains streaks as long as no valid scheduled days are missed between completions.

### Performance Optimizations

-   Database transactions ensure atomicity for complex operations
-   Efficient streak calculation algorithms for different schedule types
-   Optimized XP calculation with level-based bonuses

### Testing

All endpoints have been tested with Postman and are working correctly. The API is ready for frontend integration.

---

## Next Steps for Frontend Integration

1. **Authentication Flow**: Implement login/register screens and token storage
2. **Dashboard**: Create main dashboard showing user stats and today's summary
3. **Task Management**: Build task list, creation, and completion interfaces
4. **Habit Tracking**: Create habit management and daily completion interfaces
5. **Profile**: User profile and stats visualization
6. **Gamification UI**: Level progress bars, XP animations, HP indicators

The API provides all necessary data for a complete RPG-style productivity application. All responses include the gamification data needed to create an engaging user experience.

# Habitask API - Changelog Perbaikan Minor

## Ringkasan Perubahan

### 1. Sistem Bonus XP Berdasarkan Level

**Problem**: User dengan level tinggi kesulitan naik level karena requirement XP yang tinggi namun reward XP tetap sama.

**Solution**:

-   Menambahkan bonus multiplier XP berdasarkan level user
-   Base XP tetap sama untuk menjaga balance bagi user baru
-   Bonus XP meningkat seiring dengan level user

**Level Bonus Multipliers**:

-   Level 1-4: 1.0x (No bonus)
-   Level 5-9: 1.25x (25% bonus)
-   Level 10-14: 1.5x (50% bonus)
-   Level 15-19: 1.75x (75% bonus)
-   Level 20+: 2.0x (100% bonus)

**Contoh**:

-   MEDIUM Task (25 base XP) di Level 1 = 25 XP
-   MEDIUM Task (25 base XP) di Level 10 = 37 XP (25 × 1.5)
-   MEDIUM Task (25 base XP) di Level 20 = 50 XP (25 × 2.0)

### 2. Formula Level Up yang Lebih Seimbang

**Problem**: Progression 100 + (level × 50) terlalu tinggi untuk level tinggi.

**Solution**:

-   Mengurangi progression menjadi 100 + (level × 25)
-   Lebih mudah untuk user mencapai level tinggi
-   Tetap menantang namun tidak mengecilkan hati

**Perbandingan**:

```
Level | Formula Lama | Formula Baru | Selisih
------|--------------|--------------|--------
1→2   | 150          | 125          | -25
5→6   | 350          | 225          | -125
10→11 | 600          | 350          | -250
15→16 | 850          | 475          | -375
20→21 | 1100         | 600          | -500
```

### 3. Perbaikan Database Transactions

**Problem**: Operasi XP/HP bisa gagal di tengah jalan tanpa rollback.

**Solution**:

-   Semua operasi penting dibungkus dalam `DB::transaction()`
-   `User::addXp()` menggunakan transaksional
-   `Task::complete()` menggunakan transaksional
-   `Habit::completeToday()` menggunakan transaksional
-   `Task::applyPenalty()` menggunakan transaksional

### 4. Perbaikan Logika Streak untuk SPECIFIC_DAYS

**Problem**: Streak putus jika user tidak menyelesaikan habit "kemarin" untuk jadwal SPECIFIC_DAYS.

**Solution**:

-   Method `updateStreakForSpecificDays()` yang lebih smart
-   Mencari hari valid terakhir berdasarkan jadwal
-   Streak tetap berlanjut jika tidak ada hari valid yang terlewat
-   Menggunakan `Carbon::format('Y-m-d')` untuk perbandingan tanggal yang akurat

### 5. Method dan Helper Baru

**User Model**:

-   `getXpBonusMultiplier()`: Menghitung multiplier bonus berdasarkan level
-   `calculateXpWithBonus(int $baseXp)`: Menghitung XP final dengan bonus
-   `getGamificationData()`: Data lengkap gamifikasi untuk API response

**Habit Model**:

-   `getLastValidDayBefore()`: Mencari hari valid terakhir untuk SPECIFIC_DAYS
-   `countValidDaysBetween()`: Menghitung hari valid yang terlewat
-   `updateStreakForSpecificDays()`: Logika streak khusus SPECIFIC_DAYS

### 6. Dokumentasi API Update

-   Update gamification system documentation
-   Penjelasan sistem bonus XP
-   Contoh perhitungan XP dengan level bonus
-   Formula level up yang baru
-   Development notes yang komprehensif

## Files yang Dimodifikasi

1. **app/Models/User.php**

    - Sistem bonus XP
    - Formula level up baru
    - Database transactions
    - Helper methods

2. **app/Models/Task.php**

    - XP reward dengan bonus
    - Database transactions

3. **app/Models/Habit.php**

    - XP reward dengan bonus
    - Perbaikan logika streak
    - Database transactions
    - Smart streak untuk SPECIFIC_DAYS

4. **API_DOCUMENTATION.md**

    - Update dokumentasi gamifikasi
    - Penjelasan sistem bonus
    - Development notes

5. **test_xp_bonus_system.php** (NEW)
    - Script test untuk verifikasi sistem bonus

## Benefits

### Untuk User Baru (Level 1-4):

-   Experience sama seperti sebelumnya
-   Base XP tidak berubah
-   Learning curve tetap smooth

### Untuk User Menengah (Level 5-14):

-   Mulai mendapat bonus XP 25-50%
-   Motivasi untuk terus bermain
-   Progression yang lebih balanced

### Untuk User Advanced (Level 15+):

-   Bonus XP 75-100%
-   High-level play yang rewarding
-   Maintain engagement jangka panjang

### Untuk Developer:

-   Code lebih robust dengan transactions
-   Logika streak yang lebih akurat
-   API response yang lebih komprehensif

## Testing Recommendations

1. **Test XP Bonus System**:

    ```bash
    php test_xp_bonus_system.php
    ```

2. **Test API Endpoints**:

    - Complete tasks di different levels
    - Verify XP calculations
    - Test habit completions
    - Check level progression

3. **Test Edge Cases**:
    - Multiple level ups in single action
    - SPECIFIC_DAYS streak scenarios
    - Transaction rollbacks

## Migration Notes

-   **No database migration needed** - existing data compatible
-   **Backward compatible** - API responses enhanced, not changed
-   **Immediate effect** - bonus system active for existing users
-   **Performance impact** - minimal, calculations are lightweight

## Conclusion

Perbaikan ini memberikan:
✅ Sistem reward yang lebih engaging untuk user level tinggi
✅ Progression yang lebih balanced dan achievable  
✅ Reliability yang lebih baik dengan database transactions
✅ Logika streak yang lebih akurat dan user-friendly
✅ Code quality yang lebih baik dengan proper error handling

API tetap backward compatible dan siap untuk production deployment.

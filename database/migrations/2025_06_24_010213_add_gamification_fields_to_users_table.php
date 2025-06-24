<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('level')->default(1);
            $table->integer('xp')->default(0);
            $table->integer('xp_to_next_level')->default(100);
            $table->integer('hp')->default(100);
            $table->string('title')->default('Pemula Produktif');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['level', 'xp', 'xp_to_next_level', 'hp', 'title']);
        });
    }
};

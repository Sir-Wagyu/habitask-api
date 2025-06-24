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
        Schema::create('habits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('difficulty', ['EASY', 'MEDIUM', 'HARD', 'VERY_HARD'])->default('MEDIUM');
            $table->integer('current_streak')->default(0);
            $table->date('last_completed_date')->nullable();
            $table->enum('schedule_type', ['DAILY', 'WEEKLY', 'SPECIFIC_DAYS'])->default('DAILY');
            $table->boolean('is_on_monday')->default(true);
            $table->boolean('is_on_tuesday')->default(true);
            $table->boolean('is_on_wednesday')->default(true);
            $table->boolean('is_on_thursday')->default(true);
            $table->boolean('is_on_friday')->default(true);
            $table->boolean('is_on_saturday')->default(true);
            $table->boolean('is_on_sunday')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habits');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pomodoro_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('phase', ['focus', 'short_break', 'long_break'])->default('focus');
            $table->unsignedSmallInteger('duration_minutes');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at');
            $table->enum('status', ['completed', 'cancelled'])->default('completed');
            $table->enum('source', ['timer', 'manual'])->default('timer');
            $table->uuid('batch_id')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'completed_at']);
            $table->index(['user_id', 'batch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pomodoro_sessions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('study_minutes')->default(25);
            $table->unsignedSmallInteger('short_break_minutes')->default(5);
            $table->unsignedSmallInteger('long_break_minutes')->default(15);
            $table->unsignedTinyInteger('cycles_before_long_break')->default(4);
            $table->boolean('auto_start_breaks')->default(true);
            $table->boolean('auto_start_pomodoros')->default(false);
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('sound_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};

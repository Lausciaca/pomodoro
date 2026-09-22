<?php

use App\Http\Controllers\ManualPomodoroController;
use App\Http\Controllers\PomodoroController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TimerController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('timer')
        : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/timer', [TimerController::class, 'index'])->name('timer');

    Route::get('/dashboard', [PomodoroController::class, 'dashboard'])->name('dashboard');
    Route::get('/api/pomodoros/today', [PomodoroController::class, 'today'])->name('pomodoros.today');
    Route::post('/api/pomodoros', [PomodoroController::class, 'store'])->name('pomodoros.store');

    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');

    Route::get('/history/manual', [ManualPomodoroController::class, 'create'])->name('history.manual.create');
    Route::post('/history/manual', [ManualPomodoroController::class, 'store'])->name('history.manual.store');
    Route::delete('/history/batch/{batchId}', [ManualPomodoroController::class, 'destroyBatch'])->name('history.manual.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

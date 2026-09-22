<?php

namespace App\Http\Controllers;

use App\Models\PomodoroSession;
use App\Models\UserSetting;
use Illuminate\Support\Facades\Auth;

class TimerController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $settings = UserSetting::forUser($userId);

        $todayQuery = PomodoroSession::ownedBy($userId)
            ->today()
            ->focus()
            ->completed();

        $todayCount = (clone $todayQuery)->count();
        $todayMinutes = (int) (clone $todayQuery)->sum('duration_minutes');

        $timerConfig = [
            'study' => $settings->study_minutes,
            'shortBreak' => $settings->short_break_minutes,
            'longBreak' => $settings->long_break_minutes,
            'cycles' => $settings->cycles_before_long_break,
            'autoStartBreaks' => $settings->auto_start_breaks,
            'autoStartPomodoros' => $settings->auto_start_pomodoros,
            'notifications' => $settings->notifications_enabled,
            'sound' => $settings->sound_enabled,
        ];

        return view('timer', compact('settings', 'todayCount', 'todayMinutes', 'timerConfig'));
    }
}

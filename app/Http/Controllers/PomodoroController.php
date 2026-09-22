<?php

namespace App\Http\Controllers;

use App\Models\PomodoroSession;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PomodoroController extends Controller
{
    public function dashboard()
    {
        $userId = Auth::id();

        $todayQuery = PomodoroSession::ownedBy($userId)
            ->today()
            ->focus()
            ->completed();

        $todayCount = (clone $todayQuery)->count();
        $todayMinutes = (int) (clone $todayQuery)->sum('duration_minutes');

        $todaySessions = (clone $todayQuery)
            ->latest('completed_at')
            ->get();

        $settings = UserSetting::forUser($userId);

        $week = PomodoroSession::ownedBy($userId)
            ->focus()
            ->completed()
            ->whereBetween('completed_at', [now()->subDays(6)->startOfDay(), now()->endOfDay()])
            ->get()
            ->groupBy(fn (PomodoroSession $session) => $session->completed_at->toDateString())
            ->map(fn ($group) => [
                'count' => $group->count(),
                'minutes' => (int) $group->sum('duration_minutes'),
            ]);

        $weekDays = collect(range(6, 0))->map(function (int $daysAgo) use ($week) {
            $date = now()->subDays($daysAgo)->toDateString();

            return [
                'date' => $date,
                'label' => now()->subDays($daysAgo)->translatedFormat('D d/m'),
                'count' => $week->get($date)['count'] ?? 0,
                'minutes' => $week->get($date)['minutes'] ?? 0,
            ];
        });

        return view('dashboard', compact(
            'todayCount',
            'todayMinutes',
            'todaySessions',
            'settings',
            'weekDays',
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'phase' => ['required', 'in:focus,short_break,long_break'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:180'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'status' => ['nullable', 'in:completed,cancelled'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $userId = Auth::id();

        $session = PomodoroSession::create([
            'user_id' => $userId,
            'phase' => $validated['phase'],
            'duration_minutes' => (int) $validated['duration_minutes'],
            'started_at' => $validated['started_at'] ?? now()->subMinutes((int) $validated['duration_minutes']),
            'completed_at' => $validated['completed_at'] ?? now(),
            'status' => $validated['status'] ?? 'completed',
            'source' => PomodoroSession::SOURCE_TIMER,
            'note' => $validated['note'] ?? null,
        ]);

        $todayQuery = PomodoroSession::ownedBy($userId)->today()->focus()->completed();

        return response()->json([
            'ok' => true,
            'id' => $session->id,
            'today_count' => (clone $todayQuery)->count(),
            'today_minutes' => (int) (clone $todayQuery)->sum('duration_minutes'),
        ]);
    }

    public function today()
    {
        $userId = Auth::id();

        $todayQuery = PomodoroSession::ownedBy($userId)->today()->focus()->completed();

        return response()->json([
            'today_count' => (clone $todayQuery)->count(),
            'today_minutes' => (int) (clone $todayQuery)->sum('duration_minutes'),
            'sessions' => (clone $todayQuery)
                ->latest('completed_at')
                ->get(['id', 'duration_minutes', 'completed_at', 'source']),
        ]);
    }
}

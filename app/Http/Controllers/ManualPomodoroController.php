<?php

namespace App\Http\Controllers;

use App\Models\PomodoroSession;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManualPomodoroController extends Controller
{
    public function create()
    {
        $settings = UserSetting::forUser(Auth::id());

        return view('history.manual', compact('settings'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:180'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        $userId = Auth::id();
        $batchId = (string) Str::uuid();
        $duration = (int) $validated['duration_minutes'];
        $baseStart = Carbon::parse($validated['date'])->setTime(9, 0, 0);

        $rows = [];

        for ($i = 0; $i < (int) $validated['quantity']; $i++) {
            $startedAt = $baseStart->copy()->addMinutes($i * $duration);
            $completedAt = $startedAt->copy()->addMinutes($duration);

            $rows[] = [
                'user_id' => $userId,
                'phase' => PomodoroSession::PHASE_FOCUS,
                'duration_minutes' => $duration,
                'started_at' => $startedAt->toDateTimeString(),
                'completed_at' => $completedAt->toDateTimeString(),
                'status' => 'completed',
                'source' => PomodoroSession::SOURCE_MANUAL,
                'batch_id' => $batchId,
                'note' => $validated['note'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::transaction(function () use ($rows) {
            PomodoroSession::insert($rows);
        });

        return redirect()
            ->route('dashboard')
            ->with('status', 'manual-registered')
            ->with('registered_quantity', (int) $validated['quantity']);
    }

    public function destroyBatch(string $batchId)
    {
        $deleted = PomodoroSession::ownedBy(Auth::id())
            ->where('batch_id', $batchId)
            ->delete();

        if ($deleted === 0) {
            abort(404);
        }

        return redirect()
            ->route('dashboard')
            ->with('status', 'batch-deleted')
            ->with('deleted_quantity', $deleted);
    }
}

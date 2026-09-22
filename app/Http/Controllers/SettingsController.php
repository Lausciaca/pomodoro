<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SettingsController extends Controller
{
    public function edit()
    {
        $settings = UserSetting::forUser(Auth::id());

        return view('settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'study_minutes' => ['required', 'integer', 'min:1', 'max:180'],
            'short_break_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            'long_break_minutes' => ['required', 'integer', 'min:1', 'max:120'],
            'cycles_before_long_break' => ['required', 'integer', 'min:1', 'max:12'],
            'daily_goal' => ['required', 'integer', 'min:1', 'max:50'],
            'auto_start_breaks' => ['boolean'],
            'auto_start_pomodoros' => ['boolean'],
            'notifications_enabled' => ['boolean'],
            'sound_enabled' => ['boolean'],
        ]);

        $validated['auto_start_breaks'] = $request->boolean('auto_start_breaks');
        $validated['auto_start_pomodoros'] = $request->boolean('auto_start_pomodoros');
        $validated['notifications_enabled'] = $request->boolean('notifications_enabled');
        $validated['sound_enabled'] = $request->boolean('sound_enabled');

        UserSetting::updateOrCreate(
            ['user_id' => Auth::id()],
            $validated
        );

        return redirect()
            ->route('settings.edit')
            ->with('status', 'settings-updated');
    }
}

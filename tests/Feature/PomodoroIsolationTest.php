<?php

namespace Tests\Feature;

use App\Models\PomodoroSession;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class PomodoroIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_protected_routes(): void
    {
        $this->get(route('timer'))->assertRedirect(route('login'));
        $this->get(route('dashboard'))->assertRedirect(route('login'));
        $this->get(route('settings.edit'))->assertRedirect(route('login'));
        $this->get(route('history.manual.create'))->assertRedirect(route('login'));
        $this->getJson(route('pomodoros.today'))->assertUnauthorized();
        $this->postJson(route('pomodoros.store'), [])->assertUnauthorized();
    }

    public function test_registration_creates_default_settings_owned_by_the_user(): void
    {
        $response = $this->post('/register', [
            'name' => 'Ana',
            'email' => 'ana@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'ana@example.com')->firstOrFail();

        $this->assertDatabaseHas('user_settings', [
            'user_id' => $user->id,
            'study_minutes' => 25,
        ]);
    }

    public function test_timer_and_dashboard_only_show_own_data(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        PomodoroSession::create([
            'user_id' => $userA->id,
            'phase' => 'focus',
            'duration_minutes' => 25,
            'completed_at' => now(),
            'source' => 'timer',
        ]);

        PomodoroSession::create([
            'user_id' => $userB->id,
            'phase' => 'focus',
            'duration_minutes' => 50,
            'completed_at' => now(),
            'source' => 'timer',
        ]);

        $this->actingAs($userA)
            ->getJson(route('pomodoros.today'))
            ->assertOk()
            ->assertJson([
                'today_count' => 1,
                'today_minutes' => 25,
            ]);

        $this->actingAs($userB)
            ->getJson(route('pomodoros.today'))
            ->assertOk()
            ->assertJson([
                'today_count' => 1,
                'today_minutes' => 50,
            ]);
    }

    public function test_settings_update_only_affects_the_authenticated_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->actingAs($userA)->put(route('settings.update'), [
            'study_minutes' => 45,
            'short_break_minutes' => 10,
            'long_break_minutes' => 30,
            'cycles_before_long_break' => 3,
            'daily_goal' => 12,
            'notifications_enabled' => '1',
        ])->assertRedirect(route('settings.edit'));

        $settings = UserSetting::forUser($userA->id);

        $this->assertSame(45, $settings->study_minutes);
        $this->assertSame(12, $settings->daily_goal);
        $this->assertSame(25, UserSetting::forUser($userB->id)->study_minutes);
        $this->assertSame(8, UserSetting::forUser($userB->id)->daily_goal);
    }

    public function test_daily_goal_is_validated_when_updating_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('settings.update'), [
            'study_minutes' => 25,
            'short_break_minutes' => 5,
            'long_break_minutes' => 15,
            'cycles_before_long_break' => 4,
            'daily_goal' => 0,
        ])->assertSessionHasErrors('daily_goal');
    }

    public function test_dashboard_and_timer_show_the_custom_daily_goal(): void
    {
        $user = User::factory()->create();

        UserSetting::forUser($user->id)->update(['daily_goal' => 6]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('6')
            ->assertSee('Meta diaria');

        $this->actingAs($user)
            ->getJson(route('pomodoros.today'))
            ->assertOk()
            ->assertJson(['daily_goal' => 6]);
    }

    public function test_manual_history_is_stored_for_the_authenticated_user_and_counted(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->actingAs($userA)->post(route('history.manual.store'), [
            'date' => now()->toDateString(),
            'quantity' => 3,
            'duration_minutes' => 25,
            'note' => 'Repaso',
        ])->assertRedirect(route('dashboard'));

        $this->assertDatabaseCount('pomodoro_sessions', 3);
        $this->assertSame(3, PomodoroSession::ownedBy($userA->id)->manual()->count());
        $this->assertSame(0, PomodoroSession::ownedBy($userB->id)->count());

        $this->actingAs($userA)
            ->getJson(route('pomodoros.today'))
            ->assertJson([
                'today_count' => 3,
                'today_minutes' => 75,
            ]);
    }

    public function test_a_user_cannot_delete_another_users_manual_batch(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $batchId = (string) Str::uuid();

        PomodoroSession::create([
            'user_id' => $userB->id,
            'phase' => 'focus',
            'duration_minutes' => 25,
            'completed_at' => now(),
            'source' => 'manual',
            'batch_id' => $batchId,
        ]);

        $this->actingAs($userA)
            ->delete(route('history.manual.destroy', $batchId))
            ->assertNotFound();

        $this->assertDatabaseHas('pomodoro_sessions', [
            'user_id' => $userB->id,
            'batch_id' => $batchId,
        ]);
    }

    public function test_a_user_can_delete_own_manual_batch(): void
    {
        $user = User::factory()->create();
        $batchId = (string) Str::uuid();

        PomodoroSession::create([
            'user_id' => $user->id,
            'phase' => 'focus',
            'duration_minutes' => 25,
            'completed_at' => now(),
            'source' => 'manual',
            'batch_id' => $batchId,
        ]);

        $this->actingAs($user)
            ->delete(route('history.manual.destroy', $batchId))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseMissing('pomodoro_sessions', [
            'batch_id' => $batchId,
        ]);
    }

    public function test_authenticated_pages_render_successfully(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('timer'))->assertOk()->assertSee('pomodoro-timer', false);
        $this->actingAs($user)->get(route('dashboard'))->assertOk()->assertSee('Pomodoros hoy');
        $this->actingAs($user)->get(route('settings.edit'))->assertOk()->assertSee('Guardar configuración');
        $this->actingAs($user)->get(route('history.manual.create'))->assertOk()->assertSee('Cantidad de pomodoros');
    }

    public function test_timer_store_records_a_session_for_the_authenticated_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->actingAs($userA)
            ->postJson(route('pomodoros.store'), [
                'phase' => 'focus',
                'duration_minutes' => 25,
                'completed_at' => now()->toIso8601String(),
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'today_count' => 1,
            ]);

        $this->assertSame(1, PomodoroSession::ownedBy($userA->id)->count());
        $this->assertSame(0, PomodoroSession::ownedBy($userB->id)->count());
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DarkModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_login_page_bootstraps_theme_before_first_paint(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('pomodoro-theme-v1', false);
        $response->assertSee('prefers-color-scheme', false);
    }

    public function test_guest_login_page_declares_color_scheme(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('color-scheme', false);
    }

    public function test_authenticated_timer_page_bootstraps_theme_before_first_paint(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/timer');

        $response->assertOk();
        $response->assertSee('pomodoro-theme-v1', false);
        $response->assertSee('prefers-color-scheme', false);
    }

    public function test_authenticated_shell_uses_dark_surface(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/timer');

        $response->assertOk();
        $response->assertSee('dark:bg-slate-950', false);
    }

    public function test_navigation_exposes_accessible_icon_only_theme_toggle(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/timer');

        $response->assertOk();
        $response->assertSee('aria-label', false);
        $response->assertSee('$store.theme', false);
    }

    public function test_theme_resolver_persists_key_and_broadcasts_changes(): void
    {
        $path = resource_path('js/theme.js');

        $this->assertFileExists($path);

        $content = file_get_contents($path);

        $this->assertStringContainsString('pomodoro-theme-v1', $content);
        $this->assertStringContainsString('theme-change', $content);
        $this->assertStringContainsString("store('theme')", $content);
    }

    public function test_theme_resolver_sanitizes_corrupt_values_to_system(): void
    {
        $content = file_get_contents(resource_path('js/theme.js'));

        $this->assertStringContainsString('system', $content);
        $this->assertStringContainsString('matchMedia', $content);
    }

    public function test_timer_palette_adapts_to_effective_theme(): void
    {
        $content = file_get_contents(resource_path('js/timer.js'));

        $this->assertStringContainsString('theme-change', $content);
        $this->assertStringContainsString('#1e293b', $content);
        $this->assertStringContainsString('#334155', $content);
    }

    public function test_tailwind_config_drives_dark_mode_by_class(): void
    {
        $content = file_get_contents(base_path('tailwind.config.js'));

        $this->assertStringContainsString('darkMode', $content);
        $this->assertStringContainsString("'class'", $content);
    }

    public function test_shared_styles_declare_dark_color_scheme(): void
    {
        $content = file_get_contents(resource_path('css/app.css'));

        $this->assertStringContainsString('color-scheme', $content);
        $this->assertStringContainsString('dark:', $content);
    }

    public function test_offline_page_adapts_to_light_scheme(): void
    {
        $content = file_get_contents(public_path('offline.html'));

        $this->assertStringContainsString('prefers-color-scheme', $content);
        $this->assertStringContainsString('color-scheme', $content);
    }
}

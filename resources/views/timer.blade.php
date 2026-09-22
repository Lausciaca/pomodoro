<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Temporizador</h2>
            <p class="page-subtitle">Concéntrate por bloques y avanza hacia tu meta diaria.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl space-y-4 px-4 sm:px-6 lg:px-8">
            <div
                data-role="notification-banner"
                class="hidden items-center justify-between gap-3 rounded-2xl bg-amber-50 px-4 py-3 text-sm text-amber-800 ring-1 ring-inset ring-amber-600/20"
            >
                <span>Activa las notificaciones para enterarte cuando termine cada bloque.</span>
                <button type="button" data-role="enable-notifications" class="btn-secondary shrink-0 px-3 py-1.5 text-xs">
                    Activar
                </button>
            </div>

            <div
                id="pomodoro-timer"
                data-config="{{ json_encode($timerConfig) }}"
                class="card card-body"
            >
                <p data-role="phase" class="text-center text-sm font-semibold uppercase tracking-[0.2em]">
                    Estudio
                </p>

                <div class="relative mx-auto mt-6 h-64 w-64 sm:h-72 sm:w-72">
                    <svg class="h-full w-full -rotate-90 transform" viewBox="0 0 240 240">
                        <circle cx="120" cy="120" r="110" fill="none" stroke="#e2e8f0" stroke-width="12" />
                        <circle
                            data-role="progress"
                            cx="120" cy="120" r="110"
                            fill="none" stroke="#dc2626" stroke-width="12"
                            stroke-linecap="round"
                        />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span data-role="time" class="text-5xl font-bold tabular-nums tracking-tight text-slate-800">25:00</span>
                        <span data-role="hint" class="mt-1 text-xs text-slate-400">Presioná Espacio</span>
                    </div>
                </div>

                <div data-role="cycles" class="mt-6 flex items-center justify-center gap-2"></div>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <button type="button" data-role="toggle" class="btn px-8 py-3 text-white shadow-sm" style="background-color: #dc2626;">
                        Iniciar
                    </button>
                    <button type="button" data-role="reset" class="btn-secondary px-4 py-3">
                        Reiniciar
                    </button>
                    <button type="button" data-role="skip" class="btn-secondary px-4 py-3">
                        Saltar
                    </button>
                </div>

                <div class="mt-8 border-t border-slate-100 pt-6">
                    <div class="flex items-center justify-between text-sm">
                        <span class="font-medium text-slate-600">Meta diaria</span>
                        <span class="text-slate-500">
                            <span data-role="today-count" class="font-semibold text-slate-900">{{ $todayCount }}</span>
                            / <span data-role="goal-count">{{ $dailyGoal }}</span> pomodoros
                            · <span data-role="today-minutes" class="font-semibold text-slate-900">{{ $todayMinutes }}</span> min
                        </span>
                    </div>
                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div data-role="goal-bar" class="h-full rounded-full transition-all duration-500" style="width: 0%; background-color: #dc2626;"></div>
                    </div>
                    <p data-role="goal-message" class="mt-2 text-xs text-slate-500"></p>
                </div>
            </div>

            <p class="text-center text-xs text-slate-400">
                Las notificaciones y el sonido requieren permisos del navegador sobre HTTPS o localhost.
                <a href="{{ route('settings.edit') }}" class="underline hover:text-slate-600">Ajustar configuración</a>
            </p>
        </div>
    </div>
</x-app-layout>

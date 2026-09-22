<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Temporizador
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div
                id="pomodoro-timer"
                data-config="{{ json_encode($timerConfig) }}"
                class="bg-white shadow-sm sm:rounded-2xl p-6 sm:p-10"
            >
                <p data-role="phase" class="text-center text-sm font-semibold uppercase tracking-widest text-red-500">
                    Estudio
                </p>

                <div class="relative mx-auto mt-6 h-64 w-64">
                    <svg class="h-64 w-64 -rotate-90 transform" viewBox="0 0 240 240">
                        <circle cx="120" cy="120" r="110" fill="none" stroke="#e5e7eb" stroke-width="12" />
                        <circle
                            data-role="progress"
                            cx="120" cy="120" r="110"
                            fill="none" stroke="#ef4444" stroke-width="12"
                            stroke-linecap="round"
                        />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span data-role="time" class="text-5xl font-bold tabular-nums text-gray-800">25:00</span>
                    </div>
                </div>

                <div data-role="cycles" class="mt-6 flex items-center justify-center gap-2"></div>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                    <button
                        type="button"
                        data-role="toggle"
                        class="inline-flex items-center rounded-md bg-red-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
                    >
                        Iniciar
                    </button>
                    <button
                        type="button"
                        data-role="reset"
                        class="inline-flex items-center rounded-md bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                    >
                        Reiniciar
                    </button>
                    <button
                        type="button"
                        data-role="skip"
                        class="inline-flex items-center rounded-md bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                    >
                        Saltar
                    </button>
                </div>

                <div class="mt-8 border-t border-gray-100 pt-6 text-center text-sm text-gray-600">
                    Hoy: <span data-role="today-count" class="font-semibold text-gray-900">{{ $todayCount }}</span> pomodoros
                    · <span data-role="today-minutes" class="font-semibold text-gray-900">{{ $todayMinutes }}</span> minutos
                </div>
            </div>

            <p class="mt-4 text-center text-xs text-gray-400">
                Las notificaciones y el sonido requieren que concedas permisos en el navegador.
                <a href="{{ route('settings.edit') }}" class="underline hover:text-gray-600">Ajustar configuración</a>
            </p>
        </div>
    </div>
</x-app-layout>

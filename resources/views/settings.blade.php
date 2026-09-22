<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Configuración
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('status') === 'settings-updated')
                <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                    Configuración guardada correctamente.
                </div>
            @endif

            <div
                class="bg-white shadow-sm sm:rounded-2xl p-6 sm:p-10"
                x-data="{
                    study: {{ $settings->study_minutes }},
                    short: {{ $settings->short_break_minutes }},
                    long: {{ $settings->long_break_minutes }},
                    cycles: {{ $settings->cycles_before_long_break }},
                    get cycleMinutes() {
                        return (Number(this.study) * Number(this.cycles))
                            + (Number(this.short) * (Number(this.cycles) - 1))
                            + Number(this.long);
                    }
                }"
            >
                <form method="POST" action="{{ route('settings.update') }}" class="space-y-8">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <x-input-label for="study_minutes" value="Estudio (minutos)" />
                            <x-text-input
                                id="study_minutes"
                                name="study_minutes"
                                type="number"
                                min="1"
                                max="180"
                                class="mt-1 block w-full"
                                x-model.number="study"
                                required
                            />
                            <x-input-error :messages="$errors->get('study_minutes')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="short_break_minutes" value="Descanso corto (minutos)" />
                            <x-text-input
                                id="short_break_minutes"
                                name="short_break_minutes"
                                type="number"
                                min="1"
                                max="60"
                                class="mt-1 block w-full"
                                x-model.number="short"
                                required
                            />
                            <x-input-error :messages="$errors->get('short_break_minutes')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="long_break_minutes" value="Descanso largo (minutos)" />
                            <x-text-input
                                id="long_break_minutes"
                                name="long_break_minutes"
                                type="number"
                                min="1"
                                max="120"
                                class="mt-1 block w-full"
                                x-model.number="long"
                                required
                            />
                            <x-input-error :messages="$errors->get('long_break_minutes')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="cycles_before_long_break" value="Ciclos para descanso largo" />
                            <x-text-input
                                id="cycles_before_long_break"
                                name="cycles_before_long_break"
                                type="number"
                                min="1"
                                max="12"
                                class="mt-1 block w-full"
                                x-model.number="cycles"
                                required
                            />
                            <x-input-error :messages="$errors->get('cycles_before_long_break')" class="mt-2" />
                        </div>
                    </div>

                    <div class="rounded-lg bg-gray-50 p-4 text-sm text-gray-600">
                        Un ciclo completo dura
                        <span class="font-semibold text-gray-900" x-text="cycleMinutes"></span> minutos
                        (<span x-text="cycles"></span> pomodoros + descansos).
                    </div>

                    <div class="space-y-4 border-t border-gray-100 pt-6">
                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="auto_start_breaks" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                @checked($settings->auto_start_breaks)>
                            <span class="text-sm text-gray-700">Iniciar automáticamente los descansos</span>
                        </label>

                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="auto_start_pomodoros" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                @checked($settings->auto_start_pomodoros)>
                            <span class="text-sm text-gray-700">Iniciar automáticamente el siguiente pomodoro</span>
                        </label>

                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="notifications_enabled" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                @checked($settings->notifications_enabled)>
                            <span class="text-sm text-gray-700">Notificaciones de escritorio</span>
                        </label>

                        <label class="flex items-center gap-3">
                            <input type="checkbox" name="sound_enabled" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500"
                                @checked($settings->sound_enabled)>
                            <span class="text-sm text-gray-700">Alerta de sonido</span>
                        </label>
                    </div>

                    <div class="flex items-center justify-end">
                        <x-primary-button>Guardar configuración</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

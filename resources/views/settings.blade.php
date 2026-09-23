<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Configuración</h2>
            <p class="page-subtitle">Ajustá los tiempos, tu meta diaria y los avisos del temporizador.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'settings-updated')
                <div class="alert-success mb-4">
                    Configuración guardada correctamente.
                </div>
            @endif

            <div
                class="card card-body"
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
                <form method="POST" action="{{ route('settings.update') }}" class="space-y-10">
                    @csrf
                    @method('PUT')

                    <section>
                        <h3 class="section-title">Duraciones</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Definí cuánto dura cada bloque de trabajo y descanso.</p>

                        <div class="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-2">
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

                        <div class="mt-5 rounded-xl bg-slate-50 p-4 text-sm text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            Un ciclo completo dura
                            <span class="font-semibold text-slate-900 dark:text-slate-100" x-text="cycleMinutes"></span> minutos
                            (<span x-text="cycles"></span> pomodoros + descansos).
                        </div>
                    </section>

                    <section class="border-t border-slate-100 pt-8 dark:border-slate-800">
                        <h3 class="section-title">Meta diaria</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">¿Cuántos pomodoros querés completar por día?</p>

                        <div class="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <x-input-label for="daily_goal" value="Pomodoros por día" />
                                <x-text-input
                                    id="daily_goal"
                                    name="daily_goal"
                                    type="number"
                                    min="1"
                                    max="50"
                                    class="mt-1 block w-full"
                                    :value="old('daily_goal', $settings->daily_goal)"
                                    required
                                />
                                <x-input-error :messages="$errors->get('daily_goal')" class="mt-2" />
                            </div>
                            <div class="flex items-end">
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    Es la referencia que verás en el dashboard y el temporizador para seguir tu progreso.
                                </p>
                            </div>
                        </div>
                    </section>

                    <section class="border-t border-slate-100 pt-8 dark:border-slate-800">
                        <h3 class="section-title">Automatización</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Controlá cómo se encadenan los bloques.</p>

                        <div class="mt-5 space-y-4">
                            <label class="flex items-start gap-3">
                                <input type="checkbox" name="auto_start_breaks" value="1" class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-900"
                                    @checked($settings->auto_start_breaks)>
                                <span class="text-sm text-slate-700 dark:text-slate-300">Iniciar automáticamente los descansos</span>
                            </label>

                            <label class="flex items-start gap-3">
                                <input type="checkbox" name="auto_start_pomodoros" value="1" class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-900"
                                    @checked($settings->auto_start_pomodoros)>
                                <span class="text-sm text-slate-700 dark:text-slate-300">Iniciar automáticamente el siguiente pomodoro</span>
                            </label>
                        </div>
                    </section>

                    <section class="border-t border-slate-100 pt-8 dark:border-slate-800">
                        <h3 class="section-title">Notificaciones y sonido</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Recibí un aviso cuando termine cada bloque.</p>

                        <div class="mt-5 space-y-4" data-role="notifications-panel">
                            <label class="flex items-start gap-3">
                                <input type="checkbox" name="notifications_enabled" value="1" data-role="notifications-toggle"
                                    class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-900"
                                    @checked($settings->notifications_enabled)>
                                <span class="text-sm text-slate-700 dark:text-slate-300">Notificaciones de escritorio</span>
                            </label>

                            <div class="flex flex-wrap items-center gap-3 rounded-xl bg-slate-50 px-4 py-3 dark:bg-slate-800">
                                <span data-role="notification-status" class="text-xs font-medium text-amber-600 dark:text-amber-400">Permiso pendiente</span>
                                <div class="flex flex-wrap gap-2">
                                    <button type="button" data-role="request-notifications" class="btn-secondary px-3 py-1.5 text-xs">
                                        Activar notificaciones
                                    </button>
                                    <button type="button" data-role="test-notification" class="btn-secondary hidden px-3 py-1.5 text-xs">
                                        Enviar notificación de prueba
                                    </button>
                                </div>
                            </div>

                            <label class="flex items-start gap-3">
                                <input type="checkbox" name="sound_enabled" value="1" class="mt-0.5 rounded border-slate-300 text-brand-600 focus:ring-brand-500 dark:border-slate-600 dark:bg-slate-900"
                                    @checked($settings->sound_enabled)>
                                <span class="text-sm text-slate-700 dark:text-slate-300">Alerta de sonido</span>
                            </label>
                        </div>
                    </section>

                    <div class="flex items-center justify-end border-t border-slate-100 pt-6 dark:border-slate-800">
                        <x-primary-button>Guardar configuración</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

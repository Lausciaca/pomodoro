<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="page-title">Cargar historial manual</h2>
            <p class="page-subtitle">Registrá pomodoros que completaste fuera de la app.</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div
                class="card card-body"
                x-data="{
                    date: '{{ old('date', now()->toDateString()) }}',
                    quantity: {{ (int) old('quantity', 1) }},
                    duration: {{ (int) old('duration_minutes', $settings->study_minutes) }},
                    get total() {
                        return Number(this.quantity) * Number(this.duration);
                    }
                }"
            >
                <p class="text-sm text-slate-600">
                    Se agregarán a las estadísticas de la fecha elegida y contarán para tu meta diaria.
                </p>

                <form method="POST" action="{{ route('history.manual.store') }}" class="mt-8 space-y-6">
                    @csrf

                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div>
                            <x-input-label for="date" value="Fecha" />
                            <x-text-input
                                id="date"
                                name="date"
                                type="date"
                                class="mt-1 block w-full"
                                max="{{ now()->toDateString() }}"
                                x-model="date"
                                required
                            />
                            <x-input-error :messages="$errors->get('date')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="quantity" value="Cantidad de pomodoros" />
                            <x-text-input
                                id="quantity"
                                name="quantity"
                                type="number"
                                min="1"
                                max="100"
                                class="mt-1 block w-full"
                                x-model.number="quantity"
                                required
                            />
                            <x-input-error :messages="$errors->get('quantity')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="duration_minutes" value="Duración (minutos)" />
                            <x-text-input
                                id="duration_minutes"
                                name="duration_minutes"
                                type="number"
                                min="1"
                                max="180"
                                class="mt-1 block w-full"
                                x-model.number="duration"
                                required
                            />
                            <x-input-error :messages="$errors->get('duration_minutes')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="note" value="Nota (opcional)" />
                        <x-text-input
                            id="note"
                            name="note"
                            type="text"
                            maxlength="255"
                            class="mt-1 block w-full"
                            placeholder="Ej: sesión de repaso, curso de Laravel..."
                        />
                        <x-input-error :messages="$errors->get('note')" class="mt-2" />
                    </div>

                    <div class="rounded-xl bg-brand-50 p-4 text-sm text-brand-700">
                        Registrarás <span class="font-semibold" x-text="quantity"></span> pomodoros de
                        <span class="font-semibold" x-text="duration"></span> min =
                        <span class="font-semibold" x-text="total"></span> minutos el
                        <span class="font-semibold" x-text="date"></span>.
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-100 pt-6">
                        <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-500 hover:text-slate-700">
                            Volver al dashboard
                        </a>
                        <x-primary-button>Guardar historial</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

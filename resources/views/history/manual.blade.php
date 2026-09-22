<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Cargar historial manual
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white shadow-sm sm:rounded-2xl p-6 sm:p-10"
                x-data="{
                    date: '{{ now()->toDateString() }}',
                    quantity: 1,
                    duration: {{ $settings->study_minutes }},
                    get total() {
                        return Number(this.quantity) * Number(this.duration);
                    }
                }"
            >
                <p class="text-sm text-gray-600">
                    Registra pomodoros que completaste en otra app o fuera de la computadora.
                    Se agregarán a las estadísticas de la fecha elegida.
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

                    <div class="rounded-lg bg-red-50 p-4 text-sm text-red-700">
                        Registrarás <span class="font-semibold" x-text="quantity"></span> pomodoros de
                        <span class="font-semibold" x-text="duration"></span> min =
                        <span class="font-semibold" x-text="total"></span> minutos el
                        <span class="font-semibold" x-text="date"></span>.
                    </div>

                    <div class="flex items-center justify-between">
                        <a href="{{ route('dashboard') }}" class="text-sm text-gray-500 hover:text-gray-700">
                            Volver al dashboard
                        </a>
                        <x-primary-button>Guardar historial</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>

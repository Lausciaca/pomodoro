<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Dashboard
            </h2>
            <a
                href="{{ route('history.manual.create') }}"
                class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500"
            >
                Cargar historial manual
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status') === 'manual-registered')
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                    Se registraron {{ session('registered_quantity') }} pomodoros manuales.
                </div>
            @endif

            @if (session('status') === 'batch-deleted')
                <div class="rounded-md bg-green-50 p-4 text-sm text-green-700">
                    Se eliminaron {{ session('deleted_quantity') }} registros del lote.
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div class="bg-white shadow-sm sm:rounded-2xl p-6">
                    <p class="text-sm font-medium text-gray-500">Pomodoros hoy</p>
                    <p class="mt-2 text-4xl font-bold text-gray-900">{{ $todayCount }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-2xl p-6">
                    <p class="text-sm font-medium text-gray-500">Minutos enfocados hoy</p>
                    <p class="mt-2 text-4xl font-bold text-gray-900">{{ $todayMinutes }}</p>
                </div>
                <div class="bg-white shadow-sm sm:rounded-2xl p-6">
                    <p class="text-sm font-medium text-gray-500">Meta diaria</p>
                    <p class="mt-2 text-4xl font-bold text-gray-900">
                        {{ $settings->cycles_before_long_break * 2 }}
                        <span class="text-base font-normal text-gray-400">pomodoros</span>
                    </p>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-700">Últimos 7 días</h3>
                <div class="mt-4 flex items-end justify-between gap-2">
                    @php($maxWeek = max(1, $weekDays->max('count')))
                    @foreach ($weekDays as $day)
                        <div class="flex flex-1 flex-col items-center gap-2">
                            <span class="text-xs font-semibold text-gray-600">{{ $day['count'] }}</span>
                            <div class="flex h-32 w-full items-end rounded bg-gray-100">
                                <div
                                    class="w-full rounded bg-red-500 transition-all"
                                    style="height: {{ $day['count'] > 0 ? max(6, ($day['count'] / $maxWeek) * 100) : 0 }}%"
                                ></div>
                            </div>
                            <span class="text-[11px] capitalize text-gray-500">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-2xl p-6">
                <h3 class="text-sm font-semibold text-gray-700">Historial de hoy</h3>

                @if ($todaySessions->isEmpty())
                    <p class="mt-4 text-sm text-gray-500">
                        Aún no has completado ningún pomodoro hoy.
                    </p>
                @else
                    @php($seenBatches = [])
                    <div class="mt-4 overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wider text-gray-500">
                                    <th class="py-2 pr-4 font-medium">Hora</th>
                                    <th class="py-2 pr-4 font-medium">Duración</th>
                                    <th class="py-2 pr-4 font-medium">Origen</th>
                                    <th class="py-2 pr-4 font-medium">Nota</th>
                                    <th class="py-2 font-medium"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($todaySessions as $session)
                                    <tr>
                                        <td class="py-3 pr-4 text-gray-700">
                                            {{ $session->completed_at->format('H:i') }}
                                        </td>
                                        <td class="py-3 pr-4 text-gray-700">
                                            {{ $session->duration_minutes }} min
                                        </td>
                                        <td class="py-3 pr-4">
                                            @if ($session->source === \App\Models\PomodoroSession::SOURCE_MANUAL)
                                                <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-700">
                                                    Manual
                                                </span>
                                            @else
                                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                                    Timer
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 pr-4 text-gray-500">
                                            {{ $session->note ?? '—' }}
                                        </td>
                                        <td class="py-3 text-right">
                                            @if ($session->source === \App\Models\PomodoroSession::SOURCE_MANUAL && $session->batch_id && ! in_array($session->batch_id, $seenBatches, true))
                                                @php($seenBatches[] = $session->batch_id)
                                                <form method="POST" action="{{ route('history.manual.destroy', $session->batch_id) }}"
                                                      onsubmit="return confirm('¿Eliminar este lote manual?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-500">
                                                        Eliminar lote
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>

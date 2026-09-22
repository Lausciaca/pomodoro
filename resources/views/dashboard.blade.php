<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="page-title">Dashboard</h2>
                <p class="page-subtitle">Tu progreso de hoy y resumen de la última semana.</p>
            </div>
            <a href="{{ route('history.manual.create') }}" class="btn-primary self-start">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Cargar historial manual
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status') === 'manual-registered')
                <div class="alert-success">
                    Se registraron {{ session('registered_quantity') }} pomodoros manuales.
                </div>
            @endif

            @if (session('status') === 'batch-deleted')
                <div class="alert-success">
                    Se eliminaron {{ session('deleted_quantity') }} registros del lote.
                </div>
            @endif

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                <div class="card card-body">
                    <p class="text-sm font-medium text-slate-500">Pomodoros hoy</p>
                    <p class="mt-2 flex items-baseline gap-1">
                        <span class="text-4xl font-bold tracking-tight text-slate-900">{{ $todayCount }}</span>
                        <span class="text-lg font-medium text-slate-400">/ {{ $dailyGoal }}</span>
                    </p>
                    <div class="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-full rounded-full bg-brand-500 transition-all duration-500" style="width: {{ $goalPercent }}%"></div>
                    </div>
                    <p class="mt-2 text-xs text-slate-500">{{ $goalPercent }}% de tu meta diaria</p>
                </div>

                <div class="card card-body">
                    <p class="text-sm font-medium text-slate-500">Minutos enfocados hoy</p>
                    <p class="mt-2 text-4xl font-bold tracking-tight text-slate-900">{{ $todayMinutes }}</p>
                    <p class="mt-4 text-xs text-slate-500">
                        {{ $todayCount > 0 ? round($todayMinutes / max(1, $todayCount)) : 0 }} min de promedio por sesión
                    </p>
                </div>

                <div class="card card-body">
                    <p class="text-sm font-medium text-slate-500">Meta diaria</p>
                    @if ($goalReached)
                        <p class="mt-2 text-4xl font-bold tracking-tight text-emerald-600">¡Listo!</p>
                        <p class="mt-4 text-xs text-slate-500">Cumpliste tu meta de {{ $dailyGoal }} pomodoros.</p>
                    @else
                        <p class="mt-2 flex items-baseline gap-1">
                            <span class="text-4xl font-bold tracking-tight text-slate-900">{{ $goalRemaining }}</span>
                            <span class="text-base font-medium text-slate-400">restantes</span>
                        </p>
                        <a href="{{ route('settings.edit') }}" class="mt-4 inline-block text-xs font-medium text-brand-600 hover:text-brand-500">
                            Ajustar meta diaria
                        </a>
                    @endif
                </div>
            </div>

            <div class="card card-body">
                <div class="flex items-center justify-between">
                    <h3 class="section-title">Últimos 7 días</h3>
                    <span class="text-xs text-slate-400">Meta: {{ $dailyGoal }} pomodoros/día</span>
                </div>
                @php($maxWeek = max(1, $weekDays->max('count'), $dailyGoal))
                <div class="mt-6 flex items-end justify-between gap-2">
                    @foreach ($weekDays as $day)
                        <div class="flex flex-1 flex-col items-center gap-2">
                            <span class="text-xs font-semibold text-slate-600">{{ $day['count'] }}</span>
                            <div class="relative flex h-32 w-full items-end overflow-hidden rounded-lg bg-slate-100">
                                <div
                                    class="w-full rounded-t-lg bg-brand-500 transition-all duration-500"
                                    style="height: {{ $day['count'] > 0 ? max(6, ($day['count'] / $maxWeek) * 100) : 0 }}%"
                                ></div>
                                <div
                                    class="pointer-events-none absolute inset-x-0 border-t border-dashed border-brand-400/70"
                                    style="bottom: {{ min(100, ($dailyGoal / $maxWeek) * 100) }}%"
                                    title="Meta diaria"
                                ></div>
                            </div>
                            <span class="text-[11px] capitalize text-slate-500">{{ $day['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card card-body">
                <h3 class="section-title">Historial de hoy</h3>

                @if ($todaySessions->isEmpty())
                    <div class="mt-6 flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 py-10 text-center">
                        <svg class="h-10 w-10 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="mt-3 text-sm text-slate-500">Aún no has completado ningún pomodoro hoy.</p>
                        <a href="{{ route('timer') }}" class="mt-3 text-sm font-medium text-brand-600 hover:text-brand-500">Ir al temporizador</a>
                    </div>
                @else
                    @php($seenBatches = [])
                    <div class="mt-4 -mx-6 overflow-x-auto sm:mx-0">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead>
                                <tr class="text-left text-xs uppercase tracking-wider text-slate-500">
                                    <th class="px-6 py-2 font-medium sm:px-0 sm:pr-4">Hora</th>
                                    <th class="px-6 py-2 font-medium sm:px-0 sm:pr-4">Duración</th>
                                    <th class="px-6 py-2 font-medium sm:px-0 sm:pr-4">Origen</th>
                                    <th class="px-6 py-2 font-medium sm:px-0 sm:pr-4">Nota</th>
                                    <th class="px-6 py-2 sm:px-0"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($todaySessions as $session)
                                    <tr>
                                        <td class="whitespace-nowrap px-6 py-3 text-slate-700 sm:px-0 sm:pr-4">
                                            {{ $session->completed_at->format('H:i') }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-3 text-slate-700 sm:px-0 sm:pr-4">
                                            {{ $session->duration_minutes }} min
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-3 sm:px-0 sm:pr-4">
                                            @if ($session->source === \App\Models\PomodoroSession::SOURCE_MANUAL)
                                                <span class="badge bg-blue-50 text-blue-700">Manual</span>
                                            @else
                                                <span class="badge bg-slate-100 text-slate-600">Timer</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3 text-slate-500 sm:px-0 sm:pr-4">
                                            {{ $session->note ?? '—' }}
                                        </td>
                                        <td class="whitespace-nowrap px-6 py-3 text-right sm:px-0">
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

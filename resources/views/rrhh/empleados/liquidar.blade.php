@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4 max-w-3xl">
    <h1 class="text-2xl font-bold mb-2">
        Pagar período – {{ $empleado->nombre_completo }}
    </h1>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        Período: {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}
    </p>

    @if($errors->any())
        <div class="mb-3 p-3 rounded bg-red-100 text-red-800 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- Resumen arriba --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-3">
            <div class="text-xs text-gray-500">Horas normales</div>
            <div class="text-lg font-bold">{{ number_format($horasNormales, 2) }} hs</div>
        </div>
        <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-3">
            <div class="text-xs text-gray-500">Horas extras</div>
            <div class="text-lg font-bold text-orange-600">{{ number_format($horasExtras, 2) }} hs</div>
        </div>
        <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-3">
            <div class="text-xs text-gray-500">Monto sugerido</div>
            <div class="text-lg font-bold text-green-700">$ {{ number_format($montoTotalSugerido, 2) }}</div>
        </div>
    </div>

    {{-- Form de pago --}}
    <form action="{{ route('rrhh.empleados.pagos.store', $empleado) }}" method="POST" class="space-y-4 bg-white dark:bg-gray-900 shadow-sm rounded-lg p-4">
        @csrf

        <input type="hidden" name="desde" value="{{ $desde->format('Y-m-d') }}">
        <input type="hidden" name="hasta" value="{{ $hasta->format('Y-m-d') }}">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Horas normales
                </label>
                <input type="number" step="0.01" name="horas_normales"
                       value="{{ old('horas_normales', $horasNormales) }}"
                       class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Horas extras
                </label>
                <input type="number" step="0.01" name="horas_extras"
                       value="{{ old('horas_extras', $horasExtras) }}"
                       class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Monto total ($)
                </label>
                <input type="number" step="0.01" name="monto_total"
                       value="{{ old('monto_total', $montoTotalSugerido) }}"
                       class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    $ por hora normal
                </label>
                <input type="number" step="0.01" name="monto_hora_normal"
                       value="{{ old('monto_hora_normal', $valorHoraBase) }}"
                       class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    $ por hora extra
                </label>
                <input type="number" step="0.01" name="monto_hora_extra"
                       value="{{ old('monto_hora_extra', $valorHoraExtraSugerido) }}"
                       class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Observaciones
            </label>
            <textarea name="observaciones" rows="3"
                      class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm"
            >{{ old('observaciones', 'Horas extras, sábado, etc.') }}</textarea>
        </div>

        <div class="flex justify-between mt-4">
            <a href="{{ route('rrhh.empleados.fichadas', [
                    'empleado' => $empleado->id,
                    'desde'    => $desde->format('Y-m-d'),
                    'hasta'    => $hasta->format('Y-m-d'),
                ]) }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                Volver al resumen
            </a>

            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-700">
                Confirmar pago
            </button>
        </div>
    </form>

    {{-- Últimos pagos --}}
    @if($ultimosPagos->count())
        <div class="mt-6">
            <h2 class="text-lg font-semibold mb-2">Últimos pagos</h2>
            <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-3 py-2 text-left">Fecha</th>
                            <th class="px-3 py-2 text-left">Período</th>
                            <th class="px-3 py-2 text-right">Total ($)</th>
                            <th class="px-3 py-2 text-left">Obs</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($ultimosPagos as $pago)
                            <tr>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    {{ $pago->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap">
                                    {{ optional($pago->desde)->format('d/m/Y') }} -
                                    {{ optional($pago->hasta)->format('d/m/Y') }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    $ {{ number_format($pago->monto_total, 2) }}
                                </td>
                                <td class="px-3 py-2">
                                    {{ \Illuminate\Support\Str::limit($pago->observaciones, 40) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
@endsection

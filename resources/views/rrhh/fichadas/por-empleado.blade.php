@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4">
    <h1 class="text-2xl font-bold mb-2">
        Fichadas de {{ $empleado->nombre_completo }}
    </h1>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        Período: {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}
    </p>

    @if(session('ok'))
        <div class="mb-3 p-3 rounded bg-green-100 text-green-800 text-sm">
            {{ session('ok') }}
        </div>
    @endif

    {{-- Filtros --}}
    <form method="GET"
          action="{{ route('rrhh.empleados.fichadas', $empleado) }}"
          class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Desde
            </label>
            <input type="date" name="desde"
                   value="{{ $desde->format('Y-m-d') }}"
                   class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Hasta
            </label>
            <input type="date" name="hasta"
                   value="{{ $hasta->format('Y-m-d') }}"
                   class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-md hover:bg-blue-700">
                Filtrar
            </button>

            <a href="{{ route('rrhh.empleados.fichadas', $empleado) }}"
               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                Reset
            </a>
        </div>
    </form>

    {{-- Resumen rápido --}}
    @php
        $horasNormales = round($totalNormMin / 60, 2);
        $horasExtras   = round($totalExtrasMin / 60, 2);
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
        <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-3">
            <div class="text-xs text-gray-500">Horas normales</div>
            <div class="text-lg font-bold">{{ number_format($horasNormales, 2) }} hs</div>
        </div>
        <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-3">
            <div class="text-xs text-gray-500">Horas extras</div>
            <div class="text-lg font-bold text-orange-600">{{ number_format($horasExtras, 2) }} hs</div>
        </div>
        <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-3 flex items-center justify-between">
            <div>
                <div class="text-xs text-gray-500">Total trabajadas</div>
                <div class="text-lg font-bold">{{ number_format(round($totalTrabMin / 60, 2), 2) }} hs</div>
            </div>
            <div>
                <a href="{{ route('rrhh.empleados.liquidar', [
                        'empleado' => $empleado->id,
                        'desde'    => $desde->format('Y-m-d'),
                        'hasta'    => $hasta->format('Y-m-d'),
                    ]) }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-700">
                    Pagar período
                </a>
            </div>
        </div>
    </div>

    {{-- Tabla por día --}}
    <div class="overflow-x-auto bg-white dark:bg-gray-900 shadow-sm rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-3 py-2 text-left">Fecha</th>
                    <th class="px-3 py-2 text-left">Día</th>
                    <th class="px-3 py-2 text-right">Trabajadas</th>
                    <th class="px-3 py-2 text-right">Normales</th>
                    <th class="px-3 py-2 text-right">Extras</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($resumen as $fecha => $data)
                    @php
                        $d = \Illuminate\Support\Carbon::parse($fecha);
                        $hTrab = round($data['min_trabajados'] / 60, 2);
                        $hNorm = round($data['min_normales'] / 60, 2);
                        $hExt  = round($data['min_extras'] / 60, 2);
                    @endphp
                    <tr>
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ $d->format('d/m/Y') }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ ucfirst($d->translatedFormat('l')) }}
                        </td>
                        <td class="px-3 py-2 text-right">
                            {{ number_format($hTrab, 2) }} hs
                        </td>
                        <td class="px-3 py-2 text-right">
                            {{ number_format($hNorm, 2) }} hs
                        </td>
                        <td class="px-3 py-2 text-right">
                            {{ number_format($hExt, 2) }} hs
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-4 text-center text-gray-500">
                            No hay fichadas en el período seleccionado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

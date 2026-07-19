@extends('layouts.app')

@section('content')
@php $inputCls = 'w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm'; @endphp
<div class="container mx-auto px-4 py-4 max-w-4xl">

    <div class="flex items-center justify-between mb-1">
        <h1 class="text-2xl font-bold">Adelantos – {{ $empleado->nombre_completo }}</h1>
        <a href="{{ route('rrhh.empleados.liquidar', $empleado) }}"
           class="px-3 py-2 bg-green-600 text-white text-xs font-semibold rounded-md hover:bg-green-700">Ir a liquidar →</a>
    </div>
    <p class="text-sm text-gray-500 mb-4">
        Pendientes de saldar:
        <strong class="text-orange-600">$ {{ number_format($adelantos->whereNull('empleado_pago_id')->sum('monto'), 2) }}</strong>
    </p>

    @if(session('ok'))<div class="mb-3 p-3 rounded bg-green-100 text-green-800 text-sm">{{ session('ok') }}</div>@endif
    @if($errors->any())<div class="mb-3 p-3 rounded bg-red-100 text-red-800 text-sm">{{ $errors->first() }}</div>@endif

    {{-- Nuevo adelanto --}}
    <form method="POST" action="{{ route('rrhh.empleados.adelantos.store', $empleado) }}"
          class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-4 mb-5 grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
        @csrf
        <div class="md:col-span-3">
            <label class="block text-xs font-medium mb-1">Fecha</label>
            <input type="date" name="fecha" value="{{ old('fecha', now()->format('Y-m-d')) }}" class="{{ $inputCls }}" required>
        </div>
        <div class="md:col-span-3">
            <label class="block text-xs font-medium mb-1">Monto ($)</label>
            <input type="number" step="0.01" min="0.01" name="monto" value="{{ old('monto') }}" class="{{ $inputCls }}" required>
        </div>
        <div class="md:col-span-4">
            <label class="block text-xs font-medium mb-1">Observaciones</label>
            <input type="text" name="observaciones" value="{{ old('observaciones') }}" class="{{ $inputCls }}" placeholder="Opcional">
        </div>
        <div class="md:col-span-2">
            <button class="w-full px-3 py-2 bg-blue-600 text-white text-xs font-semibold rounded-md hover:bg-blue-700">+ Adelanto</button>
        </div>
    </form>

    {{-- Historial --}}
    <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-3 py-2 text-left">Fecha</th>
                    <th class="px-3 py-2 text-right">Monto</th>
                    <th class="px-3 py-2 text-left">Observaciones</th>
                    <th class="px-3 py-2 text-left">Estado</th>
                    <th class="px-3 py-2 text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($adelantos as $a)
                    @if($a->saldado())
                        <tr class="bg-gray-50 dark:bg-gray-800/40">
                            <td class="px-3 py-2 whitespace-nowrap">{{ $a->fecha->format('d/m/Y') }}</td>
                            <td class="px-3 py-2 text-right">$ {{ number_format($a->monto, 2) }}</td>
                            <td class="px-3 py-2">{{ $a->observaciones }}</td>
                            <td class="px-3 py-2"><span class="text-green-600 text-xs font-semibold">✓ Saldado</span></td>
                            <td class="px-3 py-2 text-right">
                                <a href="{{ route('rrhh.adelantos.vale', $a) }}" target="_blank" class="text-blue-600 text-xs">🖨 Vale</a>
                            </td>
                        </tr>
                    @else
                        <tr>
                            <form method="POST" action="{{ route('rrhh.adelantos.update', $a) }}" id="f-{{ $a->id }}">@csrf @method('PUT')</form>
                            <td class="px-3 py-2"><input form="f-{{ $a->id }}" type="date" name="fecha" value="{{ $a->fecha->format('Y-m-d') }}" class="{{ $inputCls }}"></td>
                            <td class="px-3 py-2"><input form="f-{{ $a->id }}" type="number" step="0.01" min="0.01" name="monto" value="{{ $a->monto }}" class="{{ $inputCls }} text-right" style="max-width:130px"></td>
                            <td class="px-3 py-2"><input form="f-{{ $a->id }}" type="text" name="observaciones" value="{{ $a->observaciones }}" class="{{ $inputCls }}"></td>
                            <td class="px-3 py-2"><span class="text-orange-600 text-xs font-semibold">Pendiente</span></td>
                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                <button form="f-{{ $a->id }}" class="text-blue-600 text-xs mr-2">Guardar</button>
                                <a href="{{ route('rrhh.adelantos.vale', $a) }}" target="_blank" class="text-gray-600 text-xs mr-2">🖨 Vale</a>
                                <form method="POST" action="{{ route('rrhh.adelantos.destroy', $a) }}" class="inline"
                                      onsubmit="return confirm('¿Eliminar este adelanto?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 text-xs">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="5" class="px-3 py-6 text-center text-gray-500">Sin adelantos cargados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        <a href="{{ route('rrhh.empleados.index') }}" class="text-sm text-gray-600 dark:text-gray-300">← Volver a empleados</a>
    </div>
</div>
@endsection

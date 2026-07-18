@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4">
    <h1 class="text-2xl font-bold mb-4">Fichadas</h1>

    @if(session('ok'))
        <div class="mb-3 p-3 rounded bg-green-100 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    {{-- Filtros --}}
    <form method="GET"
          action="{{ route('rrhh.fichadas.index') }}"
          class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Empleado
            </label>
            <select name="empleado_id"
                    class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                <option value="">Todos</option>
                @foreach($empleados as $e)
                    <option value="{{ $e->id }}"
                        @selected(request('empleado_id') == $e->id)
                    >
                        {{ $e->nombre_completo }} ({{ $e->codigo }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Desde
            </label>
            <input type="date"
                   name="desde"
                   value="{{ request('desde') }}"
                   class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Hasta
            </label>
            <input type="date"
                   name="hasta"
                   value="{{ request('hasta') }}"
                   class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
        </div>

        <div class="flex items-end gap-2">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:outline-none">
                Filtrar
            </button>

            <a href="{{ route('rrhh.fichadas.index') }}"
               class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                Limpiar
            </a>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="overflow-x-auto bg-white dark:bg-gray-900 shadow-sm rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Fecha</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Hora</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Empleado</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Tipo</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Foto</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Origen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($fichadas as $f)
                    <tr>
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ $f->momento->format('d/m/Y') }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ $f->momento->format('H:i') }}
                        </td>
                        <td class="px-3 py-2">
                          {{ $f->empleado?->nombre_completo ?? 'Empleado no encontrado' }}<br>
                            <span class="text-xs text-gray-500">({{ $f->empleado?->codigo ?? ('#' . $f->empleado_id) }}
)</span>
                        </td>
                        <td class="px-3 py-2 uppercase">
                            {{ $f->tipo }}
                        </td>
                        <td class="px-3 py-2">
                            @if($f->fotoUrl())
                                <a href="{{ $f->fotoUrl() }}" target="_blank" title="Ver foto de la fichada">
                                    <img src="{{ $f->fotoUrl() }}" alt="foto"
                                         style="width:44px;height:44px;object-fit:cover;border-radius:6px;border:1px solid #ccc">
                                </a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            {{ $f->origen ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-4 text-center text-gray-500">
                            No hay fichadas para los filtros seleccionados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $fichadas->withQueryString()->links() }}
    </div>
</div>
@endsection

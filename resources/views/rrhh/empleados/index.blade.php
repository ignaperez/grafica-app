@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4">
    <h1 class="text-2xl font-bold mb-4">Empleados</h1>

    @if(session('ok'))
        <div class="mb-3 p-3 rounded bg-green-100 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('rrhh.empleados.create') }}"
           class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-md hover:bg-blue-700">
            Nuevo empleado
        </a>
    </div>

    <div class="overflow-x-auto bg-white dark:bg-gray-900 shadow-sm rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-3 py-2 text-left">Nombre</th>
                    <th class="px-3 py-2 text-left">Código</th>
                    <th class="px-3 py-2 text-left">Activo</th>
                    <th class="px-3 py-2 text-left">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($empleados as $empleado)
                    <tr>
                        <td class="px-3 py-2">
                            {{ $empleado->nombre_completo }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $empleado->codigo }}
                        </td>
                        <td class="px-3 py-2">
                            @if($empleado->activo)
                                <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Sí</span>
                            @else
                                <span class="px-2 py-1 text-xs rounded bg-gray-200 text-gray-700">No</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 space-x-1">
                            <a href="{{ route('rrhh.empleados.fichadas', $empleado) }}"
                               class="text-xs text-green-700 hover:underline">
                                Ver horas
                            </a>
                            <a href="{{ route('rrhh.empleados.edit', $empleado) }}"
                               class="text-xs text-blue-700 hover:underline">
                                Editar
                            </a>
                            <form action="{{ route('rrhh.empleados.destroy', $empleado) }}"
                                  method="POST"
                                  class="inline"
                                  onsubmit="return confirm('¿Eliminar empleado?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="text-xs text-red-700 hover:underline">
                                    Borrar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                            No hay empleados cargados.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $empleados->links() }}
    </div>
</div>
@endsection

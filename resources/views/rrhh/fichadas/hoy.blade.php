@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4">
    <h1 class="text-2xl font-bold mb-4">
        Fichadas de hoy ({{ $hoy->format('d/m/Y') }})
    </h1>

    <div class="overflow-x-auto bg-white dark:bg-gray-900 shadow-sm rounded-lg">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Hora</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Empleado</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Tipo</th>
                    <th class="px-3 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Origen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($fichadas as $f)
                    <tr>
                        <td class="px-3 py-2 whitespace-nowrap">
                            {{ $f->momento->format('H:i') }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $f->empleado->nombre_completo }}
                            <span class="text-xs text-gray-500">({{ $f->empleado->codigo }})</span>
                        </td>
                        <td class="px-3 py-2 uppercase">
                            {{ $f->tipo }}
                        </td>
                        <td class="px-3 py-2">
                            {{ $f->origen ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                            No hay fichadas hoy.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

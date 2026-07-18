@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-4 max-w-4xl">
    <h1 class="text-2xl font-bold mb-4">Nuevo empleado</h1>

    @if($errors->any())
        <div class="mb-3 p-3 rounded bg-red-100 text-red-800 text-sm">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('rrhh.empleados.store') }}" method="POST" class="space-y-6">
        @csrf

        {{-- DATOS BÁSICOS --}}
        <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-4 space-y-4">
            <h2 class="text-lg font-semibold mb-2">Datos básicos</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Nombre
                    </label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Apellido
                    </label>
                    <input type="text" name="apellido" value="{{ old('apellido') }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Código (PIN / QR)
                    </label>
                    <input type="text" name="codigo" value="{{ old('codigo') }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm"
                           required>
                    <p class="text-xs text-gray-500 mt-1">
                        Este código se usa para fichar (PIN o QR).
                    </p>
                </div>

                <div class="flex items-center mt-6 gap-2">
                    <input type="checkbox" name="activo" id="activo"
                           class="rounded border-gray-300 text-blue-600"
                           {{ old('activo', true) ? 'checked' : '' }}>
                    <label for="activo" class="text-sm text-gray-700 dark:text-gray-300">
                        Activo
                    </label>
                </div>
            </div>
        </div>

        {{-- DATOS RRHH / LEGAJO --}}
        <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-4 space-y-4">
            <h2 class="text-lg font-semibold mb-2">Datos RRHH / Legajo</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        DNI
                    </label>
                    <input type="text" name="dni" value="{{ old('dni') }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        CUIL
                    </label>
                    <input type="text" name="cuil" value="{{ old('cuil') }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Categoría
                    </label>
                    <input type="text" name="categoria" value="{{ old('categoria') }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm"
                           placeholder="Operario, Administrativo, etc.">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Fecha de nacimiento
                    </label>
                    <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Fecha de ingreso
                    </label>
                    <input type="date" name="fecha_ingreso" value="{{ old('fecha_ingreso') }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Horas jornada (por día)
                    </label>
                    <input type="number" min="1" max="24" name="horas_jornada"
                           value="{{ old('horas_jornada', 8) }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Horario de ingreso
                    </label>
                    <input type="time" name="horario_ingreso" value="{{ old('horario_ingreso') }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Horario de egreso
                    </label>
                    <input type="time" name="horario_egreso" value="{{ old('horario_egreso') }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Valor hora ($)
                    </label>
                    <input type="number" step="0.01" name="valor_hora"
                           value="{{ old('valor_hora') }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Email
                    </label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Teléfono
                    </label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Dirección
                    </label>
                    <input type="text" name="direccion" value="{{ old('direccion') }}"
                           class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Observaciones
                </label>
                <textarea name="observaciones" rows="3"
                          class="w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm">{{ old('observaciones') }}</textarea>
            </div>
        </div>

        <div class="flex justify-between mt-4">
            <a href="{{ route('rrhh.empleados.index') }}"
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-xs text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">
                Cancelar
            </a>

            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700">
                Guardar
            </button>
        </div>
    </form>
</div>
@endsection

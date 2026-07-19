@extends('layouts.app')

@section('content')
@php $inputCls = 'w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm'; @endphp
<div class="container mx-auto px-4 py-4 max-w-2xl">
    <h1 class="text-2xl font-bold mb-1">Parámetros de sueldo</h1>
    <p class="text-sm text-gray-500 mb-4">
        Coeficientes que multiplican el valor hora según el tipo de día. En 1,00 = se paga el valor hora normal.
    </p>

    @if(session('ok'))<div class="mb-3 p-3 rounded bg-green-100 text-green-800 text-sm">{{ session('ok') }}</div>@endif
    @if($errors->any())<div class="mb-3 p-3 rounded bg-red-100 text-red-800 text-sm">{{ $errors->first() }}</div>@endif

    <form method="POST" action="{{ route('rrhh.sueldos.parametros.update') }}"
          class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-4 space-y-4">
        @csrf @method('PUT')

        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Coeficientes (× valor hora)</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium mb-1">Día normal (lun-vie)</label>
                <input type="number" step="0.01" min="0" name="sueldo_coef_normal"
                       value="{{ old('sueldo_coef_normal', $params['sueldo_coef_normal']) }}" class="{{ $inputCls }}" required>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Sábado</label>
                <input type="number" step="0.01" min="0" name="sueldo_coef_sabado"
                       value="{{ old('sueldo_coef_sabado', $params['sueldo_coef_sabado']) }}" class="{{ $inputCls }}" required>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Domingo / feriado</label>
                <input type="number" step="0.01" min="0" name="sueldo_coef_domingo"
                       value="{{ old('sueldo_coef_domingo', $params['sueldo_coef_domingo']) }}" class="{{ $inputCls }}" required>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Hora extra</label>
                <input type="number" step="0.01" min="0" name="sueldo_coef_extra"
                       value="{{ old('sueldo_coef_extra', $params['sueldo_coef_extra']) }}" class="{{ $inputCls }}" required>
            </div>
        </div>

        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300 pt-2">Otros</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium mb-1">Jornada del sábado (horas)</label>
                <input type="number" step="0.5" min="0" max="24" name="sueldo_jornada_sabado"
                       value="{{ old('sueldo_jornada_sabado', $params['sueldo_jornada_sabado']) }}" class="{{ $inputCls }}" required>
                <p class="text-xs text-gray-500 mt-1">Horas del sábado antes de contar como extra.</p>
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Tolerancia tardanza (min)</label>
                <input type="number" step="1" min="0" max="120" name="sueldo_tolerancia_min"
                       value="{{ old('sueldo_tolerancia_min', (int) $params['sueldo_tolerancia_min']) }}" class="{{ $inputCls }}" required>
                <p class="text-xs text-gray-500 mt-1">Minutos de gracia antes de marcar llegada tarde (solo informativo).</p>
            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button class="px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-md hover:bg-blue-700">Guardar</button>
        </div>
    </form>
</div>
@endsection

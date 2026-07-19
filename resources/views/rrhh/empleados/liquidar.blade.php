@extends('layouts.app')

@section('content')
@php
    $inputCls = 'w-full rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 text-sm';
    $fmt = fn($v) => number_format((float) $v, 2, ',', '.');

    // Filas del desglose: [etiqueta, horas, coef, monto]
    $filas = [
        ['Normales (lun-vie)', $calc['horasNormalSemana'], $coef['sueldo_coef_normal'], $vh * $calc['horasNormalSemana'] * $coef['sueldo_coef_normal']],
        ['Sábado',             $calc['horasSabado'],       $coef['sueldo_coef_sabado'], $vh * $calc['horasSabado']       * $coef['sueldo_coef_sabado']],
        ['Extra semana',       $calc['horasExtraSemana'],  $coef['sueldo_coef_extra'],  $vh * $calc['horasExtraSemana']  * $coef['sueldo_coef_extra']],
        ['Extra sábado',       $calc['horasExtraSabado'],  $coef['sueldo_coef_extra'],  $vh * $calc['horasExtraSabado']  * $coef['sueldo_coef_extra']],
        ['Domingo / feriado',  $calc['horasDomingo'],      $coef['sueldo_coef_domingo'],$vh * $calc['horasDomingo']      * $coef['sueldo_coef_domingo']],
    ];
@endphp

<div class="container mx-auto px-4 py-4 max-w-4xl">
    <div class="flex items-center justify-between mb-1">
        <h1 class="text-2xl font-bold">Liquidar – {{ $empleado->nombre_completo }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('rrhh.empleados.adelantos', $empleado) }}" class="px-3 py-2 border rounded-md text-xs">Adelantos</a>
            <a href="{{ route('rrhh.sueldos.parametros') }}" class="px-3 py-2 border rounded-md text-xs">Coeficientes</a>
        </div>
    </div>

    @if(session('ok'))<div class="mb-3 p-3 rounded bg-green-100 text-green-800 text-sm">{{ session('ok') }}</div>@endif
    @if($errors->any())<div class="mb-3 p-3 rounded bg-red-100 text-red-800 text-sm">{{ $errors->first() }}</div>@endif

    {{-- Período --}}
    <form method="GET" class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-3 mb-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium mb-1">Desde</label>
            <input type="date" name="desde" value="{{ $desde->format('Y-m-d') }}" class="{{ $inputCls }}">
        </div>
        <div>
            <label class="block text-xs font-medium mb-1">Hasta</label>
            <input type="date" name="hasta" value="{{ $hasta->format('Y-m-d') }}" class="{{ $inputCls }}">
        </div>
        <button class="px-3 py-2 bg-gray-700 text-white text-xs rounded-md">Ver período</button>
        <span class="text-xs text-gray-500 ml-2">Por defecto: semana actual (lun-sáb). Valor hora: <strong>$ {{ $fmt($vh) }}</strong></span>
    </form>

    {{-- Desglose de horas --}}
    <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg overflow-x-auto mb-4">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-3 py-2 text-left">Categoría</th>
                    <th class="px-3 py-2 text-right">Horas</th>
                    <th class="px-3 py-2 text-right">Coef.</th>
                    <th class="px-3 py-2 text-right">Monto</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach($filas as [$et, $hs, $cf, $mo])
                <tr class="{{ $hs > 0 ? '' : 'opacity-50' }}">
                    <td class="px-3 py-2">{{ $et }}</td>
                    <td class="px-3 py-2 text-right">{{ $fmt($hs) }}</td>
                    <td class="px-3 py-2 text-right">{{ rtrim(rtrim(number_format($cf,2,',','.'),'0'),',') }}×</td>
                    <td class="px-3 py-2 text-right">$ {{ $fmt($mo) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50 dark:bg-gray-800 font-semibold">
                <tr><td class="px-3 py-2" colspan="3">Bruto (normales)</td><td class="px-3 py-2 text-right">$ {{ $fmt($montoBruto) }}</td></tr>
                <tr><td class="px-3 py-2" colspan="3">Horas extras / especiales</td><td class="px-3 py-2 text-right text-orange-600">$ {{ $fmt($montoExtras) }}</td></tr>
            </tfoot>
        </table>
    </div>

    {{-- Tardanzas (informativo) --}}
    @if(($calc['tardanzasDias'] ?? 0) > 0)
    <div class="mb-4 p-3 rounded bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-sm text-amber-800 dark:text-amber-300">
        ⏰ Llegadas tarde en el período: <strong>{{ $calc['tardanzasDias'] }} día(s)</strong>,
        {{ $calc['tardanzasMin'] }} min en total. <span class="text-xs">(informativo — no se descuenta)</span>
    </div>
    @endif

    {{-- Adelantos del período --}}
    <div class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-4 mb-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-sm font-semibold">Adelantos a descontar</h2>
            <a href="{{ route('rrhh.empleados.adelantos', $empleado) }}" class="text-xs text-blue-600">Gestionar adelantos →</a>
        </div>
        @if($adelantos->count())
            <ul class="text-sm divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($adelantos as $a)
                <li class="py-1 flex justify-between">
                    <span>{{ $a->fecha->format('d/m/Y') }} · {{ $a->observaciones }}</span>
                    <span>$ {{ $fmt($a->monto) }}</span>
                </li>
                @endforeach
            </ul>
            <div class="text-right font-semibold text-red-600 mt-2">Total adelantos: − $ {{ $fmt($totalAdelantos) }}</div>
        @else
            <p class="text-sm text-gray-500">Sin adelantos pendientes en el período.</p>
        @endif
    </div>

    {{-- Registrar pago --}}
    <form action="{{ route('rrhh.empleados.pagos.store', $empleado) }}" method="POST"
          class="bg-white dark:bg-gray-900 shadow-sm rounded-lg p-4 space-y-4">
        @csrf
        <input type="hidden" name="desde" value="{{ $desde->format('Y-m-d') }}">
        <input type="hidden" name="hasta" value="{{ $hasta->format('Y-m-d') }}">
        <input type="hidden" name="horas_normales" value="{{ $calc['horasNormales'] }}">
        <input type="hidden" name="horas_extras" value="{{ $calc['horasExtras'] }}">
        <input type="hidden" name="valor_hora" value="{{ $vh }}">
        <input type="hidden" name="monto_bruto" id="f-bruto" value="{{ round($montoBruto,2) }}">
        <input type="hidden" name="monto_extras" id="f-extras" value="{{ round($montoExtras,2) }}">
        <input type="hidden" id="f-adelantos" value="{{ $totalAdelantos }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium mb-1">Bonificaciones ($)</label>
                <input type="number" step="0.01" min="0" name="bonificaciones" id="f-bonif"
                       value="{{ old('bonificaciones', 0) }}" class="{{ $inputCls }}">
            </div>
            <div>
                <label class="block text-xs font-medium mb-1">Descuentos ($)</label>
                <input type="number" step="0.01" min="0" name="descuentos" id="f-descu"
                       value="{{ old('descuentos', 0) }}" class="{{ $inputCls }}">
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium mb-1">Observaciones</label>
            <textarea name="observaciones" rows="2" class="{{ $inputCls }}">{{ old('observaciones') }}</textarea>
        </div>

        {{-- Resumen del neto --}}
        <div class="rounded-lg bg-gray-50 dark:bg-gray-800 p-4 text-sm space-y-1">
            <div class="flex justify-between"><span>Bruto (normales)</span><span>$ {{ $fmt($montoBruto) }}</span></div>
            <div class="flex justify-between"><span>Horas extras</span><span>$ {{ $fmt($montoExtras) }}</span></div>
            <div class="flex justify-between"><span>Bonificaciones</span><span id="r-bonif">$ 0,00</span></div>
            <div class="flex justify-between"><span>Descuentos</span><span id="r-descu">− $ 0,00</span></div>
            <div class="flex justify-between"><span>Adelantos</span><span>− $ {{ $fmt($totalAdelantos) }}</span></div>
            <div class="flex justify-between text-lg font-bold border-t border-gray-300 dark:border-gray-600 pt-2 mt-1">
                <span>NETO A PAGAR</span><span id="r-neto" class="text-green-700">$ {{ $fmt($netoSugerido) }}</span>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700">
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
                        <th class="px-3 py-2 text-left">Registrado</th>
                        <th class="px-3 py-2 text-left">Período</th>
                        <th class="px-3 py-2 text-right">Neto ($)</th>
                        <th class="px-3 py-2 text-left">Obs</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($ultimosPagos as $pago)
                    <tr>
                        <td class="px-3 py-2 whitespace-nowrap">{{ $pago->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 whitespace-nowrap">{{ optional($pago->desde)->format('d/m/Y') }} - {{ optional($pago->hasta)->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-right">$ {{ $fmt($pago->neto ?: $pago->monto_total) }}</td>
                        <td class="px-3 py-2">{{ \Illuminate\Support\Str::limit($pago->observaciones, 40) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

<script>
(function () {
    const bruto = {{ round($montoBruto, 2) }};
    const extras = {{ round($montoExtras, 2) }};
    const adel = {{ $totalAdelantos }};
    const fmt = n => '$ ' + n.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function recalc() {
        const bonif = parseFloat(document.getElementById('f-bonif').value) || 0;
        const descu = parseFloat(document.getElementById('f-descu').value) || 0;
        const neto = bruto + extras + bonif - descu - adel;
        document.getElementById('r-bonif').textContent = fmt(bonif);
        document.getElementById('r-descu').textContent = '− ' + fmt(descu);
        document.getElementById('r-neto').textContent = fmt(neto);
    }
    document.getElementById('f-bonif').addEventListener('input', recalc);
    document.getElementById('f-descu').addEventListener('input', recalc);
    recalc();
})();
</script>
@endsection

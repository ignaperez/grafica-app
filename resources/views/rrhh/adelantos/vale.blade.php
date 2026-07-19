<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Vale de adelanto – {{ $adelanto->empleado->nombre_completo }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; color: #111; font-size: 13px; background: #eee; }
        .bar { background: #1a1a1a; color: #ccc; padding: 10px 20px; display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
        .bar button { background: #16a34a; color: #fff; border: none; padding: 8px 20px; border-radius: 6px; cursor: pointer; font-weight: 700; }
        .vale { width: 720px; max-width: 96%; margin: 24px auto; background: #fff; border: 1px solid #000; padding: 26px 30px; }
        .hd { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 16px; }
        .hd .emp { font-size: 16px; font-weight: bold; }
        .hd .tit { text-align: right; }
        .hd .tit .t { font-size: 17px; font-weight: bold; letter-spacing: 1px; }
        .hd .tit .n { font-size: 12px; color: #444; margin-top: 3px; }
        .row { margin-bottom: 10px; line-height: 1.6; }
        .monto { font-size: 26px; font-weight: bold; margin: 14px 0; }
        .letras { font-style: italic; color: #333; }
        .obs { margin-top: 12px; border-top: 1px solid #ccc; padding-top: 8px; font-size: 12px; color: #333; }
        .firma { margin-top: 60px; display: flex; justify-content: space-around; text-align: center; font-size: 12px; }
        .firma .l { border-top: 1px solid #000; width: 240px; padding-top: 4px; }
        @media print { body { background: #fff; } .bar { display: none; } .vale { border: none; margin: 0; width: auto; } }
    </style>
</head>
<body>
@php
    $emp = \App\Models\Configuracion::empresa();
    $det = $adelanto->empleado->detalle;

    $uni = ['','Un','Dos','Tres','Cuatro','Cinco','Seis','Siete','Ocho','Nueve','Diez','Once','Doce','Trece','Catorce','Quince'];
    $enLetras = function (int $n) use (&$enLetras, $uni): string {
        if ($n <= 15)  return $uni[$n] ?: 'Cero';
        if ($n <= 19)  return 'Dieci' . mb_strtolower($enLetras($n - 10));
        if ($n === 20) return 'Veinte';
        if ($n <= 29)  return 'Veinti' . mb_strtolower($enLetras($n - 20));
        $dec = ['','','Treinta','Cuarenta','Cincuenta','Sesenta','Setenta','Ochenta','Noventa'];
        if ($n < 100)  return $dec[intdiv($n,10)] . ($n % 10 ? ' y ' . $enLetras($n % 10) : '');
        if ($n === 100) return 'Cien';
        $cen = ['','Ciento','Doscientos','Trescientos','Cuatrocientos','Quinientos','Seiscientos','Setecientos','Ochocientos','Novecientos'];
        if ($n < 1000)    return $cen[intdiv($n,100)] . ($n % 100 ? ' ' . $enLetras($n % 100) : '');
        if ($n < 2000)    return 'Mil' . ($n % 1000 ? ' ' . $enLetras($n % 1000) : '');
        if ($n < 1000000) return $enLetras(intdiv($n,1000)) . ' Mil' . ($n % 1000 ? ' ' . $enLetras($n % 1000) : '');
        if ($n < 2000000) return 'Un Millón' . ($n % 1000000 ? ' ' . $enLetras($n % 1000000) : '');
        return $enLetras(intdiv($n,1000000)) . ' Millones' . ($n % 1000000 ? ' ' . $enLetras($n % 1000000) : '');
    };
    $ent = (int) $adelanto->monto;
    $cts = (int) round(($adelanto->monto - $ent) * 100);
    $montoLetras = $enLetras($ent) . ' Pesos' . ($cts > 0 ? ' con ' . sprintf('%02d', $cts) . '/100' : '');
@endphp

<div class="bar">
    <a href="{{ route('rrhh.empleados.adelantos', $adelanto->empleado_id) }}" style="color:#aaa;text-decoration:none">← Volver</a>
    <button onclick="window.print()">🖨 Imprimir</button>
</div>

<div class="vale">
    <div class="hd">
        <div class="emp">{{ $emp['nombre_fantasia'] ?: $emp['nombre'] }}</div>
        <div class="tit">
            <div class="t">VALE DE ADELANTO</div>
            <div class="n">N° {{ str_pad($adelanto->id, 5, '0', STR_PAD_LEFT) }} · {{ $adelanto->fecha->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="row">
        Recibí de <strong>{{ $emp['nombre_fantasia'] ?: $emp['nombre'] }}</strong> la suma de:
    </div>
    <div class="monto">$ {{ number_format($adelanto->monto, 2, ',', '.') }}</div>
    <div class="letras">Son {{ $montoLetras }}.</div>

    <div class="row" style="margin-top:14px">
        En concepto de <strong>adelanto de sueldo</strong>, a descontar de mi próxima liquidación.
    </div>

    <div class="row">
        <strong>Empleado:</strong> {{ $adelanto->empleado->nombre_completo }}
        @if($det?->dni) &nbsp;·&nbsp; <strong>DNI:</strong> {{ $det->dni }} @endif
    </div>

    @if($adelanto->observaciones)
    <div class="obs"><strong>Observaciones:</strong> {{ $adelanto->observaciones }}</div>
    @endif

    <div class="firma">
        <div class="l">Firma del empleado</div>
        <div class="l">Firma / sello empresa</div>
    </div>
</div>
</body>
</html>

@extends('layouts.app')

@section('page-title', 'Trabajo #' . $trabajo->id)

@section('topbar-actions')
    <div style="display:flex;gap:8px">
        <a href="{{ route('trabajos.print', $trabajo->id) }}" target="_blank" class="gbtn gbtn-primary gbtn-sm">🖨 Imprimir</a>
        <a href="{{ route('trabajos.edit', $trabajo->id) }}" class="gbtn gbtn-ghost gbtn-sm">✎ Editar</a>
        <a href="{{ url()->previous() }}" class="gbtn gbtn-ghost gbtn-sm">← Volver</a>
    </div>
@endsection

@section('content')

@php
    $unidad = $trabajo->unidad ?? 'm2';
    $medida = match($unidad) {
        'm2'     => ($trabajo->ancho && $trabajo->alto) ? round($trabajo->ancho * $trabajo->alto * $trabajo->cantidad, 2) : null,
        'ml'     => $trabajo->largo ? round($trabajo->largo * $trabajo->cantidad, 2) : null,
        default  => null,
    };
    $estadoClase = ['pendiente'=>'be-pendiente','en_produccion'=>'be-en_produccion','terminado'=>'be-terminado'][$trabajo->estado] ?? 'be-pendiente';
@endphp

<div class="gcard" style="margin-bottom:16px">
    <div class="gcard-hd">
        <span class="gcard-title">Datos del trabajo</span>
        <span class="badge-estado {{ $estadoClase }}">{{ ucfirst(str_replace('_',' ',$trabajo->estado)) }}</span>
    </div>
    <div class="gcard-bd">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:16px">
            <div><div class="txd" style="font-size:10px;text-transform:uppercase;letter-spacing:1px">Cliente</div>
                 <div style="font-size:14px;color:var(--tx)">{{ $trabajo->cliente->nombre ?? $trabajo->orden->cliente->nombre ?? '—' }}</div></div>
            <div><div class="txd" style="font-size:10px;text-transform:uppercase;letter-spacing:1px">Orden</div>
                 <div style="font-size:14px">
                    @if($trabajo->orden_trabajo_id)
                        <a href="{{ route('ordenes-trabajo.show', $trabajo->orden_trabajo_id) }}" style="color:var(--ac);text-decoration:none" class="mono">OT #{{ str_pad($trabajo->orden_trabajo_id,4,'0',STR_PAD_LEFT) }}</a>
                    @else <span class="txd">Sin asignar</span> @endif
                 </div></div>
            <div><div class="txd" style="font-size:10px;text-transform:uppercase;letter-spacing:1px">Tipo de trabajo</div>
                 <div style="font-size:14px;color:var(--tx)">{{ $trabajo->tipoTrabajo->nombre ?? '—' }}</div></div>
            <div><div class="txd" style="font-size:10px;text-transform:uppercase;letter-spacing:1px">Material</div>
                 <div style="font-size:14px;color:var(--tx)">{{ $trabajo->material->nombre ?? '—' }}</div></div>
            <div><div class="txd" style="font-size:10px;text-transform:uppercase;letter-spacing:1px">Máquina</div>
                 <div style="font-size:14px;color:var(--tx)">{{ $trabajo->maquina->nombre ?? '—' }}</div></div>
            <div><div class="txd" style="font-size:10px;text-transform:uppercase;letter-spacing:1px">Unidad</div>
                 <div style="font-size:14px;color:var(--tx)" class="mono">{{ $unidad }}</div></div>
            @if($trabajo->ancho || $trabajo->alto)
            <div><div class="txd" style="font-size:10px;text-transform:uppercase;letter-spacing:1px">Medidas</div>
                 <div style="font-size:14px;color:var(--tx)" class="mono">{{ rtrim(rtrim((string)$trabajo->ancho,'0'),'.') }}m × {{ rtrim(rtrim((string)$trabajo->alto,'0'),'.') }}m</div></div>
            @endif
            @if($trabajo->largo)
            <div><div class="txd" style="font-size:10px;text-transform:uppercase;letter-spacing:1px">Largo</div>
                 <div style="font-size:14px;color:var(--tx)" class="mono">{{ rtrim(rtrim((string)$trabajo->largo,'0'),'.') }} m</div></div>
            @endif
            <div><div class="txd" style="font-size:10px;text-transform:uppercase;letter-spacing:1px">Cantidad</div>
                 <div style="font-size:14px;color:var(--tx)" class="mono">{{ $trabajo->cantidad }}</div></div>
            @if($medida !== null)
            <div><div class="txd" style="font-size:10px;text-transform:uppercase;letter-spacing:1px">{{ $unidad === 'ml' ? 'ml total' : 'm² total' }}</div>
                 <div style="font-size:14px;color:var(--ac);font-weight:600" class="mono">{{ number_format($medida,2) }} {{ $unidad === 'ml' ? 'ml' : 'm²' }}</div></div>
            @endif
            <div><div class="txd" style="font-size:10px;text-transform:uppercase;letter-spacing:1px">Fecha de entrega</div>
                 <div style="font-size:14px;color:var(--tx)">{{ $trabajo->fecha_entrega ? \Carbon\Carbon::parse($trabajo->fecha_entrega)->format('d/m/Y') : '—' }}</div></div>
        </div>

        @if($trabajo->descripcion)
        <div style="margin-top:16px;padding:12px 14px;border-left:3px solid var(--ac);background:var(--bg-h);border-radius:6px">
            <div class="txd" style="font-size:10px;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px">Descripción / Observaciones</div>
            <div style="color:var(--tx);font-size:13.5px;line-height:1.5">{{ $trabajo->descripcion }}</div>
        </div>
        @endif
    </div>
</div>

{{-- Referencias --}}
@if($trabajo->referencias->isNotEmpty())
<div class="gcard" style="margin-bottom:16px">
    <div class="gcard-hd"><span class="gcard-title">Referencias ({{ $trabajo->referencias->count() }})</span></div>
    <div class="gcard-bd">
        <div style="display:flex;flex-wrap:wrap;gap:12px">
            @foreach($trabajo->referencias as $ref)
                @php
                    $ext = strtolower(pathinfo($ref->nombre_original, PATHINFO_EXTENSION));
                    $esImagen = in_array($ext, ['jpg','jpeg','png','gif','bmp','webp','tif','tiff']);
                @endphp
                <a href="{{ $ref->url }}" target="_blank" title="{{ $ref->nombre_original }}"
                   style="text-decoration:none;width:140px">
                    @if($esImagen)
                        <img src="{{ $ref->url }}" alt="{{ $ref->nombre_original }}"
                             style="width:140px;height:140px;object-fit:cover;border-radius:8px;border:1px solid var(--bm);display:block">
                    @else
                        <div style="width:140px;height:140px;border-radius:8px;border:1px solid var(--bm);background:#0a0a0a;display:flex;align-items:center;justify-content:center;font-family:var(--mono);font-weight:700;color:var(--txd);font-size:15px">{{ strtoupper($ext) ?: 'ARCH' }}</div>
                    @endif
                    <div class="txd" style="font-size:10px;margin-top:5px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $ref->nombre_original }}</div>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endif

{{-- Archivos para imprimir --}}
@if($trabajo->archivosImprimir->isNotEmpty())
<div class="gcard" style="margin-bottom:16px">
    <div class="gcard-hd"><span class="gcard-title">Archivos para imprimir ({{ $trabajo->archivosImprimir->count() }})</span></div>
    <div class="gcard-bd" style="padding:0">
        <table class="gtable">
            <tbody>
                @foreach($trabajo->archivosImprimir as $arch)
                <tr>
                    <td style="width:40px" class="txd mono">{{ strtoupper(pathinfo($arch->nombre_original, PATHINFO_EXTENSION)) }}</td>
                    <td style="color:var(--tx)">{{ $arch->nombre_original }}</td>
                    <td class="txd" style="width:90px">{{ $arch->tamanioFormateado }}</td>
                    <td style="width:90px;text-align:right"><a href="{{ $arch->url }}" target="_blank" class="gbtn gbtn-ghost gbtn-xs">Abrir</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

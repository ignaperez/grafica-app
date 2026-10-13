@extends('super-admin.layout')

@section('title', 'Consultas web')

@section('content')

<div class="sa-hd">
    <div>
        <h1 class="sa-card-title" style="margin:0">Consultas del formulario</h1>
        <div style="font-size:.78rem;color:var(--txm);margin-top:4px">
            Respaldo de lo que llega por mail a
            {{ implode(' y ', (array) config('contacto.emails')) }}
        </div>
    </div>
</div>

{{-- Filtros --}}
@php
    $tabs = [
        'todas'     => 'Todas',
        'sin_leer'  => 'Sin leer',
        'con_error' => 'Mail no enviado',
    ];
@endphp
<div style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap">
    @foreach($tabs as $key => $label)
        @php $activa = $filtro === $key; @endphp
        <a href="{{ route('super-admin.consultas.index', ['filtro' => $key]) }}"
           style="font-size:.78rem;text-decoration:none;padding:6px 12px;border-radius:6px;
                  border:1px solid {{ $activa ? 'var(--ac)' : '#2a2a2a' }};
                  color:{{ $activa ? 'var(--ac)' : 'var(--txm)' }}">
            {{ $label }}
            <strong style="margin-left:4px">{{ $totales[$key] }}</strong>
        </a>
    @endforeach
</div>

@if($totales['con_error'] > 0 && $filtro !== 'con_error')
    <div class="alert-err" style="margin-bottom:14px">
        Hay {{ $totales['con_error'] }}
        {{ $totales['con_error'] === 1 ? 'consulta cuyo mail no se pudo enviar' : 'consultas cuyos mails no se pudieron enviar' }}.
        Los datos están guardados igual.
        <a href="{{ route('super-admin.consultas.index', ['filtro' => 'con_error']) }}" style="color:inherit;text-decoration:underline">Verlas</a>
    </div>
@endif

<div class="sa-card">
    <div class="sa-card-hd">
        <span class="sa-card-title">{{ $consultas->total() }} {{ $consultas->total() === 1 ? 'consulta' : 'consultas' }}</span>
    </div>

    @if($consultas->isEmpty())
        <div class="sa-card-bd" style="color:var(--txm);font-size:.85rem">
            @if($filtro === 'todas')
                Todavía no llegó ninguna consulta por el formulario.
            @else
                Ninguna consulta en este filtro.
                <a href="{{ route('super-admin.consultas.index') }}" style="color:var(--ac)">Ver todas</a>
            @endif
        </div>
    @else
    <table class="sa-table">
        <thead>
            <tr>
                <th style="width:92px">Fecha</th>
                <th>Quién</th>
                <th style="width:140px">Servicio</th>
                <th>Mensaje</th>
                <th style="width:90px">Mail</th>
                <th style="width:150px"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($consultas as $c)
            <tr style="{{ $c->leida() ? '' : 'background:rgba(230,80,42,.04)' }}">
                <td style="font-size:.74rem;color:var(--txm);white-space:nowrap">
                    {{ $c->created_at->format('d/m/Y') }}<br>
                    {{ $c->created_at->format('H:i') }} hs
                </td>
                <td style="font-size:.8rem">
                    <div style="font-weight:600">
                        {{ $c->nombre }}
                        @unless($c->leida())
                            <span style="color:var(--ac);font-size:.68rem;margin-left:4px">● nueva</span>
                        @endunless
                    </div>
                    <div><a href="mailto:{{ $c->email }}" style="color:var(--ac);font-size:.76rem">{{ $c->email }}</a></div>
                    @if($c->telefono)
                        @php $wa = $c->whatsappUrl(); @endphp
                        <div style="font-size:.76rem;color:var(--txm)">
                            @if($wa)
                                <a href="{{ $wa }}" target="_blank" rel="noopener" style="color:var(--txm)">{{ $c->telefono }} ↗</a>
                            @else
                                {{ $c->telefono }}
                            @endif
                        </div>
                    @endif
                </td>
                <td style="font-size:.78rem;color:var(--txm)">{{ $c->servicio ?: '—' }}</td>
                <td style="font-size:.78rem;max-width:380px;white-space:pre-line">{{ \Illuminate\Support\Str::limit($c->mensaje, 260) }}</td>
                <td style="font-size:.72rem">
                    @if($c->mail_enviado)
                        <span style="color:#3fb96a">✓ enviado</span>
                    @else
                        <span style="color:#e6502a" title="{{ $c->mail_error }}">✕ falló</span>
                    @endif
                </td>
                <td style="text-align:right">
                    <div style="display:flex;gap:5px;justify-content:flex-end;flex-wrap:wrap">
                        <form method="POST" action="{{ route('super-admin.consultas.leida', $c->id) }}">
                            @csrf
                            <button type="submit" style="font-size:.7rem;background:none;border:1px solid #2a2a2a;color:var(--txm);padding:4px 8px;border-radius:5px;cursor:pointer">
                                {{ $c->leida() ? '↩ No leída' : '✓ Leída' }}
                            </button>
                        </form>
                        @unless($c->mail_enviado)
                        <form method="POST" action="{{ route('super-admin.consultas.reenviar', $c->id) }}">
                            @csrf
                            <button type="submit" style="font-size:.7rem;background:none;border:1px solid var(--ac);color:var(--ac);padding:4px 8px;border-radius:5px;cursor:pointer">
                                ↻ Reenviar
                            </button>
                        </form>
                        @endunless
                        <form method="POST" action="{{ route('super-admin.consultas.destroy', $c->id) }}"
                              onsubmit="return confirm('¿Eliminar la consulta de {{ addslashes($c->nombre) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" style="font-size:.7rem;background:none;border:1px solid #3a2020;color:#a05050;padding:4px 8px;border-radius:5px;cursor:pointer">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @if($consultas->hasPages())
        <div class="sa-card-bd">{{ $consultas->links() }}</div>
    @endif
    @endif
</div>

@endsection

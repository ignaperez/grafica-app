@extends('layouts.app')

@section('page-title', 'Usuarios')

@section('topbar-actions')
    <a href="{{ route('usuarios.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nuevo usuario</a>
@endsection

@section('content')
<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">Usuarios del sistema</span>
        <span class="txd" style="font-size:11px">{{ $usuarios->count() }} usuarios</span>
    </div>
    <table class="gtable">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Rol</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($usuarios as $u)
            @php
                $rolColor = match($u->rol) {
                    'admin'      => ['color'=>'var(--ac)',    'bg'=>'rgba(230,80,42,.08)',  'border'=>'rgba(230,80,42,.2)'],
                    'ventas'     => ['color'=>'var(--blue)',  'bg'=>'rgba(61,143,212,.08)', 'border'=>'rgba(61,143,212,.2)'],
                    'produccion' => ['color'=>'var(--green)', 'bg'=>'rgba(63,185,106,.08)','border'=>'rgba(63,185,106,.2)'],
                    default      => ['color'=>'var(--txd)',   'bg'=>'transparent',          'border'=>'#333'],
                };
            @endphp
            <tr>
                <td class="mono txd" style="font-size:11px">{{ $u->id }}</td>
                <td>
                    <div style="font-weight:500;color:var(--tx);display:flex;align-items:center;gap:8px">
                        <span style="width:28px;height:28px;border-radius:50%;background:#1a0e09;
                                     border:1px solid #2a1810;display:inline-flex;align-items:center;
                                     justify-content:center;font-size:11px;font-weight:700;color:var(--ac);
                                     flex-shrink:0">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </span>
                        {{ $u->name }}
                        @if($u->id === auth()->id())
                            <span style="font-size:10px;color:var(--txd)">(vos)</span>
                        @endif
                    </div>
                </td>
                <td class="txd">{{ $u->email }}</td>
                <td>
                    <span style="display:inline-flex;align-items:center;gap:5px;padding:3px 10px;
                                 border-radius:20px;font-size:10.5px;font-weight:700;letter-spacing:.3px;
                                 color:{{ $rolColor['color'] }};background:{{ $rolColor['bg'] }};
                                 border:1px solid {{ $rolColor['border'] }}">
                        {{ ucfirst($u->rol) }}
                    </span>
                    @if($u->esSuper())
                        <span title="Administrador principal — acceso total"
                              style="margin-left:6px;font-size:10.5px;font-weight:700;color:var(--ac)">★ Principal</span>
                    @else
                        <div class="txd" style="font-size:10.5px;margin-top:4px">
                            {{ count($u->modulos ?? []) }} de {{ count(\App\Models\User::MODULOS) }} módulos
                        </div>
                    @endif
                </td>
                <td style="text-align:right">
                    <div style="display:flex;gap:6px;justify-content:flex-end">
                        <a href="{{ route('usuarios.edit', $u->id) }}" class="gbtn gbtn-ghost gbtn-xs">✎ Editar</a>
                        <form method="POST" action="{{ route('usuarios.cerrar-sesiones', $u->id) }}"
                              onsubmit="return confirm('¿Cerrar las sesiones activas de {{ addslashes($u->name) }}?')">
                            @csrf
                            <button type="submit" class="gbtn gbtn-ghost gbtn-xs" title="Forzar cierre de sesión en todas las PCs">⎋ Cerrar sesión</button>
                        </form>
                        @if($u->id !== auth()->id() && !$u->esSuper())
                        <form method="POST" action="{{ route('usuarios.destroy', $u->id) }}"
                              onsubmit="return confirm('¿Eliminar a {{ addslashes($u->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="gbtn gbtn-danger gbtn-xs">Eliminar</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection

@extends('layouts.app')

@section('page-title', 'Clientes')

@section('topbar-actions')
    <a href="{{ route('clientes.create') }}" class="gbtn gbtn-primary gbtn-sm">+ Nuevo cliente</a>
@endsection

@section('content')

<div class="gcard">
    <div class="gcard-hd">
        <span class="gcard-title">{{ $clientes->count() }} {{ $clientes->count() === 1 ? 'cliente' : 'clientes' }}</span>
        <input type="text" id="buscador" class="ginput" placeholder="Filtrar por nombre o CUIT…"
               style="width:240px;padding:5px 10px;font-size:12px;">
    </div>
    <div class="gcard-bd" style="padding:0;">
        @if($clientes->isEmpty())
            <div style="padding:48px;text-align:center;color:var(--txd);">
                No hay clientes registrados.
                <a href="{{ route('clientes.create') }}" style="color:var(--ac);">Crear el primero →</a>
            </div>
        @else
        <table class="gtable" id="tabla-clientes">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>CUIT</th>
                    <th>Condición IVA</th>
                    <th>Teléfono</th>
                    <th>Lista de precios</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @foreach($clientes as $cliente)
            <tr data-search="{{ strtolower($cliente->nombre . ' ' . $cliente->cuit) }}">
                <td>
                    <a href="{{ route('clientes.show', $cliente->id) }}"
                       style="color:var(--tx);font-weight:600;text-decoration:none;">
                        {{ $cliente->nombre }}
                    </a>
                    @if($cliente->email)
                        <div style="font-size:11px;color:var(--txd);margin-top:2px;">{{ $cliente->email }}</div>
                    @endif
                </td>
                <td class="mono" style="font-size:12px;">
                    {{ $cliente->cuit ? $cliente->cuit : '<span class="txd">—</span>' }}
                </td>
                <td>
                    @if($cliente->condicion_iva)
                        <span style="font-size:11.5px;color:var(--txm);">{{ $cliente->condicionIvaLabel() }}</span>
                    @else
                        <span class="txd">—</span>
                    @endif
                </td>
                <td style="color:var(--txm);font-size:12px;">{{ $cliente->telefono ?: '—' }}</td>
                <td style="font-size:12px;color:var(--txm);">{{ $cliente->listaPrecio->nombre ?? '—' }}</td>
                <td style="text-align:right;white-space:nowrap;">
                    <a href="{{ route('clientes.edit', $cliente->id) }}" class="gbtn gbtn-ghost gbtn-xs">Editar</a>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
$('#buscador').on('input', function () {
    const q = $(this).val().toLowerCase();
    $('#tabla-clientes tbody tr').each(function () {
        const hay = $(this).data('search').includes(q);
        $(this).toggle(hay);
    });
});
</script>
@endsection

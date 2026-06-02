@extends('super-admin.layout')

@section('title', 'Empresas')

@section('content')

<div class="sa-hd">
    <div>
        <h1>Empresas</h1>
        <div class="sub">{{ $empresas->count() }} empresa{{ $empresas->count() !== 1 ? 's' : '' }} registrada{{ $empresas->count() !== 1 ? 's' : '' }}</div>
    </div>
    <div class="sa-hd-actions">
        <a href="{{ route('super-admin.empresas.create') }}" class="btn btn-primary">+ Nueva empresa</a>
    </div>
</div>

<div class="sa-card">
    <table class="sa-table">
        <thead>
            <tr>
                <th>Empresa</th>
                <th>Slug / BD</th>
                <th>CUIT</th>
                <th>ARCA</th>
                <th>Estado</th>
                <th>Creada</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($empresas as $t)
        <tr>
            <td>
                <strong>{{ $t->nombre }}</strong>
                @if($t->email)<br><span style="color:#888;font-size:.8rem">{{ $t->email }}</span>@endif
            </td>
            <td>
                <span class="mono">{{ $t->id }}</span><br>
                <span class="mono" style="color:#555;font-size:.78rem">tenant_{{ $t->id }}</span>
            </td>
            <td class="mono">{{ $t->cuit ?? '—' }}</td>
            <td>
                @if($t->has_arca_cert)
                    <span class="badge badge-ok">✓ Cert</span>
                @else
                    <span class="badge badge-warn">Sin cert</span>
                @endif
                @if($t->arca_punto_venta)
                    <span class="mono" style="font-size:.75rem;color:#888;display:block;margin-top:2px">PV {{ $t->arca_punto_venta }}</span>
                @endif
            </td>
            <td>
                @if($t->trashed())
                    <span class="badge badge-del">Inactiva</span>
                @else
                    <span class="badge badge-ok">Activa</span>
                @endif
            </td>
            <td style="color:#888;font-size:.8rem">{{ $t->created_at->format('d/m/Y') }}</td>
            <td style="text-align:right">
                <a href="{{ route('super-admin.empresas.show', $t->id) }}" class="btn btn-ghost btn-sm">Ver</a>
                @if(!$t->trashed())
                <form method="POST" action="{{ route('super-admin.empresas.impersonar', $t->id) }}" style="display:inline">
                    @csrf
                    <button class="btn btn-ghost btn-sm" type="submit">↗ Panel</button>
                </form>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="text-align:center;color:#888;padding:40px">
                No hay empresas aún. <a href="{{ route('super-admin.empresas.create') }}" style="color:var(--ac)">Crear la primera →</a>
            </td>
        </tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection

{{-- Cuerpo: cliente (solo primera hoja, porque fluye) + tabla de ítems.
     mPDF pagina sola y repite el <thead> en cada hoja nueva. --}}

<div class="label">Cliente</div>
<div class="who-name">{{ $cli->nombre ?? '—' }}</div>
<div class="who-row">
    @if($cli?->cuit)CUIT {{ $cli->cuit }}<br>@endif
    @if($cliIva){{ $cliIva }}<br>@endif
    @if($cli?->direccion){{ $cli->direccion }}<br>@endif
    @if($cli?->email){{ $cli->email }}<br>@endif
    @if($cli?->telefono)Tel. {{ $cli->telefono }}@endif
</div>

<table class="items">
    <thead>
        <tr>
            <th class="c" style="width:8mm">#</th>
            <th class="l">Descripción</th>
            <th class="c" style="width:16mm">Unidad</th>
            <th class="r" style="width:20mm">Medida</th>
            <th class="r" style="width:24mm">P. Unit.</th>
            <th class="r" style="width:26mm">Subtotal</th>
        </tr>
    </thead>
    <tbody>
        @foreach($presupuesto->items as $i => $item)
        <tr>
            <td class="idx c">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
            <td>
                <div class="desc-main">{{ $item->descripcion }}</div>
                <div class="desc-sub">
                    @if($item->unidad === 'm2')
                        {{ number_format($item->ancho, 2) }} × {{ number_format($item->alto, 2) }} m × {{ $item->cantidad }} u
                    @elseif($item->unidad === 'ml')
                        {{ number_format($item->largo, 2) }} ml × {{ $item->cantidad }} u
                    @else
                        {{ $item->cantidad }} unidad(es)
                    @endif
                </div>
            </td>
            <td class="u">{{ $item->unidadLabel() }}</td>
            <td class="num">{{ number_format($item->medidaTotal(), 3, ',', '.') }}</td>
            <td class="num">${{ number_format($item->precio_unitario, 2, ',', '.') }}</td>
            <td class="num s">${{ number_format($item->subtotal, 2, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

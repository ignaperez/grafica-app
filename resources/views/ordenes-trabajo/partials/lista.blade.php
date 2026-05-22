@if($trabajos->isEmpty())
    <p class="text-muted">No hay trabajos cargados aún.</p>
@else
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Medidas</th>
                <th>Cantidad</th>
                <th>Consumo</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trabajos as $t)
                <tr>
                    <td>{{ $t->producto->nombre ?? '-' }} ({{ $t->producto->tipo ?? '' }})</td>
                    <td>{{ $t->ancho }} x {{ $t->alto }} cm</td>
                    <td>{{ $t->cantidad }}</td>
                    <td>{{ number_format($t->consumo, 2) }} m²</td>
                    <td>{{ $t->observaciones }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

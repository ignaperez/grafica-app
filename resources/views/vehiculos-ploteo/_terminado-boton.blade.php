{{--
    Marcar / reabrir un vehículo. Espera $v (el VehiculoPloteo).
    Lo ve tanto el colocador como producción: cualquiera de los dos puede cerrarlo.
--}}
<form method="POST" action="{{ route('vehiculos-ploteo.terminado', $v->id) }}" style="display:inline">
    @csrf
    @if($v->terminado())
        <input type="hidden" name="reabrir" value="1">
        <button type="submit" class="gbtn gbtn-ghost {{ $chico ?? false ? 'gbtn-xs' : 'gbtn-sm' }}"
                title="Vuelve a figurar como pendiente"
                onclick="return confirm('¿Reabrir este vehículo? Vuelve a figurar como pendiente.')">
            ↩ Reabrir
        </button>
    @else
        <button type="submit" class="gbtn gbtn-primary {{ $chico ?? false ? 'gbtn-xs' : 'gbtn-sm' }}"
                title="Marcar el ploteo como terminado"
                onclick="return confirm('¿Marcar este vehículo como terminado?')">
            ✓ Terminado
        </button>
    @endif
</form>

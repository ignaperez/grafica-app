<select name="{{ $name }}" class="gselect gselect-sm" style="width:100%">
    @foreach(['unidades','m²','ml','kg','hojas','pliegos','rollos','metros'] as $u)
        <option value="{{ $u }}" {{ ($selected ?? 'unidades') === $u ? 'selected' : '' }}>{{ $u }}</option>
    @endforeach
</select>

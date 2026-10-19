@php
    use App\Models\User;
    use App\Support\Permisos;

    // $seleccionados = ['ordenes' => ['ver','crear'], ...]
    $seleccionados = $seleccionados ?? [];
@endphp

<div class="gcard" style="margin-top:12px">
    <div class="gcard-hd">
        <span class="gcard-title">Permisos</span>
        <span class="txd" style="font-size:11px">Qué puede hacer en cada sección</span>
    </div>
    <div class="gcard-bd">
        {{-- marca que el form envió la sección (aunque no se tilde nada) --}}
        <input type="hidden" name="modulos_marcado" value="1">

        <div class="txd" style="font-size:11.5px;margin-bottom:12px;line-height:1.5">
            El rol es solo una plantilla que propone estos permisos al crearlo: acá podés dar o
            quitar lo que quieras, sin importar el rol. <strong>Ver</strong> habilita el acceso a
            la sección; sin eso, no la ve en el menú.
        </div>

        <table class="gtable perm-tabla">
            <thead>
                <tr>
                    <th style="text-align:left">Sección</th>
                    @foreach(Permisos::ACCIONES as $key => $label)
                        <th style="width:72px;text-align:center">{{ $label }}</th>
                    @endforeach
                    <th style="width:82px;text-align:center">Todo</th>
                </tr>
            </thead>
            <tbody>
                @foreach(User::MODULOS as $modKey => $modLabel)
                    @php $acciones = (array) ($seleccionados[$modKey] ?? []); @endphp
                    <tr data-modulo="{{ $modKey }}">
                        <td style="font-size:12.5px;color:var(--tx)">{{ $modLabel }}</td>

                        @foreach(array_keys(Permisos::ACCIONES) as $accKey)
                            <td style="text-align:center">
                                <input type="checkbox"
                                       class="perm-chk"
                                       data-accion="{{ $accKey }}"
                                       name="permisos[{{ $modKey }}][]"
                                       value="{{ $accKey }}"
                                       {{ in_array($accKey, $acciones, true) ? 'checked' : '' }}>
                            </td>
                        @endforeach

                        <td style="text-align:center">
                            <input type="checkbox" class="perm-todo" title="Marcar o desmarcar toda la fila">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap">
            <button type="button" class="gbtn gbtn-ghost gbtn-xs" onclick="permTodos(true)">Marcar todo</button>
            <button type="button" class="gbtn gbtn-ghost gbtn-xs" onclick="permTodos(false)">Desmarcar todo</button>
            <button type="button" class="gbtn gbtn-ghost gbtn-xs" onclick="permSoloVer()">Solo lectura</button>
        </div>
    </div>
</div>

<style>
    .perm-tabla td, .perm-tabla th { padding:7px 8px; }
    .perm-tabla tbody tr:hover td { background:var(--bg-h); }
    .perm-tabla input[type="checkbox"] { cursor:pointer; }
    /* Una fila sin "Ver" no se usa: la sección no aparece en el menú */
    .perm-tabla tr.sin-ver td { opacity:.5; }
</style>

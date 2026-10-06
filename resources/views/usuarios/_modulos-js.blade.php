@php
    // $pretildar = true en el alta: al elegir el rol se tildan sus módulos default.
    $pretildar      = $pretildar ?? false;
    $rolesPorModulo = \App\Models\User::MODULO_ROLES;
    $notasModulo    = \App\Models\User::MODULO_NOTAS;
    $defaultsPorRol = collect(array_keys(\App\Models\User::ROLES))
        ->mapWithKeys(fn ($r) => [$r => \App\Models\User::modulosPorRol($r)])
        ->all();
    $labelRoles = collect(array_keys(\App\Models\User::MODULOS))
        ->mapWithKeys(fn ($k) => [$k => \App\Models\User::rolesDeModuloLabel($k)])
        ->all();
@endphp
<script>
// Los módulos que el rol elegido no habilita se atenúan y se deshabilitan: antes
// se podían tildar los 11 para cualquier rol y no hacían nada (las rutas los
// cortan por rol). Espeja App\Models\User::MODULO_ROLES.
(function () {
    const ROLES_POR_MODULO = @json($rolesPorModulo);
    const NOTAS            = @json($notasModulo);
    const LABEL_ROLES      = @json($labelRoles);
    const DEFAULTS         = @json($defaultsPorRol);
    const PRETILDAR        = @json($pretildar);

    const $rol  = document.querySelector('select[name="rol"]');
    const $grid = document.getElementById('modulos-grid');
    if (!$rol || !$grid) return;

    function pintar(rol) {
        $grid.querySelectorAll('.mod-item').forEach(function (item) {
            const mod    = item.dataset.modulo;
            const chk    = item.querySelector('input[type="checkbox"]');
            const nota   = item.querySelector('.mod-nota');
            const aplica = rol === '' || (ROLES_POR_MODULO[mod] || []).includes(rol);

            item.classList.toggle('mod-na', !aplica);
            chk.disabled = !aplica;
            item.title   = aplica ? '' : 'Disponible solo para: ' + LABEL_ROLES[mod];

            // Un checkbox deshabilitado no se envía: si el módulo está tildado se
            // preserva en un hidden para no perderlo al guardar.
            let hidden = $grid.querySelector('input[data-preserva="' + mod + '"]');
            if (!aplica && chk.checked) {
                if (!hidden) {
                    hidden = document.createElement('input');
                    hidden.type = 'hidden';
                    hidden.name = 'modulos[]';
                    hidden.value = mod;
                    hidden.dataset.preserva = mod;
                    item.insertAdjacentElement('afterend', hidden);
                }
            } else if (hidden) {
                hidden.remove();
            }

            if (nota) {
                nota.textContent = !aplica
                    ? 'solo ' + LABEL_ROLES[mod]
                    : ((NOTAS[mod] || {})[rol] || '');
            }
        });
    }

    $rol.addEventListener('change', function () {
        if (PRETILDAR) {
            const mods = DEFAULTS[this.value] || [];
            $grid.querySelectorAll('input[type="checkbox"]').forEach(function (chk) {
                chk.checked = mods.includes(chk.value);
            });
        }
        pintar(this.value);
    });

    pintar($rol.value || '');
})();
</script>

@php
    // $pretildar = true en el alta: al elegir el rol se proponen sus permisos.
    $pretildar      = $pretildar ?? false;
    $defaultsPorRol = collect(array_keys(\App\Models\User::ROLES))
        ->mapWithKeys(fn ($r) => [$r => \App\Models\User::modulosPorRol($r)])
        ->all();
@endphp
<script>
// Matriz de permisos: módulo × acción.
//
// El rol ya NO limita nada — es solo una plantilla que propone permisos al
// elegirlo en el alta. Antes los checkboxes de los módulos que el rol no
// habilitaba salían atenuados y no servían para nada; ahora todos se pueden
// marcar y todos tienen efecto.
(function () {
    const DEFAULTS  = @json($defaultsPorRol);
    const PRETILDAR = @json($pretildar);

    const $rol  = document.querySelector('select[name="rol"]');
    const filas = () => Array.from(document.querySelectorAll('.perm-tabla tbody tr'));

    function sincronizarFila(tr) {
        const chks  = Array.from(tr.querySelectorAll('.perm-chk'));
        const todo  = tr.querySelector('.perm-todo');
        const ver   = tr.querySelector('.perm-chk[data-accion="ver"]');
        const todos = chks.every(c => c.checked);

        if (todo) todo.checked = todos;

        // Sin "Ver" la sección no aparece en el menú: se marca la fila como
        // inactiva para que se note de un vistazo.
        tr.classList.toggle('sin-ver', !!ver && !ver.checked && chks.some(c => c.checked));
    }

    function aplicar(permisos) {
        filas().forEach(tr => {
            const mod = tr.dataset.modulo;
            const dar = (permisos && permisos[mod]) || [];
            tr.querySelectorAll('.perm-chk').forEach(c => {
                c.checked = dar.includes(c.dataset.accion);
            });
            sincronizarFila(tr);
        });
    }

    window.permTodos = function (valor) {
        document.querySelectorAll('.perm-chk').forEach(c => { c.checked = valor; });
        filas().forEach(sincronizarFila);
    };

    window.permSoloVer = function () {
        filas().forEach(tr => {
            tr.querySelectorAll('.perm-chk').forEach(c => {
                c.checked = c.dataset.accion === 'ver';
            });
            sincronizarFila(tr);
        });
    };

    // "Todo" de la fila
    document.querySelectorAll('.perm-todo').forEach(todo => {
        todo.addEventListener('change', function () {
            const tr = this.closest('tr');
            tr.querySelectorAll('.perm-chk').forEach(c => { c.checked = todo.checked; });
            sincronizarFila(tr);
        });
    });

    // Marcar cualquier acción implica poder ver: sin "Ver" no llega a la pantalla
    document.querySelectorAll('.perm-chk').forEach(chk => {
        chk.addEventListener('change', function () {
            const tr = this.closest('tr');
            if (this.checked && this.dataset.accion !== 'ver') {
                const ver = tr.querySelector('.perm-chk[data-accion="ver"]');
                if (ver) ver.checked = true;
            }
            sincronizarFila(tr);
        });
    });

    if ($rol && PRETILDAR) {
        $rol.addEventListener('change', function () {
            aplicar(DEFAULTS[this.value] || {});
        });
    }

    filas().forEach(sincronizarFila);
})();
</script>

{{--
    Modal de alta rápida de cliente, reutilizable desde cualquier form que
    tenga un <select id="sel-cliente"> (Select2). Incluir dentro del form/vista
    y agregar un botón: <button type="button" onclick="abrirNuevoCliente()">+ Nuevo</button>.
    Al crear, agrega el cliente al select y lo deja seleccionado.
--}}
<div id="qc-overlay" style="display:none;position:fixed;inset:0;z-index:1050;
     background:rgba(0,0,0,.6);align-items:flex-start;justify-content:center;padding:48px 16px;overflow:auto">
    <div class="gcard" style="width:100%;max-width:480px;margin:0">
        <div class="gcard-hd">
            <span class="gcard-title">Nuevo cliente</span>
            <button type="button" class="gbtn gbtn-ghost gbtn-xs" onclick="cerrarNuevoCliente()">✕</button>
        </div>
        <div class="gcard-bd">
            <div id="qc-error" style="display:none;padding:9px 12px;border-radius:7px;background:#1f0a0a;
                 border:1px solid #3d1a1a;color:#e05555;font-size:13px;margin-bottom:14px"></div>

            <div class="gfg">
                <label class="glabel">Nombre / Razón social *</label>
                <input type="text" id="qc-nombre" class="ginput" autocomplete="off">
            </div>

            <div class="row g-2">
                <div class="col-6">
                    <div class="gfg">
                        <label class="glabel">CUIT</label>
                        <input type="text" id="qc-cuit" class="ginput" autocomplete="off" placeholder="Opcional">
                    </div>
                </div>
                <div class="col-6">
                    <div class="gfg">
                        <label class="glabel">Condición IVA</label>
                        <select id="qc-condicion" class="gselect">
                            <option value="">—</option>
                            <option value="responsable_inscripto">Responsable Inscripto</option>
                            <option value="monotributo">Monotributo</option>
                            <option value="exento">Exento</option>
                            <option value="consumidor_final">Consumidor Final</option>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="gfg">
                        <label class="glabel">Teléfono</label>
                        <input type="text" id="qc-telefono" class="ginput" autocomplete="off" placeholder="Opcional">
                    </div>
                </div>
                <div class="col-6">
                    <div class="gfg">
                        <label class="glabel">Email</label>
                        <input type="email" id="qc-email" class="ginput" autocomplete="off" placeholder="Opcional">
                    </div>
                </div>
            </div>

            <div class="gfg mb-0">
                <label class="glabel">Dirección</label>
                <input type="text" id="qc-direccion" class="ginput" autocomplete="off" placeholder="Opcional">
            </div>

            <div style="display:flex;gap:8px;margin-top:18px">
                <button type="button" id="qc-guardar" class="gbtn gbtn-primary" onclick="guardarNuevoCliente()">Guardar cliente</button>
                <button type="button" class="gbtn gbtn-ghost" onclick="cerrarNuevoCliente()">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const overlay = document.getElementById('qc-overlay');
    const errBox  = document.getElementById('qc-error');
    const CSRF    = '{{ csrf_token() }}';

    window.abrirNuevoCliente = function () {
        errBox.style.display = 'none';
        overlay.style.display = 'flex';
        setTimeout(() => document.getElementById('qc-nombre').focus(), 50);
    };
    window.cerrarNuevoCliente = function () { overlay.style.display = 'none'; };

    // Cerrar al clickear el fondo o con Escape
    overlay.addEventListener('click', e => { if (e.target === overlay) cerrarNuevoCliente(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape' && overlay.style.display === 'flex') cerrarNuevoCliente(); });

    window.guardarNuevoCliente = function () {
        const nombre = document.getElementById('qc-nombre').value.trim();
        if (!nombre) { mostrarErr('Ingresá el nombre del cliente.'); return; }

        const btn = document.getElementById('qc-guardar');
        btn.disabled = true; btn.textContent = 'Guardando…';
        errBox.style.display = 'none';

        fetch('{{ route("clientes.quick") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({
                nombre:        nombre,
                cuit:          document.getElementById('qc-cuit').value.trim(),
                condicion_iva: document.getElementById('qc-condicion').value,
                telefono:      document.getElementById('qc-telefono').value.trim(),
                email:         document.getElementById('qc-email').value.trim(),
                direccion:     document.getElementById('qc-direccion').value.trim(),
            }),
        })
        .then(async r => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok) throw new Error(data.error || Object.values(data.errors || {}).flat()[0] || 'No se pudo crear el cliente.');
            return data;
        })
        .then(data => {
            // Agregar al Select2 y seleccionarlo
            const $sel = window.jQuery ? jQuery('#sel-cliente') : null;
            if ($sel && $sel.length) {
                const opt = new Option(data.text, data.id, true, true);
                $sel.append(opt).trigger('change');
            }
            // Limpiar y cerrar
            ['qc-nombre','qc-cuit','qc-telefono','qc-email','qc-direccion'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('qc-condicion').value = '';
            cerrarNuevoCliente();
        })
        .catch(err => mostrarErr(err.message))
        .finally(() => { btn.disabled = false; btn.textContent = 'Guardar cliente'; });
    };

    function mostrarErr(msg) { errBox.textContent = msg; errBox.style.display = 'block'; }
});
</script>

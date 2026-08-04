<script>
(function () {
    const CSRF        = '{{ csrf_token() }}';
    const PATENTE_URL = '{{ route("vehiculos-ploteo.patente-existe") }}';
    const MARCA_STORE = '{{ route("vehiculos-ploteo.marcas-store") }}';
    const MODELO_STORE= '{{ route("vehiculos-ploteo.modelos-store") }}';
    const MODELOS_URL = '{{ route("vehiculos-ploteo.modelos-por-marca", ["marca" => "__ID__"]) }}';

    // ── Modelo dependiente de marca ──────────────────────────────────
    function fetchModelos(marcaId, selected) {
        const $modelo = $('#veh-modelo');
        if (!marcaId) { $modelo.html('<option value="">— Elegí una marca primero —</option>'); return; }
        $modelo.html('<option value="">Cargando…</option>');
        fetch(MODELOS_URL.replace('__ID__', marcaId), { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(list => {
                let html = '<option value="">— Modelo —</option>';
                list.forEach(m => {
                    const sel = (String(m.id) === String(selected)) ? 'selected' : '';
                    html += `<option value="${m.id}" ${sel}>${m.nombre}</option>`;
                });
                $modelo.html(html);
            })
            .catch(() => $modelo.html('<option value="">— error al cargar —</option>'));
    }

    $('#veh-marca').on('change', function () { fetchModelos(this.value, null); });

    // Carga inicial (edit: precargar modelos de la marca guardada)
    $(function () {
        const marcaId = $('#veh-marca').val();
        const pre     = $('#veh-modelo').data('selected');
        if (marcaId) fetchModelos(marcaId, pre);
    });

    // ── Patente: sin espacios + mayúsculas + aviso "ya estuvo" ───────
    let patTimer;
    window.normPatente = function (el) {
        el.value = el.value.toUpperCase().replace(/\s+/g, '');
        clearTimeout(patTimer);
        patTimer = setTimeout(chequearPatente, 350);
    };
    function chequearPatente() {
        const el = document.getElementById('veh-patente');
        if (!el) return;
        const pat   = el.value;
        const aviso = document.getElementById('patente-aviso');
        if (pat.length < 2) { aviso.style.display = 'none'; return; }
        const ignore = el.dataset.ignore || '';
        fetch(PATENTE_URL + '?patente=' + encodeURIComponent(pat) + (ignore ? '&ignore=' + ignore : ''),
              { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(d => {
                if (d.existe) {
                    aviso.innerHTML = '⚠ Este vehículo (<strong>' + pat + '</strong>) ya estuvo en la gráfica '
                        + d.count + (d.count === 1 ? ' vez' : ' veces')
                        + (d.ultima ? ' · última: ' + d.ultima : '') + '.';
                    aviso.style.display = 'block';
                } else {
                    aviso.style.display = 'none';
                }
            })
            .catch(() => {});
    }

    // ── Modales de alta ─────────────────────────────────────────────
    function addOption(sel, id, text) {
        if ($(sel + ' option[value="' + id + '"]').length === 0) $(sel).append(new Option(text, id, false, false));
    }
    function showErr(id, msg) { $('#' + id).text(msg).show(); }

    window.cerrarModal    = function (id) { document.getElementById(id).style.display = 'none'; };
    window.abrirNuevaMarca = function () { $('#marca-error').hide(); $('#marca-overlay').css('display', 'flex'); setTimeout(() => $('#marca-nombre').focus(), 50); };
    window.abrirNuevoModelo = function () {
        $('#modelo-error').hide();
        $('#modelo-marca').val($('#veh-marca').val() || '');
        $('#modelo-overlay').css('display', 'flex');
        setTimeout(() => $('#modelo-nombre').focus(), 50);
    };

    window.guardarMarca = function () {
        const nombre = $('#marca-nombre').val().trim();
        if (!nombre) { showErr('marca-error', 'Ingresá el nombre de la marca.'); return; }
        const $btn = $('#marca-guardar').prop('disabled', true).text('Guardando…');
        fetch(MARCA_STORE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ nombre }),
        })
        .then(async r => { const d = await r.json().catch(() => ({})); if (!r.ok) throw new Error(d.error || Object.values(d.errors || {}).flat()[0] || 'No se pudo crear la marca.'); return d; })
        .then(d => {
            addOption('#veh-marca', d.id, d.nombre);
            addOption('#modelo-marca', d.id, d.nombre);
            $('#veh-marca').val(d.id);
            fetchModelos(d.id, null);
            $('#marca-nombre').val('');
            cerrarModal('marca-overlay');
        })
        .catch(e => showErr('marca-error', e.message))
        .finally(() => $btn.prop('disabled', false).text('Guardar marca'));
    };

    window.guardarModelo = function () {
        const marca_id = $('#modelo-marca').val();
        const nombre   = $('#modelo-nombre').val().trim();
        if (!marca_id) { showErr('modelo-error', 'Elegí la marca.'); return; }
        if (!nombre)   { showErr('modelo-error', 'Ingresá el nombre del modelo.'); return; }
        const $btn = $('#modelo-guardar').prop('disabled', true).text('Guardando…');
        fetch(MODELO_STORE, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ marca_id, nombre }),
        })
        .then(async r => { const d = await r.json().catch(() => ({})); if (!r.ok) throw new Error(d.error || Object.values(d.errors || {}).flat()[0] || 'No se pudo crear el modelo.'); return d; })
        .then(d => {
            $('#veh-marca').val(marca_id);           // alinear el select principal a esa marca
            fetchModelos(marca_id, d.id);            // recargar y dejar el nuevo seleccionado
            $('#modelo-nombre').val('');
            cerrarModal('modelo-overlay');
        })
        .catch(e => showErr('modelo-error', e.message))
        .finally(() => $btn.prop('disabled', false).text('Guardar modelo'));
    };

    // Cerrar por fondo / Escape
    $('#marca-overlay, #modelo-overlay').on('click', function (e) { if (e.target === this) this.style.display = 'none'; });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') { cerrarModal('marca-overlay'); cerrarModal('modelo-overlay'); }
    });
})();
</script>

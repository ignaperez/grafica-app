{{--
  Partial: lookup de CUIT en padrón ARCA
  Llena automáticamente: f-nombre, f-cuit, f-direccion, f-slug (opcional)
  Parámetros:
    $cuitActual  (opcional) — CUIT precargado al editar
    $conSlug     (bool, default true) — si debe auto-generar el slug
--}}
@php $conSlug = $conSlug ?? true; @endphp

<div class="sa-card">
    <div class="sa-card-hd"><span class="sa-card-title">Consultar CUIT en ARCA</span></div>
    <div class="sa-card-bd">
        <div style="display:flex;gap:10px;align-items:flex-end">
            <div class="fg" style="flex:1;margin:0">
                <label class="glb">CUIT</label>
                <input class="gin" type="text" id="cuit-lookup"
                       placeholder="20-12345678-9 ó 20123456789"
                       value="{{ $cuitActual ?? '' }}"
                       maxlength="13" autocomplete="off">
            </div>
            <button type="button" class="btn btn-primary" id="btn-consultar" style="white-space:nowrap">
                <span id="btn-txt">Consultar ARCA →</span>
                <span id="btn-spin" style="display:none">Consultando…</span>
            </button>
        </div>
        <div id="arca-result" style="display:none;margin-top:14px;padding:14px;background:#0a1a0a;border:1px solid #1d4a30;border-radius:6px">
            <div style="font-size:.75rem;color:#3fb96a;letter-spacing:.1em;text-transform:uppercase;font-weight:700;margin-bottom:8px">✓ Datos obtenidos de ARCA</div>
            <div id="arca-preview" style="font-size:.88rem;line-height:1.7;color:#c8e6c9"></div>
            <button type="button" id="btn-aplicar" class="btn btn-ghost btn-sm" style="margin-top:10px">
                Aplicar datos al formulario ↓
            </button>
        </div>
        <div id="arca-error" style="display:none;margin-top:10px;padding:10px 14px;background:#1a0a0a;border:1px solid #4a1d1d;border-radius:6px;color:#e05050;font-size:.85rem"></div>
    </div>
</div>

<script>
(function () {
    const URL_CONSULTA = '{{ route('super-admin.consultar-cuit') }}';
    const CON_SLUG     = {{ $conSlug ? 'true' : 'false' }};
    let arcaDatos = null;

    function toSlug(str) {
        return str.toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    // Auto-slug desde nombre (solo en create)
    if (CON_SLUG) {
        const fNombre = document.getElementById('f-nombre');
        const fSlug   = document.getElementById('f-slug');
        if (fNombre && fSlug) {
            fNombre.addEventListener('input', function () {
                if (!fSlug.dataset.touched) fSlug.value = toSlug(this.value);
            });
            fSlug.addEventListener('input', function () { this.dataset.touched = '1'; });
        }
    }

    function consultarArca() {
        const cuit    = document.getElementById('cuit-lookup').value.replace(/\D/g, '');
        const errBox  = document.getElementById('arca-error');
        const resBox  = document.getElementById('arca-result');
        const btnTxt  = document.getElementById('btn-txt');
        const btnSpin = document.getElementById('btn-spin');

        errBox.style.display = 'none';
        resBox.style.display = 'none';
        arcaDatos = null;

        if (cuit.length !== 11) {
            errBox.textContent = 'El CUIT debe tener 11 dígitos (sin guiones).';
            errBox.style.display = 'block';
            return;
        }

        btnTxt.style.display = 'none';
        btnSpin.style.display = 'inline';

        fetch(`${URL_CONSULTA}?cuit=${cuit}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            btnTxt.style.display = 'inline';
            btnSpin.style.display = 'none';

            if (!ok || data.error) {
                errBox.textContent = data.error || 'Error al consultar ARCA.';
                errBox.style.display = 'block';
                return;
            }

            arcaDatos = { ...data, cuit };

            const iva = {
                monotributo: 'Monotributista',
                responsable_inscripto: 'Resp. Inscripto',
                exento: 'Exento',
                consumidor_final: 'Consumidor Final'
            }[data.condicion_iva] || '';

            document.getElementById('arca-preview').innerHTML =
                `<strong>${data.nombre || '—'}</strong><br>` +
                (data.direccion ? `📍 ${data.direccion}<br>` : '') +
                (iva ? `🏷 ${iva}` : '') +
                (data.estado && data.estado !== 'ACTIVO'
                    ? ` · <span style="color:#e05050">${data.estado}</span>` : '');

            resBox.style.display = 'block';
            resBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        })
        .catch(err => {
            btnTxt.style.display = 'inline';
            btnSpin.style.display = 'none';
            errBox.textContent = 'Error de red: ' + err.message;
            errBox.style.display = 'block';
        });
    }

    function aplicarDatos() {
        if (!arcaDatos) return;
        const { nombre, direccion, cuit } = arcaDatos;

        const fNombre   = document.getElementById('f-nombre');
        const fCuit     = document.getElementById('f-cuit');
        const fDir      = document.getElementById('f-direccion');
        const fSlug     = document.getElementById('f-slug');
        const fArcaCuit = document.getElementById('f-arca-cuit');

        if (fNombre   && nombre)   fNombre.value = nombre;
        if (fDir      && direccion) fDir.value   = direccion;
        if (fArcaCuit && cuit)     fArcaCuit.value = cuit;

        if (fCuit && cuit.length === 11) {
            fCuit.value = cuit.slice(0,2) + '-' + cuit.slice(2,10) + '-' + cuit.slice(10);
        }

        if (CON_SLUG && fSlug && nombre && !fSlug.dataset.touched) {
            fSlug.value = toSlug(nombre);
        }
    }

    document.getElementById('btn-consultar').addEventListener('click', consultarArca);
    document.getElementById('btn-aplicar')?.addEventListener('click', aplicarDatos);
    document.getElementById('cuit-lookup').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); consultarArca(); }
    });
})();
</script>

@extends('super-admin.layout')

@section('title', $tenant->nombre)

@section('content')

<div class="sa-hd">
    <div>
        <h1>{{ $tenant->nombre }}</h1>
        <div class="sub mono">tenant_{{ $tenant->id }}</div>
    </div>
    <div class="sa-hd-actions">
        <a href="{{ route('super-admin.empresas.edit', $tenant->id) }}" class="btn btn-ghost">Editar</a>
        @if(!$tenant->trashed())
        <form method="POST" action="{{ route('super-admin.empresas.impersonar', $tenant->id) }}" style="display:inline">
            @csrf
            <button class="btn btn-primary" type="submit">↗ Ir al panel</button>
        </form>
        @endif
        <a href="{{ route('super-admin.empresas.index') }}" class="btn btn-ghost">← Volver</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

    {{-- Datos generales --}}
    <div class="sa-card">
        <div class="sa-card-hd"><span class="sa-card-title">Datos de la empresa</span></div>
        <div class="sa-card-bd" style="display:grid;gap:12px">
            <div><div style="font-size:.75rem;color:#888;text-transform:uppercase;letter-spacing:.08em">CUIT</div><div class="mono">{{ $tenant->cuit ?? '—' }}</div></div>
            <div><div style="font-size:.75rem;color:#888;text-transform:uppercase;letter-spacing:.08em">Email</div><div>{{ $tenant->email ?? '—' }}</div></div>
            <div><div style="font-size:.75rem;color:#888;text-transform:uppercase;letter-spacing:.08em">Teléfono</div><div>{{ $tenant->telefono ?? '—' }}</div></div>
            <div><div style="font-size:.75rem;color:#888;text-transform:uppercase;letter-spacing:.08em">Dirección</div><div>{{ $tenant->direccion ?? '—' }}</div></div>
            <div><div style="font-size:.75rem;color:#888;text-transform:uppercase;letter-spacing:.08em">Subdominio</div>
                @if($tenant->subdomain())
                <a href="{{ $tenant->panelUrl() }}" target="_blank" style="color:var(--ac)" class="mono">{{ $tenant->subdomain() }}.plote.ar ↗</a>
                @else
                <span style="color:#888">Sin dominio</span>
                @endif
            </div>
            <div><div style="font-size:.75rem;color:#888;text-transform:uppercase;letter-spacing:.08em">Estado</div>
                @if($tenant->trashed())
                <span class="badge badge-del">Inactiva</span>
                @else
                <span class="badge badge-ok">Activa</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ARCA --}}
    <div class="sa-card">
        <div class="sa-card-hd">
            <span class="sa-card-title">ARCA / Facturación electrónica</span>
        </div>

        {{-- Estado actual --}}
        <div class="sa-card-bd" style="display:grid;gap:12px">
            <div>
                <div style="font-size:.75rem;color:#888;text-transform:uppercase;letter-spacing:.08em">CUIT fiscal</div>
                <div class="mono">{{ $tenant->arca_cuit ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:.75rem;color:#888;text-transform:uppercase;letter-spacing:.08em">Punto de venta</div>
                <div class="mono">{{ $tenant->arca_punto_venta ?? '—' }}</div>
            </div>
            <div>
                <div style="font-size:.75rem;color:#888;text-transform:uppercase;letter-spacing:.08em">Entorno</div>
                <span class="badge {{ $tenant->arca_production ? 'badge-ok' : 'badge-warn' }}">
                    {{ $tenant->arca_production ? 'Producción' : 'Homologación' }}
                </span>
            </div>
            <div>
                <div style="font-size:.75rem;color:#888;text-transform:uppercase;letter-spacing:.08em">Certificado</div>
                @if($tenant->has_arca_cert)
                    <span class="badge badge-ok">✓ Cargado</span>
                @else
                    <span class="badge badge-warn">Sin certificado</span>
                @endif
            </div>
            <div>
                <div style="font-size:.75rem;color:#888;text-transform:uppercase;letter-spacing:.08em">Clave privada</div>
                @if(\Illuminate\Support\Facades\Storage::disk('local')->exists("arca/{$tenant->id}/private.key"))
                    <span class="badge badge-ok">✓ Generada</span>
                @else
                    <span class="badge badge-warn">Sin clave</span>
                @endif
            </div>
        </div>

        {{-- ── Paso 1: Generar CSR ── --}}
        <div style="padding:16px 20px;border-top:1px solid #1e1e1e">
            <div style="font-size:.78rem;color:#888;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">
                Paso 1 — Generar clave privada y CSR
            </div>
            <div style="font-size:.8rem;color:#777;margin-bottom:12px">
                Genera la clave en el servidor y produce el CSR para presentar en ARCA.
                @if(\Illuminate\Support\Facades\Storage::disk('local')->exists("arca/{$tenant->id}/private.key"))
                <br><span style="color:#f59e0b">⚠ Ya existe una clave. Si regenerás, el certificado actual queda inválido.</span>
                @endif
            </div>
            <div style="display:flex;gap:8px;align-items:flex-end">
                <div class="fg" style="flex:1;margin:0">
                    <label class="glb" style="font-size:.78rem">CUIT (11 dígitos, sin guiones)</label>
                    <input class="gin" type="text" id="csr-cuit"
                           value="{{ preg_replace('/\D/', '', $tenant->arca_cuit ?? $tenant->cuit ?? '') }}"
                           placeholder="20123456789" maxlength="11" style="font-family:monospace">
                </div>
                <button type="button" id="btn-gen-csr" class="btn btn-ghost" style="white-space:nowrap;margin-bottom:0">
                    <span id="btn-gen-txt">Generar CSR →</span>
                    <span id="btn-gen-spin" style="display:none">Generando…</span>
                </button>
            </div>

            <div id="csr-ok" style="display:none;margin-top:14px">
                <div style="font-size:.8rem;color:#3fb96a;margin-bottom:6px">
                    ✓ CSR generado — copiá el texto y pegalo en ARCA para obtener el cert.crt
                </div>
                <textarea id="csr-text" readonly
                    style="width:100%;height:130px;background:#0a0a0a;color:#9ecfa4;border:1px solid #1d4a30;
                           border-radius:4px;padding:10px;font-family:monospace;font-size:.72rem;
                           resize:vertical;line-height:1.4"></textarea>
                <button type="button" id="btn-copy-csr" class="btn btn-ghost btn-sm" style="margin-top:6px">
                    Copiar CSR
                </button>
            </div>
            <div id="csr-err" style="display:none;margin-top:8px;padding:8px 12px;background:#1a0a0a;
                                     border:1px solid #4a1d1d;border-radius:4px;color:#e05050;font-size:.82rem">
            </div>
        </div>

        {{-- ── Paso 2: Subir cert.crt ── --}}
        <div style="padding:16px 20px;border-top:1px solid #1e1e1e">
            <div style="font-size:.78rem;color:#888;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">
                Paso 2 — Subir certificado de ARCA (.crt)
            </div>
            <div style="font-size:.8rem;color:#777;margin-bottom:12px">
                Presentá el CSR en <strong style="color:#ccc">auth.afip.gob.ar</strong>
                → Administración de Certificados → wsfe → Generar certificado.
                Descargás el .crt y lo subís acá.
            </div>
            <form method="POST" action="{{ route('super-admin.empresas.cert', $tenant->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="fg">
                    <label class="glb">Certificado (.crt / .pem)</label>
                    <input class="gin" type="file" name="cert" accept=".crt,.cer,.pem">
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
                    <button class="btn btn-ghost btn-sm" type="submit">Subir certificado</button>
                    <span style="font-size:.75rem;color:#666">— o bien subí también la clave si la generaste externamente:</span>
                </div>
                <div class="fg" style="margin-top:10px">
                    <label class="glb">Clave privada (.key) — solo si la generaste fuera del panel</label>
                    <input class="gin" type="file" name="key">
                </div>
            </form>
        </div>

        {{-- ── Paso 3: Descargar private.key ── --}}
        @if(\Illuminate\Support\Facades\Storage::disk('local')->exists("arca/{$tenant->id}/private.key"))
        <div style="padding:16px 20px;border-top:1px solid #1e1e1e;background:#0a1209;border-radius:0 0 8px 8px">
            <div style="font-size:.78rem;color:#888;text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">
                Paso 3 — Backup de la clave privada
            </div>
            <div style="font-size:.8rem;color:#f59e0b;margin-bottom:12px">
                ⚠ Guardá este archivo en un lugar seguro (fuera del servidor).
                Si se pierde la clave, no podés facturar con este certificado.
            </div>
            <a href="{{ route('super-admin.empresas.descargar-key', $tenant->id) }}"
               class="btn btn-ghost btn-sm">
                ⬇ Descargar private.key
            </a>
        </div>
        @endif

    </div>{{-- /sa-card ARCA --}}

</div>

{{-- Danger zone --}}
@if(!$tenant->trashed())
<div class="sa-card" style="border-color:#2a1515">
    <div class="sa-card-hd" style="border-color:#2a1515"><span class="sa-card-title" style="color:#e05050">Zona peligrosa</span></div>
    <div class="sa-card-bd" style="display:flex;align-items:center;justify-content:space-between">
        <div>
            <div style="font-weight:600">Desactivar empresa</div>
            <div style="font-size:.82rem;color:#888;margin-top:2px">La empresa y sus datos quedan en la BD pero no puede acceder al sistema.</div>
        </div>
        <form method="POST" action="{{ route('super-admin.empresas.destroy', $tenant->id) }}"
              onsubmit="return confirm('¿Desactivar {{ $tenant->nombre }}?')">
            @csrf @method('DELETE')
            <button class="btn btn-danger" type="submit">Desactivar</button>
        </form>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
(function () {
    const URL_CSR  = '{{ route('super-admin.empresas.generar-csr', $tenant->id) }}';
    const CSRF     = '{{ csrf_token() }}';

    const btnGen   = document.getElementById('btn-gen-csr');
    const btnTxt   = document.getElementById('btn-gen-txt');
    const btnSpin  = document.getElementById('btn-gen-spin');
    const okBox    = document.getElementById('csr-ok');
    const errBox   = document.getElementById('csr-err');
    const csrText  = document.getElementById('csr-text');
    const btnCopy  = document.getElementById('btn-copy-csr');

    if (!btnGen) return;

    btnGen.addEventListener('click', function () {
        const cuit = document.getElementById('csr-cuit').value.replace(/\D/g, '');

        errBox.style.display = 'none';
        okBox.style.display  = 'none';

        if (cuit.length !== 11) {
            errBox.textContent   = 'El CUIT debe tener 11 dígitos.';
            errBox.style.display = 'block';
            return;
        }

        const tieneKey = {{ \Illuminate\Support\Facades\Storage::disk('local')->exists("arca/{$tenant->id}/private.key") ? 'true' : 'false' }};
        if (tieneKey && !confirm('Ya existe una clave privada para esta empresa.\n\nSi generás una nueva, el certificado actual quedará INVÁLIDO y deberás pedir un nuevo cert.crt a ARCA.\n\n¿Continuar?')) {
            return;
        }

        btnTxt.style.display  = 'none';
        btnSpin.style.display = 'inline';
        btnGen.disabled       = true;

        fetch(URL_CSR, {
            method:  'POST',
            headers: {
                'Accept':       'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ cuit }),
        })
        .then(r => r.json().then(data => ({ ok: r.ok, data })))
        .then(({ ok, data }) => {
            btnTxt.style.display  = 'inline';
            btnSpin.style.display = 'none';
            btnGen.disabled       = false;

            if (!ok || data.error) {
                errBox.textContent   = data.error || 'Error al generar el CSR.';
                errBox.style.display = 'block';
            } else {
                csrText.value       = data.csr;
                okBox.style.display = 'block';
                okBox.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

                // Actualizar badge de clave en la página sin recargar
                document.querySelectorAll('.badge-warn').forEach(function (el) {
                    if (el.textContent.trim() === 'Sin clave') {
                        el.className   = 'badge badge-ok';
                        el.textContent = '✓ Generada';
                    }
                });
            }
        })
        .catch(function (err) {
            btnTxt.style.display  = 'inline';
            btnSpin.style.display = 'none';
            btnGen.disabled       = false;
            errBox.textContent    = 'Error de red: ' + err.message;
            errBox.style.display  = 'block';
        });
    });

    // Copiar CSR al portapapeles
    if (btnCopy) {
        btnCopy.addEventListener('click', function () {
            csrText.select();
            navigator.clipboard?.writeText(csrText.value)
                .then(() => { btnCopy.textContent = '✓ Copiado'; setTimeout(() => { btnCopy.textContent = 'Copiar CSR'; }, 2000); })
                .catch(() => document.execCommand('copy'));
        });
    }
})();
</script>
@endsection

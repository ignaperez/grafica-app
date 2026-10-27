@extends('layouts.app')

@section('page-title', 'Firmar documento')

@section('content')
<style>
    .firmar-wrap { max-width: 1000px; }
    .drop {
        border: 1px dashed var(--bm); border-radius: 12px; background: var(--bg-s);
        padding: 26px; text-align: center; cursor: pointer; transition: border-color .15s;
    }
    .drop:hover, .drop.over { border-color: var(--ac); }
    .drop input { display: none; }

    #escenario { position: relative; margin: 0 auto; background: #333; line-height: 0; box-shadow: 0 4px 20px rgba(0,0,0,.4); }
    #oc-canvas, #oc-img { display: block; max-width: 100%; height: auto; }

    #firma-mov {
        position: absolute; top: 20px; left: 20px; width: 180px; cursor: move;
        border: 1px dashed rgba(230,80,42,.9); box-sizing: border-box; touch-action: none;
    }
    #firma-mov img { width: 100%; height: auto; display: block; pointer-events: none; }
    #firma-mov .handle {
        position: absolute; right: -8px; bottom: -8px; width: 18px; height: 18px;
        background: var(--ac); border: 2px solid #fff; border-radius: 50%; cursor: nwse-resize; touch-action: none;
    }

    .nav-pag { display: flex; align-items: center; justify-content: center; gap: 12px; margin: 10px 0; }
    .firma-mini { max-height: 60px; background: #fff; border-radius: 6px; padding: 4px; }
</style>

<div class="firmar-wrap">

    {{-- ── 1. Firma ológrafa ─────────────────────────────────────────── --}}
    <div class="gcard" style="margin-bottom:16px">
        <div class="gcard-hd"><span class="gcard-title">1 · Tu firma</span></div>
        <div class="gcard-bd">
            @if($tieneFirma)
                <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
                    <img src="{{ route('documentos.firma.img') }}?v={{ now()->timestamp }}" class="firma-mini" alt="firma">
                    <span class="txd" style="font-size:12px">Firma guardada. Se usa para firmar los documentos.</span>
                    <div style="display:flex;gap:8px;margin-left:auto">
                        <form method="POST" action="{{ route('documentos.firma.guardar') }}" enctype="multipart/form-data" style="display:inline">
                            @csrf
                            <label class="gbtn gbtn-ghost gbtn-sm" style="cursor:pointer">
                                Reemplazar
                                <input type="file" name="firma" accept="image/png" style="display:none" onchange="this.form.submit()">
                            </label>
                        </form>
                        <form method="POST" action="{{ route('documentos.firma.eliminar') }}" style="display:inline"
                              onsubmit="return confirm('¿Eliminar tu firma guardada?')">
                            @csrf @method('DELETE')
                            <button class="gbtn gbtn-danger gbtn-sm">Eliminar</button>
                        </form>
                    </div>
                </div>
            @else
                <form method="POST" action="{{ route('documentos.firma.guardar') }}" enctype="multipart/form-data">
                    @csrf
                    <p class="txd" style="font-size:13px;margin:0 0 12px">
                        Subí tu firma escaneada en <strong>PNG con fondo transparente</strong> (una sola vez; después queda guardada).
                    </p>
                    <label class="drop" style="display:block">
                        <div style="font-size:26px">✍️</div>
                        <div style="font-size:12px;color:var(--txd)">Elegí el PNG de tu firma</div>
                        <input type="file" name="firma" accept="image/png" onchange="this.form.submit()">
                    </label>
                    @error('firma')<div class="gerr" style="margin-top:8px">{{ $message }}</div>@enderror
                </form>
            @endif
        </div>
    </div>

    {{-- ── 2. Documento ──────────────────────────────────────────────── --}}
    <div class="gcard" style="margin-bottom:16px {{ $tieneFirma ? '' : ';opacity:.5;pointer-events:none' }}">
        <div class="gcard-hd"><span class="gcard-title">2 · Orden de compra</span></div>
        <div class="gcard-bd">
            <label class="drop over" id="drop-oc" style="display:block">
                <div style="font-size:26px">📄</div>
                <div style="font-size:13px;color:var(--tx)">Subí la orden de compra (PDF o imagen)</div>
                <div style="font-size:11px;color:var(--txd);margin-top:3px">Hasta 100 MB</div>
                <input type="file" id="oc-file" accept="application/pdf,image/*">
            </label>
            <div id="oc-nombre" class="txd" style="font-size:12px;margin-top:8px"></div>
        </div>
    </div>

    {{-- ── 3. Ubicar la firma + generar ──────────────────────────────── --}}
    <div class="gcard" id="card-editor" style="display:none;margin-bottom:40px">
        <div class="gcard-hd">
            <span class="gcard-title">3 · Ubicá la firma y generá</span>
        </div>
        <div class="gcard-bd">
            <p class="txd" style="font-size:12px;margin:0 0 12px">
                Arrastrá la firma hasta donde va y usá el punto naranja para agrandarla o achicarla.
                En documentos de varias hojas, moveté a la hoja que querés firmar.
            </p>

            <div class="nav-pag" id="nav-pag" style="display:none">
                <button type="button" class="gbtn gbtn-ghost gbtn-sm" id="pag-prev">← Anterior</button>
                <span class="txd" style="font-size:12px">Hoja <strong id="pag-actual">1</strong> / <span id="pag-total">1</span></span>
                <button type="button" class="gbtn gbtn-ghost gbtn-sm" id="pag-next">Siguiente →</button>
            </div>

            <div id="escenario">
                <canvas id="oc-canvas" style="display:none"></canvas>
                <img id="oc-img" style="display:none" alt="">
                <div id="firma-mov">
                    <img src="{{ route('documentos.firma.img') }}?v={{ now()->timestamp }}" alt="firma" id="firma-img">
                    <div class="handle" id="firma-handle"></div>
                </div>
            </div>

            <form method="POST" action="{{ route('documentos.procesar') }}" enctype="multipart/form-data"
                  id="form-firmar" style="text-align:center;margin-top:18px">
                @csrf
                {{-- el archivo real se copia acá al generar --}}
                <input type="hidden" name="pagina" id="f-pagina" value="1">
                <input type="hidden" name="x" id="f-x">
                <input type="hidden" name="y" id="f-y">
                <input type="hidden" name="w" id="f-w">
                <button type="submit" class="gbtn gbtn-primary" style="padding:11px 28px" id="btn-generar">
                    ⬇ Generar PDF firmado
                </button>
            </form>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script>
(function () {
    const pdfjsLib = window['pdfjsLib'];
    if (pdfjsLib) pdfjsLib.GlobalWorkerOptions.workerSrc =
        'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const inputOC   = document.getElementById('oc-file');
    const card      = document.getElementById('card-editor');
    const escenario = document.getElementById('escenario');
    const canvas    = document.getElementById('oc-canvas');
    const imgEl     = document.getElementById('oc-img');
    const mov       = document.getElementById('firma-mov');
    const handle    = document.getElementById('firma-handle');
    const navPag    = document.getElementById('nav-pag');
    const lblActual = document.getElementById('pag-actual');
    const lblTotal  = document.getElementById('pag-total');

    let pdfDoc = null, pagActual = 1, pagTotal = 1, selectedFile = null;

    // ── Cargar la OC ────────────────────────────────────────────────
    inputOC.addEventListener('change', async function () {
        const file = this.files[0];
        if (!file) return;
        selectedFile = file;
        document.getElementById('oc-nombre').textContent = '📎 ' + file.name;

        if (file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')) {
            const buf = await file.arrayBuffer();
            pdfDoc = await pdfjsLib.getDocument({ data: buf }).promise;
            pagTotal = pdfDoc.numPages; pagActual = 1;
            lblTotal.textContent = pagTotal;
            navPag.style.display = pagTotal > 1 ? 'flex' : 'none';
            imgEl.style.display = 'none'; canvas.style.display = 'block';
            await renderPdfPage(1);
        } else {
            pdfDoc = null; pagTotal = 1; pagActual = 1;
            navPag.style.display = 'none';
            canvas.style.display = 'none'; imgEl.style.display = 'block';
            imgEl.src = URL.createObjectURL(file);
            await new Promise(r => { imgEl.onload = r; });
        }
        card.style.display = 'block';
        document.getElementById('f-pagina').value = pagActual;
        resetFirmaPos();
        card.scrollIntoView({ behavior: 'smooth' });
    });

    async function renderPdfPage(n) {
        const page = await pdfDoc.getPage(n);
        const scale = Math.min(2, 1400 / page.getViewport({ scale: 1 }).width);
        const vp = page.getViewport({ scale });
        canvas.width = vp.width; canvas.height = vp.height;
        await page.render({ canvasContext: canvas.getContext('2d'), viewport: vp }).promise;
        lblActual.textContent = n;
        document.getElementById('f-pagina').value = n;
    }

    document.getElementById('pag-prev')?.addEventListener('click', async () => {
        if (pagActual > 1) { pagActual--; await renderPdfPage(pagActual); }
    });
    document.getElementById('pag-next')?.addEventListener('click', async () => {
        if (pagActual < pagTotal) { pagActual++; await renderPdfPage(pagActual); }
    });

    function resetFirmaPos() {
        mov.style.left = '20px'; mov.style.top = '20px';
        mov.style.width = Math.round((escenario.clientWidth || 600) * 0.28) + 'px';
    }

    // ── Arrastrar ───────────────────────────────────────────────────
    let dragging = false, resizing = false, sx, sy, sl, st, sw;

    mov.addEventListener('pointerdown', e => {
        if (e.target === handle) return;
        dragging = true; sx = e.clientX; sy = e.clientY;
        sl = mov.offsetLeft; st = mov.offsetTop;
        mov.setPointerCapture(e.pointerId); e.preventDefault();
    });
    handle.addEventListener('pointerdown', e => {
        resizing = true; sx = e.clientX; sw = mov.offsetWidth;
        handle.setPointerCapture(e.pointerId); e.preventDefault(); e.stopPropagation();
    });
    document.addEventListener('pointermove', e => {
        if (dragging) {
            let nl = sl + (e.clientX - sx), nt = st + (e.clientY - sy);
            nl = Math.max(0, Math.min(nl, escenario.clientWidth  - mov.offsetWidth));
            nt = Math.max(0, Math.min(nt, escenario.clientHeight - mov.offsetHeight));
            mov.style.left = nl + 'px'; mov.style.top = nt + 'px';
        } else if (resizing) {
            let nw = sw + (e.clientX - sx);
            nw = Math.max(40, Math.min(nw, escenario.clientWidth - mov.offsetLeft));
            mov.style.width = nw + 'px';
        }
    });
    document.addEventListener('pointerup', () => { dragging = false; resizing = false; });

    // ── Generar: pasar coords (fracciones) + el archivo al form ─────
    document.getElementById('form-firmar').addEventListener('submit', function (e) {
        if (!selectedFile) { e.preventDefault(); alert('Subí la orden de compra.'); return; }

        const W = escenario.clientWidth, H = escenario.clientHeight;
        document.getElementById('f-x').value = (mov.offsetLeft / W).toFixed(5);
        document.getElementById('f-y').value = (mov.offsetTop  / H).toFixed(5);
        document.getElementById('f-w').value = (mov.offsetWidth / W).toFixed(5);
        document.getElementById('f-pagina').value = pagActual;

        // adjuntar el archivo seleccionado al form (no estaba en este form)
        const dt = new DataTransfer();
        dt.items.add(selectedFile);
        let fileInput = this.querySelector('input[name="oc"]');
        if (!fileInput) {
            fileInput = document.createElement('input');
            fileInput.type = 'file'; fileInput.name = 'oc';
            fileInput.style.display = 'none';
            this.appendChild(fileInput);
        }
        fileInput.files = dt.files;

        document.getElementById('btn-generar').textContent = 'Generando…';
        document.getElementById('btn-generar').disabled = true;
        // se re-habilita si la descarga no navega (el form devuelve un archivo)
        setTimeout(() => {
            document.getElementById('btn-generar').textContent = '⬇ Generar PDF firmado';
            document.getElementById('btn-generar').disabled = false;
        }, 4000);
    });
})();
</script>
@endsection

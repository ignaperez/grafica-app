<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Factura;
use App\Models\FacturaItem;
use App\Models\FacturaBorrador;
use App\Models\Cliente;
use App\Models\Presupuesto;
use App\Services\ArcaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class FacturaController extends Controller
{
    /**
     * Candados tomados durante una emisión (anti doble-submit). Si la emisión no
     * llega a ARCA se liberan; si sale bien NO se liberan, así un reenvío tardío
     * del mismo formulario se descarta en vez de pedir un segundo CAE.
     */
    private array $locks = [];

    /** Cuánto vive el candado / la memoria de una emisión, en segundos. */
    private const EMISION_TTL = 600;

    /** Factura → nota de crédito de la misma letra (A→NC-A, B→NC-B, C→NC-C). */
    private const TIPO_NC = [1 => 3, 6 => 8, 11 => 13];

    public function index()
    {
        $facturas = Factura::with(['cliente', 'presupuesto', 'createdBy', 'cobros'])
            ->orderByDesc('id')
            ->get();

        // Borradores pendientes (cargas que quedaron a medias por un error)
        $borradores = FacturaBorrador::with('cliente')->orderByDesc('id')->get();

        return view('facturas.index', compact('facturas', 'borradores'));
    }

    /** Descarga el listado de facturas como Excel. */
    public function exportar()
    {
        $facturas = Factura::with(['cliente', 'cobros'])
            ->orderByDesc('id')
            ->get();

        $html = view('facturas.export', compact('facturas'))->render();

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="facturas_' . now()->format('Y-m-d') . '.xls"',
        ]);
    }

    public function create(Request $request)
    {
        // Retomar un borrador guardado: flasheamos sus datos como old() y
        // reusamos exactamente el mismo render que en la vuelta de un error.
        if ($request->filled('borrador_id')) {
            $borrador = FacturaBorrador::find($request->borrador_id);
            if ($borrador) {
                return redirect()->route('facturas.create')
                    ->withInput(array_merge($borrador->datos ?? [], ['borrador_id' => $borrador->id]))
                    ->with('info', 'Retomando un borrador guardado. Revisá los datos y volvé a emitir.');
            }
        }

        // Nota de crédito a partir de un comprobante ya emitido: traemos TODOS
        // sus datos (cliente, receptor, ítems, IVA y la referencia fiscal) como
        // old() y reusamos exactamente el mismo render del formulario.
        if ($request->filled('nc_de')) {
            $original = Factura::with(['cliente', 'items'])->find($request->nc_de);

            // Solo se acredita una FACTURA. Sobre una NC el tipo calculado caería
            // fuera de los tipos ofrecidos y el <select> se iría en silencio a la
            // primera opción (Factura A) — se podría emitir una factura real por
            // error. Se corta acá.
            if (! $original || ! $original->esFactura()) {
                return redirect()->route('facturas.index')->with('error',
                    'Solo se puede emitir una nota de crédito sobre una factura, no sobre otra nota de crédito.');
            }

            if ($original->estado === 'anulada') {
                return redirect()->route('facturas.show', $original->id)->with('error',
                    'Esa factura ya está anulada: no corresponde emitirle una nota de crédito.');
            }

            // Los ítems se copian como precio FINAL. En comprobantes viejos
            // (anteriores al cambio de criterio) el precio guardado era NETO, y
            // ahí la suma de ítems no da el total: lo avisamos en vez de acreditar
            // un importe menor en silencio.
            $sumaItems = round((float) $original->items->sum('subtotal'), 2);
            $aviso     = abs($sumaItems - (float) $original->imp_total) > 0.05
                ? ' ⚠ Ojo: los ítems de ese comprobante suman $' . number_format($sumaItems, 2, ',', '.') .
                  ' pero su total fue $' . number_format((float) $original->imp_total, 2, ',', '.') .
                  ' (se guardó con el criterio viejo de precio neto). Corregí los precios antes de emitir.'
                : '';

            return redirect()->route('facturas.create')
                ->withInput($this->datosNcDesde($original))
                ->with('info', 'Datos traídos de ' . $original->tipoLabel() . ' ' .
                    $original->numeroFormateado() . '. Revisá los ítems (podés ajustar o borrar los que no se acrediten) y emití la nota de crédito.' . $aviso);
        }

        $presupuesto         = null;
        $clienteSeleccionado = null;
        $condIva             = \App\Models\Configuracion::get('empresa_condicion_iva', '');
        $condicionEmisor     = $condIva === 'responsable_inscripto' ? 'responsable_inscripto' : 'monotributo';
        // Tipo por defecto: RI → Factura B (6), Monotributo → Factura C (11)
        $tipoCbte            = $condicionEmisor === 'responsable_inscripto' ? 6 : 11;

        // Tipos de comprobante desde ARCA (con fallback)
        try {
            $tiposCbte = (new ArcaService())->tiposCbte();
        } catch (\Exception $e) {
            $tiposCbte = \App\Services\ArcaService::TIPOS_CBTE;
        }

        // Filtrar tipos según condición del emisor:
        // Monotributista → solo C (11) y NC-C (13)
        // RI            → A (1), B (6), NC-A (3), NC-B (8) — NO Factura C
        if ($condicionEmisor === 'monotributo') {
            $tiposCbte = array_intersect_key($tiposCbte, [11 => null, 13 => null]);
        } elseif ($condicionEmisor === 'responsable_inscripto') {
            $tiposCbte = array_intersect_key($tiposCbte, [1 => null, 6 => null, 3 => null, 8 => null]);
        }

        if ($request->filled('presupuesto_id')) {
            // Este es el punto de entrada real desde presupuestos/index. El botón
            // ya se deshabilita allá si el presupuesto tiene factura, pero por URL
            // se llega igual — así que la guarda va acá también.
            $yaFacturado = Factura::where('presupuesto_id', $request->presupuesto_id)
                ->where('estado', '!=', 'anulada')
                ->first();

            if ($yaFacturado) {
                return redirect()->route('facturas.show', $yaFacturado->id)->with('error',
                    'Ese presupuesto ya fue facturado (' . $yaFacturado->numeroFormateado() . ').');
            }

            $presupuesto         = Presupuesto::with(['cliente', 'items'])->find($request->presupuesto_id);
            $clienteSeleccionado = $presupuesto?->cliente;
        }

        // Si hubo un error de validación y se volvió con old(), recuperar el cliente
        if (! $clienteSeleccionado && old('cliente_id')) {
            $clienteSeleccionado = Cliente::find(old('cliente_id'));
        }

        // Token de un solo uso que identifica ESTA carga del formulario: si el
        // mismo form se envía dos veces (doble clic), el segundo POST se descarta.
        $emisionToken = (string) Str::uuid();

        return view('facturas.create', compact('presupuesto', 'tipoCbte', 'tiposCbte', 'clienteSeleccionado', 'condicionEmisor', 'emisionToken'));
    }

    public function store(Request $request)
    {
        $isNC = in_array((int) $request->tipo, [3, 8, 13]);

        $request->validate([
            'cliente_id'     => 'required|exists:clientes,id',
            'tipo'           => 'required|in:1,3,6,8,11,13',
            'fecha'          => 'required|date|before_or_equal:today',
            'concepto'       => 'required|in:1,2,3',
            'doc_tipo'       => 'required|in:80,96,99',
            // doc_nro requerido solo cuando doc_tipo != 99 (Consumidor Final)
            'doc_nro'        => 'required_unless:doc_tipo,99|nullable|string|max:20',
            'observaciones'  => 'nullable|string',
            'forma_pago'     => 'nullable|in:' . implode(',', array_keys(\App\Models\Cobro::FORMAS)),
            'items'          => 'required|array|min:1',
            'items.*.descripcion'     => 'required|string|max:1000',
            'items.*.cantidad'        => 'required|numeric|min:0.001',
            'items.*.unidad'          => 'nullable|in:unidad,m2,ml',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.iva'             => 'nullable|in:0,2.5,5,10.5,21,27,exento,no_gravado',
            // Comprobante original (solo para NCs)
            'nc_tipo'    => $isNC ? 'required|in:1,6,11' : 'nullable|integer',
            'nc_pto_vta' => $isNC ? 'required|integer|min:1' : 'nullable|integer',
            'nc_nro'     => $isNC ? 'required|integer|min:1' : 'nullable|integer',
        ]);

        // ── Anti doble emisión ─────────────────────────────────────
        // Un doble clic en "Sí, emitir" mandaba dos POST casi simultáneos: los dos
        // pasaban el chequeo de duplicado antes de que ninguno insertara, y ARCA
        // devolvía dos CAE. El candado es atómico (tabla cache_locks) y deja pasar
        // solo al primero; el segundo cae acá y nunca llega a ARCA.
        $emisionToken = (string) $request->input('emision_token', '');

        if ($emisionToken !== '' && ! $this->tomarCandado('token:' . $emisionToken)) {
            return $this->respuestaDuplicada($emisionToken);
        }

        // Evitar facturar dos veces el mismo presupuesto (las NC no cuentan).
        if ($request->presupuesto_id && ! $isNC) {
            // Candado por presupuesto: cubre el caso de dos pestañas/formularios
            // distintos (tokens distintos) facturando el mismo presupuesto a la vez.
            if (! $this->tomarCandado('presupuesto:' . $request->presupuesto_id)) {
                // Vuelve al formulario con todo guardado como borrador: puede ser
                // el 2do clic, pero también otra pestaña con una carga distinta.
                return $this->volverConBorrador($request,
                    'Ya hay una emisión en curso para ese presupuesto. Esperá unos segundos y revisá el listado de facturas antes de reintentar.');
            }

            $yaFacturado = Factura::where('presupuesto_id', $request->presupuesto_id)
                ->where('estado', '!=', 'anulada')
                ->first();
            if ($yaFacturado) {
                $this->liberarCandados();
                return redirect()->route('presupuestos.index')->with('error',
                    'El presupuesto ya fue facturado (' . $yaFacturado->numeroFormateado() . '). No se puede facturar dos veces.');
            }
        }

        $cbteTipo        = (int) $request->tipo;
        $condIvaEm       = \App\Models\Configuracion::get('empresa_condicion_iva', '');
        $condicionEmisor = $condIvaEm === 'responsable_inscripto' ? 'responsable_inscripto' : 'monotributo';

        // Cliente (se usa en la guardia de compatibilidad y para la condición IVA)
        $clienteFac = Cliente::findOrFail($request->cliente_id);

        // Validar tipo según condición del EMISOR
        if ($condicionEmisor === 'monotributo' && !in_array($cbteTipo, [11, 13])) {
            return $this->volverConBorrador($request,
                'Como Monotributista solo podés emitir Factura C y Nota de Crédito C.'
            );
        }

        // Guardia de compatibilidad letra ↔ condición del receptor (ARCA rechaza
        // combinaciones inválidas ahora que se informa CondicionIVAReceptorId).
        if ($condicionEmisor === 'responsable_inscripto') {
            $cond  = $clienteFac->condicion_iva;
            $letra = in_array($cbteTipo, [1, 3]) ? 'A' : (in_array($cbteTipo, [6, 8]) ? 'B' : null);

            // A monotributistas, por ahora, se factura desde la app de ARCA.
            if ($cond === 'monotributo') {
                return $this->volverConBorrador($request,
                    'A clientes monotributistas, por ahora, facturá directamente desde la app de ARCA.'
                );
            }
            // Factura/NC A → solo Responsable Inscripto.
            if ($letra === 'A' && $cond !== 'responsable_inscripto') {
                return $this->volverConBorrador($request,
                    'Factura A solo se puede emitir a Responsables Inscriptos. ' .
                    'Este cliente es ' . ($clienteFac->condicionIvaLabel() ?: 'sin condición IVA registrada') . '.'
                );
            }
            // Factura/NC B → Consumidor Final o Exento (nunca a un Responsable Inscripto).
            if ($letra === 'B' && !in_array($cond, ['consumidor_final', 'exento', null], true)) {
                return $this->volverConBorrador($request,
                    'Factura B se emite a Consumidor Final o Exento. A un Responsable Inscripto corresponde Factura A.'
                );
            }
        }

        // La NC tiene que apuntar a un comprobante de su misma letra: NC-A → Factura A,
        // NC-B → Factura B, NC-C → Factura C. Si no, ARCA rechaza (o peor, acredita
        // contra un comprobante que no es).
        if ($isNC) {
            $letraEsperada = array_search($cbteTipo, self::TIPO_NC, true);
            if ($letraEsperada !== false && (int) $request->nc_tipo !== $letraEsperada) {
                return $this->volverConBorrador($request,
                    'Una ' . (new Factura(['tipo' => $cbteTipo]))->tipoLabel() . ' debe referenciar una ' .
                    (new Factura(['tipo' => $letraEsperada]))->tipoLabel() . ', no una ' .
                    (new Factura(['tipo' => (int) $request->nc_tipo]))->tipoLabel() . '.');
            }
        }

        // ARCA no permite que el DocNro del receptor sea igual al CUIT del emisor
        if ((int) $request->doc_nro === (int) \App\Models\Configuracion::get('empresa_cuit')) {
            return $this->volverConBorrador($request,
                'El CUIT del receptor no puede ser igual al CUIT del emisor (ARCA error 10069).'
            );
        }

        // Ítems para el cálculo fiscal: el precio_unitario es SIEMPRE final (IVA incluido).
        $sinIva    = in_array($cbteTipo, [11, 13]); // Factura C / NC C: sin IVA
        $itemsCalc = [];
        foreach ($request->items as $it) {
            [$ivaTipo, $ali] = $this->parseIva($it['iva'] ?? '21', $sinIva);
            $itemsCalc[] = [
                'final'    => round((float) $it['cantidad'] * (float) $it['precio_unitario'], 2),
                'iva_tipo' => $ivaTipo,
                'alicuota' => $ali,
            ];
        }

        // Solicitar CAE a ARCA
        try {
            $arca = new ArcaService();
            $imp  = $arca->importesDesdeItems($itemsCalc, $cbteTipo);
            $impNeto = $imp['neto'];
            $impIva  = $imp['iva'];
            $total   = $imp['total'];

            $arcaData = [
                'CbteTipo' => $cbteTipo,
                'Concepto' => (int) $request->concepto,
                'DocTipo'  => (int) $request->doc_tipo,
                'DocNro'   => (int) ($request->doc_nro ?? 0),
                'Items'    => $itemsCalc,
                // Condición IVA del receptor — obligatoria para AFIP (RG 5616/2024)
                'CondicionIVAReceptor' => $clienteFac->condicion_iva,
            ];

            // Para Notas de Crédito: referencia al comprobante original
            if ($isNC) {
                $arcaData['NcTipo']   = (int) $request->nc_tipo;
                $arcaData['NcPtoVta'] = (int) $request->nc_pto_vta;
                $arcaData['NcNro']    = (int) $request->nc_nro;
            }

            $resultado = $arca->solicitarCAE($arcaData);
        } catch (\Exception $e) {
            // Puede ser un timeout / SoapFault con el CAE YA otorgado del otro lado.
            // Reintentar a ciegas duplicaría: avisamos que se verifique primero.
            return $this->volverConBorrador($request,
                'Error ARCA: ' . $e->getMessage() .
                ' — IMPORTANTE: si fue un corte de conexión o un timeout, el CAE puede haberse otorgado igual. ' .
                'Verificá el último número emitido en ARCA antes de reintentar.');
        }

        // Crear factura en DB
        $factura = Factura::create([
            'presupuesto_id'  => $request->presupuesto_id ?: null,
            'cliente_id'      => $request->cliente_id,
            'created_by'      => auth()->id(),
            'tipo'            => $cbteTipo,
            'punto_venta'     => $arca->getPtoVta(),
            'numero'          => $resultado->numero,
            'fecha'           => now()->toDateString(),
            'cae'             => $resultado->cae,
            'cae_vencimiento' => $resultado->cae_vencimiento,
            'estado'          => 'emitida',
            'doc_tipo'        => (int) $request->doc_tipo,
            'doc_nro'         => $request->doc_nro ?: null,
            'concepto'        => (int) $request->concepto,
            'imp_neto'        => $impNeto,
            'imp_iva'         => $impIva,
            'imp_total'       => $total,
            'observaciones'   => $request->observaciones,
            'forma_pago'      => $request->forma_pago ?: null,
            'nc_tipo'         => $isNC ? (int) $request->nc_tipo    : null,
            'nc_pto_vta'      => $isNC ? (int) $request->nc_pto_vta : null,
            'nc_nro'          => $isNC ? (int) $request->nc_nro     : null,
        ]);

        // Guardar ítems (precio_unitario NETO; subtotal = neto de la línea)
        foreach ($request->items as $i => $it) {
            $subtotal = round((float)$it['cantidad'] * (float)$it['precio_unitario'], 2);
            FacturaItem::create([
                'factura_id'      => $factura->id,
                'descripcion'     => $it['descripcion'],
                'cantidad'        => $it['cantidad'],
                'unidad'          => $it['unidad'] ?? 'unidad',
                'precio_unitario' => $it['precio_unitario'],
                'subtotal'        => $subtotal,
                'alicuota_iva'    => $itemsCalc[$i]['alicuota'],
                'iva_tipo'        => $itemsCalc[$i]['iva_tipo'],
                'orden'           => $i,
            ]);
        }

        // Si venía de un presupuesto, marcarlo como facturado
        if ($request->presupuesto_id) {
            Presupuesto::find($request->presupuesto_id)?->update(['estado' => 'aprobado']);
        }

        // Emisión exitosa: el borrador (si existía) ya no hace falta
        if ($request->filled('borrador_id')) {
            FacturaBorrador::find($request->borrador_id)?->delete();
        }

        // Recién ahora, con la factura y sus ítems ya guardados, anotamos a qué
        // factura corresponde el token: así un reenvío del mismo formulario va a
        // verla en vez de emitir otra. El candado NO se libera (vive hasta el TTL).
        // Va en try/catch a propósito: el CAE ya está otorgado y la factura guardada,
        // un problema de cache acá no puede tirar abajo una emisión válida.
        if ($emisionToken !== '') {
            try {
                $this->cacheRepo()->put('emision-hecha:' . $emisionToken, $factura->id, self::EMISION_TTL);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('No se pudo registrar el token de emisión: ' . $e->getMessage());
            }
        }

        return redirect()->route('facturas.show', $factura->id)
            ->with('success', 'Factura ' . $factura->numeroFormateado() . ' emitida. CAE: ' . $factura->cae);
    }

    /**
     * Elimina un borrador manualmente desde el listado.
     */
    public function destroyBorrador(FacturaBorrador $borrador)
    {
        $borrador->delete();
        return back()->with('success', 'Borrador eliminado.');
    }

    public function show(Factura $factura)
    {
        $factura->load(['cliente', 'presupuesto', 'items', 'createdBy', 'cobros.createdBy', 'remitos']);
        return view('facturas.show', compact('factura'));
    }

    public function print(Factura $factura)
    {
        $factura->load(['cliente', 'items']);
        return view('facturas.print', compact('factura'));
    }

    /**
     * PDF A4 generado con mPDF (paginación nativa, encabezado/pie repetidos).
     * ?download=1 fuerza la descarga; sin eso se muestra inline en el navegador.
     */
    public function pdf(Request $request, Factura $factura)
    {
        $service = new \App\Services\FacturaPdfService();
        $mpdf    = $service->generar($factura);
        $pdf     = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

        $nombre      = $service->nombreArchivo($factura) . '.pdf';
        $disposition = $request->boolean('download') ? 'attachment' : 'inline';

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => $disposition . '; filename="' . $nombre . '"',
        ]);
    }

    // ── Vista previa (sin llamar a ARCA) ─────────────────────────────────

    /**
     * Vista previa de la factura ANTES de emitir (sin llamar a ARCA).
     * Genera el MISMO PDF que la factura emitida (FacturaPdfService) sobre una
     * Factura en memoria sin CAE ni número → la previa es visualmente idéntica.
     */
    public function preview(Request $request)
    {
        $tipo = (int) $request->tipo;

        // Armar los ítems en memoria (sin guardar). El precio_unitario es FINAL (IVA incluido).
        $sinIva    = in_array($tipo, [11, 13]);
        $items     = collect();
        $itemsCalc = [];
        foreach ($request->items ?? [] as $i => $it) {
            if (trim((string) ($it['descripcion'] ?? '')) === '') continue;
            $cant = (float) ($it['cantidad'] ?? 0);
            $pu   = (float) ($it['precio_unitario'] ?? 0);
            $sub  = round($cant * $pu, 2);
            [$ivaTipo, $ali] = $this->parseIva($it['iva'] ?? '21', $sinIva);
            $itemsCalc[] = ['final' => $sub, 'iva_tipo' => $ivaTipo, 'alicuota' => $ali];
            $items->push(new FacturaItem([
                'descripcion'     => $it['descripcion'] ?? '',
                'cantidad'        => $cant ?: 1,
                'unidad'          => $it['unidad'] ?? 'unidad',
                'precio_unitario' => $pu,
                'subtotal'        => $sub,
                'alicuota_iva'    => $ali,
                'iva_tipo'        => $ivaTipo,
                'orden'           => $i,
            ]));
        }

        $imp     = (new ArcaService())->importesDesdeItems($itemsCalc, $tipo);
        $impNeto = $imp['neto'];
        $impIva  = $imp['iva'];
        $total   = $imp['total'];

        $ptoVta = (int) (\App\Models\Configuracion::get('arca_punto_venta') ?: config('arca.punto_venta', 1));

        $factura = new Factura([
            'cliente_id'   => $request->cliente_id,
            'tipo'         => $tipo,
            'punto_venta'  => $ptoVta,
            'numero'       => 0,
            'fecha'        => $request->fecha ?: now()->toDateString(),
            'cae'          => null,
            'estado'       => 'pendiente',
            'doc_tipo'     => (int) $request->doc_tipo,
            'doc_nro'      => $request->doc_nro ?: null,
            'concepto'     => (int) $request->concepto,
            'imp_neto'     => $impNeto,
            'imp_iva'      => $impIva,
            'imp_total'    => $total,
            'observaciones'=> $request->observaciones,
            'nc_tipo'      => $request->nc_tipo    ?: null,
            'nc_pto_vta'   => $request->nc_pto_vta ?: null,
            'nc_nro'       => $request->nc_nro     ?: null,
        ]);

        // Cargar relaciones en memoria para que el servicio no toque la DB.
        $cliente = $request->cliente_id
            ? Cliente::find($request->cliente_id)
            : new Cliente(['nombre' => 'Consumidor Final']);
        $factura->setRelation('cliente', $cliente);
        $factura->setRelation('items', $items);

        $service = new \App\Services\FacturaPdfService();
        $mpdf    = $service->generar($factura, preview: true);
        $pdf     = $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);

        return response($pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="previsualizacion.pdf"',
        ]);
    }

    // ── Desde presupuesto ─────────────────────────────────────────────────

    /**
     * Inicia el flujo de facturación desde un presupuesto aprobado.
     */
    public function fromPresupuesto(Presupuesto $presupuesto)
    {
        // Ni siquiera abrimos el formulario si el presupuesto ya tiene factura.
        $yaFacturado = Factura::where('presupuesto_id', $presupuesto->id)
            ->where('estado', '!=', 'anulada')
            ->first();

        if ($yaFacturado) {
            return redirect()->route('facturas.show', $yaFacturado->id)->with('error',
                'Ese presupuesto ya fue facturado (' . $yaFacturado->numeroFormateado() . ').');
        }

        return redirect()->route('facturas.create', ['presupuesto_id' => $presupuesto->id]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    /**
     * Guarda la carga actual como borrador en DB y vuelve al formulario con el
     * mensaje de error. Así, si ARCA falla (o cualquier regla rechaza), nada se
     * pierde: queda en `factura_borradores` y se puede retomar aunque se cierre
     * la pestaña. Reusa el borrador existente si el form ya traía un borrador_id.
     */
    private function volverConBorrador(Request $request, string $mensaje)
    {
        // La emisión no se concretó → soltamos los candados para que el usuario
        // pueda corregir y reintentar sin esperar el TTL.
        $this->liberarCandados();

        $borrador = $this->guardarBorrador($request, $mensaje);

        return back()
            ->withInput(array_merge(
                $request->except(['_token', 'borrador_id', 'emision_token']),
                ['borrador_id' => $borrador->id]
            ))
            ->with('error', $mensaje)
            ->with('info', 'Los datos quedaron guardados como borrador — no perdiste la carga.');
    }

    /**
     * Traduce el valor del selector de IVA del formulario a [iva_tipo, alícuota].
     * Valores: 0/2.5/5/10.5/21/27 (gravado), 'exento', 'no_gravado'.
     * En Factura C / NC C no hay IVA → siempre gravado 0.
     */
    private function parseIva(string $v, bool $sinIva): array
    {
        if ($sinIva)               return ['gravado', 0.0];
        if ($v === 'exento')       return ['exento', 0.0];
        if ($v === 'no_gravado')   return ['no_gravado', 0.0];
        return ['gravado', (float) $v];
    }

    private function guardarBorrador(Request $request, ?string $error = null): FacturaBorrador
    {
        // Total estimado solo para mostrar en el listado
        $total = 0;
        foreach ((array) $request->items as $it) {
            $total += round((float) ($it['cantidad'] ?? 0) * (float) ($it['precio_unitario'] ?? 0), 2);
        }

        $datos = $request->except(['_token', 'borrador_id', 'emision_token']);

        $borrador = $request->filled('borrador_id')
            ? FacturaBorrador::find($request->borrador_id)
            : null;

        if ($borrador) {
            $borrador->update([
                'cliente_id' => $request->cliente_id ?: null,
                'datos'      => $datos,
                'total'      => $total,
                'error'      => $error,
            ]);
        } else {
            $borrador = FacturaBorrador::create([
                'created_by' => auth()->id(),
                'cliente_id' => $request->cliente_id ?: null,
                'datos'      => $datos,
                'total'      => $total,
                'error'      => $error,
            ]);
        }

        return $borrador;
    }

    // ── Anti doble emisión ─────────────────────────────────────

    /**
     * Toma un candado atómico para la emisión en curso. Devuelve false si ya
     * lo tiene otro request (= es un envío duplicado y no debe llegar a ARCA).
     */
    /**
     * Repositorio de cache directo.
     *
     * OJO: en contexto tenant el binding `cache` NO es el CacheManager de Laravel
     * sino Stancl\Tenancy\CacheManager, cuyo __call() reescribe cualquier método
     * no declarado como `->tags([...])->metodo(...)`. Como en Laravel 12
     * Illuminate\Cache\DatabaseStore dejó de extender TaggableStore, un
     * `Cache::put()` / `Cache::lock()` ahí tira BadMethodCallException
     * ("This cache store does not support tagging").
     *
     * `store()` SÍ está declarado en el manager, así que devuelve un Repository
     * normal y esquiva el __call. La aislación por empresa no se pierde: la tabla
     * cache_locks vive en la DB del tenant y además va el prefijo de cache.
     */
    private function cacheRepo(): \Illuminate\Cache\Repository
    {
        return Cache::store(config('cache.default'));
    }

    private function tomarCandado(string $clave): bool
    {
        try {
            $lock = $this->cacheRepo()->getStore()->lock('emision:' . $clave, self::EMISION_TTL);

            if (! $lock->get()) {
                return false;
            }

            $this->locks[] = $lock;
        } catch (\Throwable $e) {
            // Si el store de candados no está disponible preferimos dejar pasar
            // la emisión (comportamiento de siempre) antes que bloquear el módulo.
            \Illuminate\Support\Facades\Log::warning('No se pudo tomar el candado de emisión: ' . $e->getMessage());
        }

        return true;
    }

    /** Suelta los candados de esta emisión (solo cuando NO se emitió nada). */
    private function liberarCandados(): void
    {
        foreach ($this->locks as $lock) {
            optional($lock)->release();
        }

        $this->locks = [];
    }

    /**
     * Respuesta a un envío duplicado del mismo formulario. Si el primer envío
     * ya terminó bien, mandamos a ver esa factura; si todavía está en vuelo,
     * avisamos que espere en vez de dejar que emita otra.
     */
    private function respuestaDuplicada(string $token)
    {
        try {
            $id = $this->cacheRepo()->get('emision-hecha:' . $token);
        } catch (\Throwable $e) {
            $id = null;
        }

        if ($id && ($factura = Factura::find($id))) {
            return redirect()->route('facturas.show', $factura->id)->with('error',
                'Ese comprobante ya se había emitido (' . $factura->tipoLabel() . ' ' .
                $factura->numeroFormateado() . '). Se ignoró el envío duplicado.');
        }

        return redirect()->route('facturas.index')->with('error',
            'La emisión de ese comprobante ya estaba en curso — se ignoró el envío duplicado. ' .
            'Revisá el listado antes de volver a emitir.');
    }

    // ── Nota de crédito desde un comprobante existente ─────────────────

    /**
     * Buscador de comprobantes a acreditar (Select2 AJAX del formulario de NC).
     * Solo facturas no anuladas: una nota de crédito no se acredita a sí misma.
     */
    public function buscar(Request $request)
    {
        $q      = trim((string) $request->q);
        $digits = preg_replace('/\D/', '', $q);

        $facturas = Factura::with('cliente')
            ->whereIn('tipo', [1, 6, 11])
            ->where('estado', '!=', 'anulada')
            ->when($q !== '', function ($query) use ($q, $digits) {
                $query->where(function ($sub) use ($q, $digits) {
                    // Acepta tanto "37" como el formato que muestra la app,
                    // "0003-00000037" (4 dígitos de PV + 8 de número).
                    if ($digits !== '') {
                        $numero = (int) (strlen($digits) > 8 ? substr($digits, -8) : $digits);
                        if ($numero > 0) {
                            $sub->orWhere('numero', $numero);
                        }
                    }
                    $sub->orWhereHas('cliente', fn ($c) => $c->where('nombre', 'like', '%' . $q . '%'));
                });
            })
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return response()->json($facturas->map(fn (Factura $f) => [
            'id'   => $f->id,
            'text' => $f->tipoLabel() . ' ' . $f->numeroFormateado()
                    . ' · ' . ($f->cliente->nombre ?? 'sin cliente')
                    . ' · $' . number_format((float) $f->imp_total, 2, ',', '.')
                    . ' · ' . $f->fecha->format('d/m/Y'),
        ]));
    }

    /**
     * Datos completos de un comprobante para precargar una NC sin recargar la
     * página (lo consume el selector "comprobante a acreditar" del formulario).
     */
    public function datos(Factura $factura)
    {
        // Misma regla que en create(): solo se acredita una factura vigente.
        abort_unless($factura->esFactura() && $factura->estado !== 'anulada', 404);

        $factura->load(['cliente', 'items']);

        return response()->json(array_merge($this->datosNcDesde($factura), [
            'etiqueta' => $factura->tipoLabel() . ' ' . $factura->numeroFormateado(),
            'cliente'  => [
                'id'            => $factura->cliente_id,
                'nombre'        => $factura->cliente?->nombre,
                'cuit'          => $factura->cliente?->cuit,
                'condicion_iva' => $factura->cliente?->condicion_iva,
            ],
        ]));
    }

    /**
     * Arma los campos del formulario de NC a partir del comprobante original.
     * Las claves son las mismas que usa el form, así sirve tanto para old()
     * (botón "Nota de crédito") como para el JSON del selector.
     */
    private function datosNcDesde(Factura $factura): array
    {
        // La NC espeja la letra del original: A → NC-A (3), B → NC-B (8), C → NC-C (13).
        // Sin `default`: si llega un tipo que no es factura preferimos el error
        // ruidoso acá antes que un tipo inválido silencioso en el formulario.
        $tipoNc = self::TIPO_NC[(int) $factura->tipo]
            ?? throw new \InvalidArgumentException('No se puede acreditar un comprobante tipo ' . $factura->tipo);

        return [
            'cliente_id'    => $factura->cliente_id,
            'tipo'          => $tipoNc,
            'concepto'      => (int) $factura->concepto,
            'doc_tipo'      => (int) $factura->doc_tipo,
            'doc_nro'       => $factura->doc_nro,
            'observaciones' => 'Nota de crédito por ' . $factura->tipoLabel() . ' ' . $factura->numeroFormateado() . '.',
            // Referencia fiscal que ARCA exige en la NC (CbtesAsoc)
            'nc_tipo'       => (int) $factura->tipo,
            'nc_pto_vta'    => (int) $factura->punto_venta,
            'nc_nro'        => (int) $factura->numero,
            'items'         => $factura->items->map(fn (FacturaItem $it) => [
                'descripcion'     => $it->descripcion,
                'cantidad'        => $it->cantidad,
                'unidad'          => $it->unidad ?: 'unidad',
                'precio_unitario' => $it->precio_unitario,
                'iva'             => $this->ivaFormulario($it),
            ])->all(),
        ];
    }

    /** Valor del selector de IVA del formulario a partir del ítem guardado. */
    private function ivaFormulario(FacturaItem $item): string
    {
        if ($item->iva_tipo === 'exento')     return 'exento';
        if ($item->iva_tipo === 'no_gravado') return 'no_gravado';

        // 21.00 → "21", 10.50 → "10.5" (los value del <select>)
        $ali = rtrim(rtrim(number_format((float) $item->alicuota_iva, 2, '.', ''), '0'), '.');

        return $ali === '' ? '0' : $ali;
    }

}

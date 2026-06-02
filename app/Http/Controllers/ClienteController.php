<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cliente;
use App\Models\ListaPrecio;
use App\Services\ArcaService;

class ClienteController extends Controller
{
    public function index()
    {
        $clientes = Cliente::with('listaPrecio')->orderBy('nombre')->get();
        return view('clientes.index', compact('clientes'));
    }

    public function create()
    {
        $listas = ListaPrecio::orderBy('nombre')->get();
        return view('clientes.create', compact('listas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'cuit'            => 'nullable|string|max:20',
            'condicion_iva'   => 'nullable|in:responsable_inscripto,monotributo,exento,consumidor_final',
            'telefono'        => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
            'direccion'       => 'nullable|string',
            'lista_precio_id' => 'required|exists:lista_precios,id',
        ]);

        Cliente::create($request->only([
            'nombre', 'cuit', 'condicion_iva',
            'telefono', 'email', 'direccion', 'lista_precio_id',
        ]));

        return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
    }

    public function show(Cliente $cliente)
    {
        $cliente->load('listaPrecio', 'ordenesTrabajo');
        return view('clientes.show', compact('cliente'));
    }

    public function edit(Cliente $cliente)
    {
        $listas = ListaPrecio::orderBy('nombre')->get();
        return view('clientes.edit', compact('cliente', 'listas'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nombre'          => 'required|string|max:255',
            'cuit'            => 'nullable|string|max:20',
            'condicion_iva'   => 'nullable|in:responsable_inscripto,monotributo,exento,consumidor_final',
            'telefono'        => 'nullable|string|max:50',
            'email'           => 'nullable|email|max:255',
            'direccion'       => 'nullable|string',
            'lista_precio_id' => 'required|exists:lista_precios,id',
        ]);

        $cliente->update($request->only([
            'nombre', 'cuit', 'condicion_iva',
            'telefono', 'email', 'direccion', 'lista_precio_id',
        ]));

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.');
    }

    /**
     * Búsqueda AJAX para Select2.
     * GET /clientes/search?q=term
     */
    public function search(Request $request)
    {
        $term = $request->get('q');

        $clientes = Cliente::where('nombre', 'like', "%{$term}%")
            ->orderBy('nombre')
            ->limit(10)
            ->get();

        return response()->json(
            $clientes->map(fn ($c) => [
                'id'            => $c->id,
                'text'          => $c->nombre,
                'cuit'          => $c->cuit,
                'condicion_iva' => $c->condicion_iva,
            ])
        );
    }

    /**
     * Consulta CUIT contra el Padrón A13 de ARCA.
     * GET /clientes/consultar-cuit?cuit=XXXXXXXXXXX
     */
    public function consultarCuit(Request $request)
    {
        $cuit = preg_replace('/\D/', '', $request->get('cuit', ''));

        if (strlen($cuit) !== 11) {
            return response()->json(['error' => 'El CUIT debe tener 11 dígitos.'], 422);
        }

        try {
            $datos = (new ArcaService())->consultarPadron($cuit);
            return response()->json($datos);
        } catch (\Exception $e) {
            // En debug devolvemos el detalle completo para diagnosticar
            $payload = ['error' => $e->getMessage()];
            if (config('app.debug') && $request->has('_debug')) {
                $payload['trace'] = collect(explode("\n", $e->getTraceAsString()))->take(8)->all();
            }
            return response()->json($payload, 422);
        }
    }

    /**
     * Solo para diagnóstico en dev — devuelve la respuesta raw de ARCA padrón.
     * GET /clientes/debug-padron?cuit=XXXXXXXXXXX
     */
    public function debugPadron(Request $request)
    {
        abort_unless(config('app.debug'), 404);

        $cuit = preg_replace('/\D/', '', $request->get('cuit', ''));
        $arca = new ArcaService();
        $out  = ['cuit' => $cuit];

        // ── Constancia de Inscripción = A5 renombrado ─────────────────
        // ws_sr_constancia_inscripcion es el nuevo nombre de ws_sr_padron_a5 en ARCA.
        // El WSDL sigue siendo personaServiceA5 — solo cambió el alias del servicio.
        try {
            $authConst = $arca->getAuthPublic('ws_sr_constancia_inscripcion');
            $cc = new \SoapClient(
                'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA5?WSDL',
                ['soap_version' => SOAP_1_1, 'trace' => true, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_BOTH]
            );
            $rc = $cc->getPersona([
                'token'            => $authConst['token'],
                'sign'             => $authConst['sign'],
                'cuitRepresentada' => (int) $arca->getCuit(),
                'idPersona'        => (int) $cuit,
            ]);
            $out['constancia'] = ['ok' => true, 'raw' => json_decode(json_encode($rc), true)];
        } catch (\Exception $e) {
            $out['constancia'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        // ── A13 raw (fallback: nombre + domicilio sin IVA) ───────────
        try {
            $auth13 = $arca->getAuthPublic('ws_sr_padron_a13');
            $c13    = new \SoapClient(
                'https://aws.afip.gov.ar/sr-padron/webservices/personaServiceA13?WSDL',
                ['soap_version' => SOAP_1_1, 'trace' => true, 'exceptions' => true, 'cache_wsdl' => WSDL_CACHE_BOTH]
            );
            $r13 = $c13->getPersona([
                'token' => $auth13['token'], 'sign' => $auth13['sign'],
                'cuitRepresentada' => (int) $arca->getCuit(), 'idPersona' => (int) $cuit,
            ]);
            $out['a13'] = ['ok' => true, 'raw' => json_decode(json_encode($r13), true)];
        } catch (\Exception $e) {
            $out['a13'] = ['ok' => false, 'error' => $e->getMessage()];
        }

        // ── Resultado final (lo que llega al formulario del cliente) ──
        try {
            $out['parseado'] = $arca->consultarPadron($cuit);
        } catch (\Exception $e) {
            $out['parseado'] = ['error' => $e->getMessage()];
        }

        return response()->json($out);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Fichada;
use App\Services\HorasService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FichadaController extends Controller
{
    public function __construct(private HorasService $horasService)
    {
    }

    // ======================
    // KIOSCO (tablet) - SIN LOGIN
    // ======================

    public function showKiosk()
    {
        return view('fichadas.kiosk');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => ['required', 'string'],
            'tipo'   => ['required', 'in:entrada,salida,pausa_inicio,pausa_fin'],
            'foto'   => ['nullable', 'string'],  // dataURL capturada por la tablet
        ]);

        $empleado = Empleado::where('codigo', $data['codigo'])
            ->where('activo', true)
            ->first();

        if (!$empleado) {
            return back()
                ->withInput()
                ->with('error', 'Código no válido o empleado inactivo.');
        }

        $momento = Carbon::now();

        // No permitir dos ENTRADA seguidas
        $ultima = Fichada::where('empleado_id', $empleado->id)
            ->orderByDesc('momento')
            ->first();

        if ($ultima && $ultima->tipo === 'entrada' && $data['tipo'] === 'entrada') {
            return back()
                ->withInput()
                ->with('error', 'Ya registraste una ENTRADA. Marcá primero SALIDA o PAUSA.');
        }

        Fichada::create([
            'empleado_id' => $empleado->id,
            'tipo'        => $data['tipo'],
            'momento'     => $momento,
            'origen'      => 'tablet-recepcion',
            'foto'        => $this->guardarFoto($request->input('foto'), $empleado),
        ]);

        $tipoLabel = match ($data['tipo']) {
            'entrada'      => 'ENTRADA',
            'salida'       => 'SALIDA',
            'pausa_inicio' => 'INICIO PAUSA',
            'pausa_fin'    => 'FIN PAUSA',
        };

        return back()->with(
            'ok',
            "✅ {$empleado->nombre_completo} – {$tipoLabel} registrada a las " . $momento->format('H:i')
        );
    }

    /**
     * Guarda la foto (dataURL base64) capturada por la tablet al fichar.
     * Best-effort: si no vino o es inválida, devuelve null (la fichada igual se registra).
     */
    private function guardarFoto(?string $dataUrl, Empleado $empleado): ?string
    {
        if (!$dataUrl || !preg_match('#^data:image/(\w+);base64,#', $dataUrl, $m)) {
            return null;
        }

        $bin = base64_decode(substr($dataUrl, strpos($dataUrl, ',') + 1), true);
        if ($bin === false || strlen($bin) > 3_000_000) {  // cap 3 MB
            return null;
        }

        $ext  = $m[1] === 'jpeg' ? 'jpg' : $m[1];
        $ruta = 'fichadas/' . $empleado->id . '/' . Carbon::now()->format('Ymd_His')
              . '_' . \Illuminate\Support\Str::random(6) . '.' . $ext;

        \Illuminate\Support\Facades\Storage::disk('public')->put($ruta, $bin);

        return $ruta;
    }

    // ======================
    // RRHH
    // ======================

    public function dashboard()
    {
        $hoy = Carbon::today();

        $totalHoy  = Fichada::whereBetween('momento', [$hoy->copy()->startOfDay(), $hoy->copy()->endOfDay()])->count();
        $empleados = Empleado::where('activo', true)->count();

        return view('rrhh.dashboard', compact('totalHoy', 'empleados', 'hoy'));
    }

    public function index(Request $request)
    {
        $query = Fichada::with('empleado')->orderByDesc('momento');

        if ($request->filled('empleado_id')) {
            $query->where('empleado_id', $request->empleado_id);
        }

        if ($request->filled('desde')) {
            $query->where('momento', '>=', Carbon::parse($request->desde)->startOfDay());
        }

        if ($request->filled('hasta')) {
            $query->where('momento', '<=', Carbon::parse($request->hasta)->endOfDay());
        }

        $fichadas  = $query->paginate(50);
        $empleados = Empleado::orderBy('nombre')->orderBy('apellido')->get();

        return view('rrhh.fichadas.index', compact('fichadas', 'empleados'));
    }

    public function hoy()
    {
        $hoy = Carbon::today();

        $fichadas = Fichada::with('empleado')
            ->whereBetween('momento', [$hoy->copy()->startOfDay(), $hoy->copy()->endOfDay()])
            ->orderBy('momento')
            ->get();

        return view('rrhh.fichadas.hoy', compact('fichadas', 'hoy'));
    }

    public function porEmpleado(Empleado $empleado, Request $request)
    {
        $desde = $request->filled('desde')
            ? Carbon::parse($request->desde)->startOfDay()
            : Carbon::today()->startOfMonth();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->hasta)->endOfDay()
            : Carbon::today()->endOfMonth();

        $calc = $this->horasService->calcular($empleado, $desde, $hasta);

        return view('rrhh.fichadas.por-empleado', [
            'empleado'        => $empleado,
            'desde'           => $desde,
            'hasta'           => $hasta,
            'resumen'         => $calc['resumen'],
            'totalTrabMin'    => $calc['totalTrabMin'],
            'totalExtrasMin'  => $calc['totalExtrasMin'],
            'totalNormMin'    => $calc['totalNormMin'],
            'horasBaseSemana' => $calc['horasBaseSemana'],
        ]);
    }
}

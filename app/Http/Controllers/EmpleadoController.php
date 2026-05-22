<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\EmpleadoDetalle;
use App\Models\EmpleadoPago;
use App\Services\HorasService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EmpleadoController extends Controller
{
    public function __construct(private HorasService $horasService)
    {
    }

    public function index()
    {
        $empleados = Empleado::orderBy('nombre')->orderBy('apellido')->paginate(20);
        return view('rrhh.empleados.index', compact('empleados'));
    }

    public function create()
    {
        return view('rrhh.empleados.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre'           => ['required', 'string', 'max:255'],
            'apellido'         => ['nullable', 'string', 'max:255'],
            'codigo'           => ['required', 'string', 'max:50', 'unique:empleados,codigo'],
            'dni'              => ['nullable', 'string', 'max:20'],
            'cuil'             => ['nullable', 'string', 'max:20'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'fecha_ingreso'    => ['nullable', 'date'],
            'direccion'        => ['nullable', 'string', 'max:255'],
            'telefono'         => ['nullable', 'string', 'max:50'],
            'email'            => ['nullable', 'email', 'max:255'],
            'categoria'        => ['nullable', 'string', 'max:100'],
            'valor_hora'       => ['nullable', 'numeric'],
            'horas_jornada'    => ['nullable', 'integer', 'min:1', 'max:24'],
            'observaciones'    => ['nullable', 'string'],
        ]);

        $empleado = Empleado::create([
            'nombre'   => $data['nombre'],
            'apellido' => $data['apellido'] ?? null,
            'codigo'   => $data['codigo'],
            'activo'   => $request->has('activo'),
        ]);

        EmpleadoDetalle::create([
            'empleado_id'      => $empleado->id,
            'dni'              => $data['dni'] ?? null,
            'cuil'             => $data['cuil'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'fecha_ingreso'    => $data['fecha_ingreso'] ?? null,
            'direccion'        => $data['direccion'] ?? null,
            'telefono'         => $data['telefono'] ?? null,
            'email'            => $data['email'] ?? null,
            'categoria'        => $data['categoria'] ?? null,
            'valor_hora'       => $data['valor_hora'] ?? null,
            'horas_jornada'    => $data['horas_jornada'] ?? 8,
            'observaciones'    => $data['observaciones'] ?? null,
        ]);

        return redirect()->route('rrhh.empleados.index')->with('ok', 'Empleado creado correctamente.');
    }

    public function edit(Empleado $empleado)
    {
        $detalle = $empleado->detalle ?: new EmpleadoDetalle([
            'empleado_id'   => $empleado->id,
            'horas_jornada' => 8,
        ]);

        return view('rrhh.empleados.edit', compact('empleado', 'detalle'));
    }

    public function update(Request $request, Empleado $empleado)
    {
        $data = $request->validate([
            'nombre'           => ['required', 'string', 'max:255'],
            'apellido'         => ['nullable', 'string', 'max:255'],
            'codigo'           => ['required', 'string', 'max:50', 'unique:empleados,codigo,' . $empleado->id],
            'dni'              => ['nullable', 'string', 'max:20'],
            'cuil'             => ['nullable', 'string', 'max:20'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'fecha_ingreso'    => ['nullable', 'date'],
            'direccion'        => ['nullable', 'string', 'max:255'],
            'telefono'         => ['nullable', 'string', 'max:50'],
            'email'            => ['nullable', 'email', 'max:255'],
            'categoria'        => ['nullable', 'string', 'max:100'],
            'valor_hora'       => ['nullable', 'numeric'],
            'horas_jornada'    => ['nullable', 'integer', 'min:1', 'max:24'],
            'observaciones'    => ['nullable', 'string'],
        ]);

        $empleado->update([
            'nombre'   => $data['nombre'],
            'apellido' => $data['apellido'] ?? null,
            'codigo'   => $data['codigo'],
            'activo'   => $request->has('activo'),
        ]);

        $detalle = $empleado->detalle ?: new EmpleadoDetalle(['empleado_id' => $empleado->id]);
        $detalle->fill([
            'dni'              => $data['dni'] ?? null,
            'cuil'             => $data['cuil'] ?? null,
            'fecha_nacimiento' => $data['fecha_nacimiento'] ?? null,
            'fecha_ingreso'    => $data['fecha_ingreso'] ?? null,
            'direccion'        => $data['direccion'] ?? null,
            'telefono'         => $data['telefono'] ?? null,
            'email'            => $data['email'] ?? null,
            'categoria'        => $data['categoria'] ?? null,
            'valor_hora'       => $data['valor_hora'] ?? null,
            'horas_jornada'    => $data['horas_jornada'] ?? 8,
            'observaciones'    => $data['observaciones'] ?? null,
        ])->save();

        return redirect()->route('rrhh.empleados.index')->with('ok', 'Empleado actualizado.');
    }

    public function destroy(Empleado $empleado)
    {
        $empleado->activo = false;
        $empleado->save();
        $empleado->delete();

        return redirect()->route('rrhh.empleados.index')->with('ok', 'Empleado dado de baja.');
    }

    public function liquidar(Empleado $empleado, Request $request)
    {
        $desde = $request->filled('desde')
            ? Carbon::parse($request->desde)->startOfDay()
            : Carbon::today()->startOfMonth();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->hasta)->endOfDay()
            : Carbon::today()->endOfDay();

        $calc = $this->horasService->calcular($empleado, $desde, $hasta);

        $detalle        = $empleado->detalle;
        $valorHoraBase  = $detalle->valor_hora ?? 0;
        $valorHoraExtra = $valorHoraBase > 0 ? round($valorHoraBase * 1.5, 2) : 0;
        $montoNormal    = round($calc['horasNormales'] * $valorHoraBase, 2);
        $montoExtra     = round($calc['horasExtras'] * $valorHoraExtra, 2);
        $montoTotal     = $montoNormal + $montoExtra;

        $ultimosPagos = $empleado->pagos()->orderByDesc('created_at')->limit(5)->get();

        return view('rrhh.empleados.liquidar', [
            'empleado'               => $empleado,
            'desde'                  => $desde,
            'hasta'                  => $hasta,
            'horasNormales'          => $calc['horasNormales'],
            'horasExtras'            => $calc['horasExtras'],
            'valorHoraBase'          => $valorHoraBase,
            'valorHoraExtraSugerido' => $valorHoraExtra,
            'montoNormalSugerido'    => $montoNormal,
            'montoExtraSugerido'     => $montoExtra,
            'montoTotalSugerido'     => $montoTotal,
            'ultimosPagos'           => $ultimosPagos,
        ]);
    }

    public function registrarPago(Empleado $empleado, Request $request)
    {
        $data = $request->validate([
            'desde'             => ['required', 'date'],
            'hasta'             => ['required', 'date'],
            'horas_normales'    => ['required', 'numeric', 'min:0'],
            'horas_extras'      => ['required', 'numeric', 'min:0'],
            'monto_hora_normal' => ['required', 'numeric', 'min:0'],
            'monto_hora_extra'  => ['required', 'numeric', 'min:0'],
            'monto_total'       => ['required', 'numeric', 'min:0'],
            'observaciones'     => ['nullable', 'string'],
        ]);

        EmpleadoPago::create([
            'empleado_id'       => $empleado->id,
            'desde'             => $data['desde'],
            'hasta'             => $data['hasta'],
            'horas_normales'    => $data['horas_normales'],
            'horas_extras'      => $data['horas_extras'],
            'monto_hora_normal' => $data['monto_hora_normal'],
            'monto_hora_extra'  => $data['monto_hora_extra'],
            'monto_total'       => $data['monto_total'],
            'observaciones'     => $data['observaciones'] ?? null,
        ]);

        return redirect()
            ->route('rrhh.empleados.fichadas', [
                'empleado' => $empleado->id,
                'desde'    => $data['desde'],
                'hasta'    => $data['hasta'],
            ])
            ->with('ok', 'Pago registrado correctamente.');
    }
}

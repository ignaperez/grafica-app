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
            'horario_ingreso'  => ['nullable', 'date_format:H:i'],
            'horario_egreso'   => ['nullable', 'date_format:H:i'],
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
            'horario_ingreso'  => $data['horario_ingreso'] ?? null,
            'horario_egreso'   => $data['horario_egreso'] ?? null,
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
            'horario_ingreso'  => ['nullable', 'date_format:H:i'],
            'horario_egreso'   => ['nullable', 'date_format:H:i'],
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
            'horario_ingreso'  => $data['horario_ingreso'] ?? null,
            'horario_egreso'   => $data['horario_egreso'] ?? null,
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
        // Por defecto, la SEMANA actual: lunes a sábado.
        if ($request->filled('desde') && $request->filled('hasta')) {
            $desde = Carbon::parse($request->desde)->startOfDay();
            $hasta = Carbon::parse($request->hasta)->endOfDay();
        } else {
            $desde = Carbon::today()->startOfWeek(Carbon::MONDAY);
            $hasta = $desde->copy()->addDays(5)->endOfDay(); // lun … sáb
        }

        $calc = $this->horasService->calcular($empleado, $desde, $hasta);
        $coef = ParametroSueldoController::actuales();
        $vh   = (float) ($empleado->detalle->valor_hora ?? 0);

        // Bruto (normales): lun-vie × coef_normal + sábado × coef_sábado
        $montoBruto = round($vh * (
            $calc['horasNormalSemana'] * $coef['sueldo_coef_normal'] +
            $calc['horasSabado']       * $coef['sueldo_coef_sabado']
        ), 2);

        // Horas extras: (extra semana + extra sábado) × coef_extra + domingo × coef_domingo
        $montoExtras = round($vh * (
            ($calc['horasExtraSemana'] + $calc['horasExtraSabado']) * $coef['sueldo_coef_extra'] +
            $calc['horasDomingo'] * $coef['sueldo_coef_domingo']
        ), 2);

        // Adelantos pendientes hasta el fin del período
        $adelantos      = $empleado->adelantos()
            ->whereNull('empleado_pago_id')
            ->whereDate('fecha', '<=', $hasta)
            ->orderBy('fecha')->get();
        $totalAdelantos = round((float) $adelantos->sum('monto'), 2);

        $netoSugerido = round($montoBruto + $montoExtras - $totalAdelantos, 2);

        $ultimosPagos = $empleado->pagos()->orderByDesc('created_at')->limit(5)->get();

        return view('rrhh.empleados.liquidar', compact(
            'empleado', 'desde', 'hasta', 'calc', 'coef', 'vh',
            'montoBruto', 'montoExtras', 'adelantos', 'totalAdelantos', 'netoSugerido', 'ultimosPagos'
        ));
    }

    public function registrarPago(Empleado $empleado, Request $request)
    {
        $data = $request->validate([
            'desde'          => ['required', 'date'],
            'hasta'          => ['required', 'date'],
            'horas_normales' => ['required', 'numeric', 'min:0'],
            'horas_extras'   => ['required', 'numeric', 'min:0'],
            'monto_bruto'    => ['required', 'numeric', 'min:0'],
            'monto_extras'   => ['required', 'numeric', 'min:0'],
            'valor_hora'     => ['nullable', 'numeric', 'min:0'],
            'bonificaciones' => ['nullable', 'numeric', 'min:0'],
            'descuentos'     => ['nullable', 'numeric', 'min:0'],
            'observaciones'  => ['nullable', 'string'],
        ]);

        // Adelantos pendientes del período: se recalculan y se saldan (integridad).
        $adelantosPend  = $empleado->adelantos()
            ->whereNull('empleado_pago_id')
            ->whereDate('fecha', '<=', $data['hasta'])->get();
        $totalAdelantos = round((float) $adelantosPend->sum('monto'), 2);

        $bonif    = (float) ($data['bonificaciones'] ?? 0);
        $descu    = (float) ($data['descuentos'] ?? 0);
        $neto     = round($data['monto_bruto'] + $data['monto_extras'] + $bonif - $descu - $totalAdelantos, 2);

        $pago = EmpleadoPago::create([
            'empleado_id'       => $empleado->id,
            'desde'             => $data['desde'],
            'hasta'             => $data['hasta'],
            'horas_normales'    => $data['horas_normales'],
            'horas_extras'      => $data['horas_extras'],
            'monto_hora_normal' => $data['valor_hora'] ?? 0,
            'monto_hora_extra'  => 0,
            'monto_total'       => round($data['monto_bruto'] + $data['monto_extras'], 2),
            'bonificaciones'    => $bonif,
            'descuentos'        => $descu,
            'adelantos'         => $totalAdelantos,
            'neto'              => $neto,
            'observaciones'     => $data['observaciones'] ?? null,
        ]);

        // Saldar los adelantos incluidos en esta liquidación
        if ($adelantosPend->isNotEmpty()) {
            $empleado->adelantos()
                ->whereNull('empleado_pago_id')
                ->whereDate('fecha', '<=', $data['hasta'])
                ->update(['empleado_pago_id' => $pago->id]);
        }

        return redirect()
            ->route('rrhh.empleados.liquidar', ['empleado' => $empleado->id, 'desde' => $data['desde'], 'hasta' => $data['hasta']])
            ->with('ok', 'Pago registrado. Neto: $' . number_format($neto, 2, ',', '.'));
    }
}

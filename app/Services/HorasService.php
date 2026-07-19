<?php

namespace App\Services;

use App\Models\Empleado;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class HorasService
{
    /**
     * Calcula el resumen de horas trabajadas, normales y extras
     * para un empleado en un rango de fechas dado.
     *
     * @param  Empleado  $empleado
     * @param  Carbon    $desde
     * @param  Carbon    $hasta
     * @return array{
     *   resumen: array,
     *   totalTrabMin: int,
     *   totalNormMin: int,
     *   totalExtrasMin: int,
     *   horasNormales: float,
     *   horasExtras: float,
     *   horasBaseSemana: int,
     * }
     */
    public function calcular(Empleado $empleado, Carbon $desde, Carbon $hasta): array
    {
        $fichadas = $empleado->fichadas()
            ->whereBetween('momento', [$desde, $hasta])
            ->orderBy('momento')
            ->get();

        $detalle         = $empleado->detalle;
        $horasBaseSemana = $detalle->horas_jornada ?? 8;
        $minBaseSemana   = $horasBaseSemana * 60;
        $jornadaSabado   = (float) \App\Models\Configuracion::get('sueldo_jornada_sabado', 4);
        $minBaseSabado   = (int) round($jornadaSabado * 60);
        $toleranciaMin   = (int) \App\Models\Configuracion::get('sueldo_tolerancia_min', 10);
        $horarioIngreso  = $detalle->horario_ingreso ?? null; // 'HH:MM:SS' o null

        $resumen        = [];
        $totalTrabMin   = 0;
        $totalExtrasMin = 0;
        $totalNormMin   = 0;

        // Desglose por categoría (minutos)
        $catNormal   = 0;  // lun-vie, hasta jornada
        $catSabado   = 0;  // sábado, hasta jornada sábado
        $catDomingo  = 0;  // domingo/feriado, todo
        $extraSemana = 0;  // lun-vie, sobre jornada
        $extraSabado = 0;  // sábado, sobre jornada sábado

        // Tardanzas (informativo)
        $tardanzasMin  = 0;
        $tardanzasDias = 0;

        $porDia = $fichadas->groupBy(fn ($f) => $f->momento->toDateString());

        foreach ($porDia as $fecha => $items) {
            $items = $items->sortBy('momento')->values();

            $minTrabajados = $this->calcularMinutosTrabajados($items);

            $dow = Carbon::parse($fecha)->dayOfWeekIso; // 1=lun, 7=dom

            // Categorizar por tipo de día
            if ($dow === 7) {                 // domingo (feriados: a futuro)
                $catDomingo += $minTrabajados;
                $minBaseDia  = 0;
                $minExtras   = $minTrabajados;
            } elseif ($dow === 6) {           // sábado
                $base = min($minTrabajados, $minBaseSabado);
                $ext  = max($minTrabajados - $minBaseSabado, 0);
                $catSabado   += $base;
                $extraSabado += $ext;
                $minBaseDia   = $minBaseSabado;
                $minExtras    = $ext;
            } else {                          // lun-vie
                $base = min($minTrabajados, $minBaseSemana);
                $ext  = max($minTrabajados - $minBaseSemana, 0);
                $catNormal   += $base;
                $extraSemana += $ext;
                $minBaseDia   = $minBaseSemana;
                $minExtras    = $ext;
            }

            $minNormales     = $minTrabajados - $minExtras;
            $totalTrabMin   += $minTrabajados;
            $totalExtrasMin += $minExtras;
            $totalNormMin   += $minNormales;

            // Tardanza (informativo): primera ENTRADA del día vs horario de ingreso.
            // Solo cuenta si supera la tolerancia; los minutos son desde el horario.
            $tarde = 0;
            if ($horarioIngreso) {
                $entrada = $items->firstWhere('tipo', 'entrada');
                if ($entrada) {
                    $esperado = Carbon::parse($fecha . ' ' . $horarioIngreso);
                    $limite   = $esperado->copy()->addMinutes($toleranciaMin);
                    if ($entrada->momento->gt($limite)) {
                        $tarde = $esperado->diffInMinutes($entrada->momento);
                        $tardanzasMin  += $tarde;
                        $tardanzasDias += 1;
                    }
                }
            }

            $resumen[$fecha] = [
                'fichadas'         => $items,
                'min_trabajados'   => $minTrabajados,
                'min_normales'     => $minNormales,
                'min_extras'       => $minExtras,
                'min_base_jornada' => $minBaseDia,
                'tarde_min'        => $tarde,
            ];
        }

        return [
            'resumen'         => $resumen,
            'totalTrabMin'    => $totalTrabMin,
            'totalNormMin'    => $totalNormMin,
            'totalExtrasMin'  => $totalExtrasMin,
            'horasNormales'   => round($totalNormMin / 60, 2),
            'horasExtras'     => round($totalExtrasMin / 60, 2),
            'horasBaseSemana' => $horasBaseSemana,

            // Desglose por categoría (horas)
            'horasNormalSemana' => round($catNormal / 60, 2),
            'horasSabado'       => round($catSabado / 60, 2),
            'horasDomingo'      => round($catDomingo / 60, 2),
            'horasExtraSemana'  => round($extraSemana / 60, 2),
            'horasExtraSabado'  => round($extraSabado / 60, 2),

            // Tardanzas (informativo)
            'tardanzasMin'  => $tardanzasMin,
            'tardanzasDias' => $tardanzasDias,
        ];
    }

    /**
     * Recorre las fichadas de un día y suma los minutos trabajados,
     * descontando pausas.
     */
    private function calcularMinutosTrabajados(Collection $items): int
    {
        $minTrabajados = 0;
        $estado        = 'fuera';
        $inicioJornada = null;
        $enPausa       = false;
        $inicioPausa   = null;
        $minPausa      = 0;

        foreach ($items as $f) {
            switch ($f->tipo) {
                case 'entrada':
                    if ($estado === 'fuera') {
                        $estado        = 'trabajando';
                        $inicioJornada = $f->momento->copy();
                        $enPausa       = false;
                        $inicioPausa   = null;
                        $minPausa      = 0;
                    }
                    break;

                case 'pausa_inicio':
                    if ($estado === 'trabajando' && !$enPausa) {
                        $enPausa     = true;
                        $inicioPausa = $f->momento->copy();
                    }
                    break;

                case 'pausa_fin':
                    if ($estado === 'trabajando' && $enPausa && $inicioPausa) {
                        $enPausa     = false;
                        $minPausa   += $inicioPausa->diffInMinutes($f->momento);
                        $inicioPausa = null;
                    }
                    break;

                case 'salida':
                    if ($estado === 'trabajando' && $inicioJornada) {
                        $minTotal = $inicioJornada->diffInMinutes($f->momento) - $minPausa;

                        $minTrabajados += max($minTotal, 0);

                        $estado        = 'fuera';
                        $inicioJornada = null;
                        $enPausa       = false;
                        $inicioPausa   = null;
                        $minPausa      = 0;
                    }
                    break;
            }
        }

        return $minTrabajados;
    }
}

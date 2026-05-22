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
        $minBaseSabado   = 4 * 60;
        $minBaseDomingo  = 0;

        $resumen        = [];
        $totalTrabMin   = 0;
        $totalExtrasMin = 0;
        $totalNormMin   = 0;

        $porDia = $fichadas->groupBy(fn ($f) => $f->momento->toDateString());

        foreach ($porDia as $fecha => $items) {
            $items = $items->sortBy('momento')->values();

            $minTrabajados = $this->calcularMinutosTrabajados($items);

            $dow = Carbon::parse($fecha)->dayOfWeekIso; // 1=lun, 7=dom

            $minBaseDia = match (true) {
                $dow === 6 => $minBaseSabado,
                $dow === 7 => $minBaseDomingo,
                default    => $minBaseSemana,
            };

            $minExtras   = max($minTrabajados - $minBaseDia, 0);
            $minNormales = $minTrabajados - $minExtras;

            $totalTrabMin   += $minTrabajados;
            $totalExtrasMin += $minExtras;
            $totalNormMin   += $minNormales;

            $resumen[$fecha] = [
                'fichadas'         => $items,
                'min_trabajados'   => $minTrabajados,
                'min_normales'     => $minNormales,
                'min_extras'       => $minExtras,
                'min_base_jornada' => $minBaseDia,
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

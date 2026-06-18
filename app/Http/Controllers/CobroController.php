<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Factura;
use App\Models\Cobro;

class CobroController extends Controller
{
    /** Registra un cobro (total o parcial) contra una factura. */
    public function store(Request $request, Factura $factura)
    {
        if (! $factura->esFactura()) {
            return back()->with('error', 'Las notas de crédito no se cobran.');
        }

        $data = $request->validate([
            'monto'         => 'required|numeric|min:0.01',
            'forma_pago'    => 'required|in:' . implode(',', array_keys(Cobro::FORMAS)),
            'fecha'         => 'required|date|before_or_equal:today',
            'observaciones' => 'nullable|string|max:500',
        ]);

        // No permitir cobrar más que el saldo pendiente (con tolerancia de redondeo).
        $saldo = $factura->saldoPendiente();
        if ($data['monto'] > $saldo + 0.01) {
            return back()->with('error',
                'El monto ($' . number_format($data['monto'], 2, ',', '.') . ') supera el saldo pendiente ($'
                . number_format($saldo, 2, ',', '.') . ').'
            );
        }

        $factura->cobros()->create([
            'created_by'    => auth()->id(),
            'monto'         => $data['monto'],
            'forma_pago'    => $data['forma_pago'],
            'fecha'         => $data['fecha'],
            'observaciones' => $data['observaciones'] ?? null,
        ]);

        $factura->load('cobros');
        $msg = $factura->estadoCobro() === 'cobrada'
            ? 'Cobro registrado. Factura ' . $factura->numeroFormateado() . ' COBRADA.'
            : 'Cobro registrado. Saldo pendiente: $' . number_format($factura->saldoPendiente(), 2, ',', '.') . '.';

        return back()->with('success', $msg);
    }

    /** Revierte un cobro. */
    public function destroy(Cobro $cobro)
    {
        $cobro->delete();
        return back()->with('success', 'Cobro eliminado.');
    }
}

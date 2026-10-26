<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * IVA en el presupuesto. Hasta ahora el precio era "final" a secas, sin ningún
 * concepto de IVA. Ahora:
 *
 *  - `alicuota_iva` (default 21): la alícuota del presupuesto completo.
 *  - `precio_con_iva` (default true): si los precios que se cargan YA incluyen
 *    el IVA (tilde puesto) o si son netos y hay que sumárselo (tilde quitado).
 *
 * `precio_unitario` / `subtotal` / `total` siguen guardándose como FINAL (IVA
 * incluido), igual que siempre, así los presupuestos existentes no cambian de
 * importe: con los defaults (con IVA, 21%) un presupuesto viejo se ve idéntico.
 *
 * Cómo se MUESTRA el IVA depende de la condición del cliente, no se guarda:
 *  - Responsable Inscripto / Monotributo → discriminado (neto + IVA + total).
 *  - Consumidor Final / Exento / etc.     → IVA contenido (informativo, ya en el total).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            if (! Schema::hasColumn('presupuestos', 'alicuota_iva')) {
                $table->decimal('alicuota_iva', 5, 2)->default(21)->after('multiplicador');
            }
            if (! Schema::hasColumn('presupuestos', 'precio_con_iva')) {
                $table->boolean('precio_con_iva')->default(true)->after('alicuota_iva');
            }
        });
    }

    public function down(): void
    {
        Schema::table('presupuestos', function (Blueprint $table) {
            foreach (['alicuota_iva', 'precio_con_iva'] as $col) {
                if (Schema::hasColumn('presupuestos', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seguimientos', function (Blueprint $table) {
            $table->id();

            // Vínculos (fecha/monto/N° se leen por relación → siempre sincronizados)
            $table->foreignId('presupuesto_id')->unique()->constrained('presupuestos')->cascadeOnDelete();
            $table->foreignId('factura_id')->nullable()->constrained('facturas')->nullOnDelete();

            // Cargados a mano
            $table->string('area_oficina')->nullable();
            $table->text('detalle')->nullable();
            $table->string('orden_compra')->nullable();
            $table->decimal('monto_op', 14, 2)->nullable();
            $table->string('estado')->default('presupuestado');
            $table->text('observaciones')->nullable();
            $table->string('pasado_a')->nullable();
            $table->date('fecha_pago')->nullable();   // "Transferido a Hernán"

            $table->softDeletes();
            $table->timestamps();
        });

        $this->backfill();
    }

    /**
     * Genera una fila por cada presupuesto existente, con su factura vinculada
     * y un estado inicial razonable (cobrado / facturado / presupuestado).
     */
    private function backfill(): void
    {
        if (!Schema::hasTable('presupuestos')) return;

        $presupuestos = DB::table('presupuestos')->whereNull('deleted_at')->get();

        foreach ($presupuestos as $p) {
            $factura = DB::table('facturas')
                ->where('presupuesto_id', $p->id)
                ->whereNull('deleted_at')
                ->orderBy('id')
                ->first();

            $estado = 'presupuestado';
            if ($factura) {
                $estado    = 'facturado';
                $cobrado   = (float) DB::table('cobros')
                    ->where('factura_id', $factura->id)
                    ->whereNull('deleted_at')
                    ->sum('monto');
                if ($cobrado > 0 && $cobrado + 0.01 >= (float) $factura->imp_total) {
                    $estado = 'cobrado';
                }
            }

            DB::table('seguimientos')->insert([
                'presupuesto_id' => $p->id,
                'factura_id'     => $factura->id ?? null,
                'estado'         => $estado,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('seguimientos');
    }
};

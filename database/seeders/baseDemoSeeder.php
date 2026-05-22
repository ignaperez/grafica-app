<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ListaPrecio;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\OrdenTrabajo;
use App\Models\Trabajo;

class BaseDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Listas de precios
        $consumidor = ListaPrecio::create([
            'nombre' => 'Consumidor Final',
            'descripcion' => 'Precio completo',
            'multiplicador' => 1.00,
        ]);

        $gremio = ListaPrecio::create([
            'nombre' => 'Gremio',
            'descripcion' => '10% de descuento',
            'multiplicador' => 0.90,
        ]);

        // 2. Productos
        $productos = [
            ['tipo' => 'vinilo', 'nombre' => 'Vinilo promocional Jaspe', 'descripcion' => 'Vinilo económico para corta duración', 'precio' => 1500.00],
            ['tipo' => 'lona', 'nombre' => 'Lona backlight Premium', 'descripcion' => 'Lona translúcida para cajas de luz', 'precio' => 2700.00],
            ['tipo' => 'corpórea', 'nombre' => 'Letras PVC 5mm', 'descripcion' => 'Corte láser, ideal para interiores', 'precio' => 5000.00],
        ];

        foreach ($productos as $producto) {
            Producto::create($producto);
        }

        // 3. Cliente
        $cliente = Cliente::create([
            'nombre' => 'Juan Pérez',
            'telefono' => '1122334455',
            'email' => 'juanperez@mail.com',
            'direccion' => 'Av. Siempreviva 123',
            'lista_precio_id' => $consumidor->id,
        ]);

        // 4. Orden de trabajo
        $orden = OrdenTrabajo::create([
            'cliente_id' => $cliente->id,
            'fecha_recibido' => now(),
            'observaciones' => 'Entrega urgente',
            'estado' => 'pendiente',
        ]);

        // 5. Trabajos en esa orden
        Trabajo::insert([
            [
                'orden_trabajo_id' => $orden->id,
                'tipo' => 'vinilo',
                'descripcion' => 'Vidriera local',
                'cantidad' => 5,
                'medidas' => '1x1',
                'estado' => 'pendiente',
                'fecha_entrega' => now()->addDays(2),
            ],
            [
                'orden_trabajo_id' => $orden->id,
                'tipo' => 'lona',
                'descripcion' => 'Cartel promocional',
                'cantidad' => 2,
                'medidas' => '2x1',
                'estado' => 'pendiente',
                'fecha_entrega' => now()->addDays(3),
            ],
        ]);
    }
}

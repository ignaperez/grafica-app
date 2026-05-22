<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ListaPrecio;


class ListaPreciosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
{
    ListaPrecio::insert([
        ['nombre' => 'Consumidor Final', 'descripcion' => null],
        ['nombre' => 'Gremio', 'descripcion' => null],
        ['nombre' => 'Estado', 'descripcion' => 'Gobierno nacional, municipal o provincial'],
        ['nombre' => 'Revendedor', 'descripcion' => null],
    ]);
}
}

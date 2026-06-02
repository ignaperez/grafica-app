<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    protected $table        = 'configuracion';
    protected $primaryKey   = 'clave';
    public    $incrementing = false;
    protected $keyType      = 'string';

    protected $fillable = ['clave', 'valor', 'descripcion'];

    /**
     * Obtener un valor. Devuelve $default si no existe.
     */
    public static function get(string $clave, mixed $default = null): mixed
    {
        $row = static::find($clave);
        return $row ? $row->valor : $default;
    }

    /**
     * Guardar (upsert) un valor.
     */
    public static function set(string $clave, mixed $valor): void
    {
        static::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
    }

    /**
     * Devuelve las tarifas de MO globales como array numérico.
     */
    public static function mo(): array
    {
        return [
            'm2'     => (float) static::get('mo_m2',     0),
            'ml'     => (float) static::get('mo_ml',     0),
            'unidad' => (float) static::get('mo_unidad', 0),
        ];
    }

    /**
     * Devuelve los datos de empresa como array.
     */
    public static function empresa(): array
    {
        return [
            'nombre'               => static::get('empresa_nombre',               config('app.name')),
            'cuit'                 => static::get('empresa_cuit',                 ''),
            'direccion'            => static::get('empresa_direccion',            ''),
            'telefono'             => static::get('empresa_telefono',             ''),
            'propietario'          => static::get('empresa_propietario',          ''),
            'email'                => static::get('empresa_email',                ''),
            'condicion_iva'        => static::get('empresa_condicion_iva',        ''),
            'iibb'                 => static::get('empresa_iibb',                 ''),
            'inicio_actividades'   => static::get('empresa_inicio_actividades',   ''),
        ];
    }
}

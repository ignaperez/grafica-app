# CLAUDE.md — grafica-app

Sistema de gestión para una empresa de gráfica / imprenta. Corre en Laragon (Windows).

---

## Stack

| Capa | Tecnología |
|---|---|
| Backend | Laravel 12, PHP 8.2 |
| Auth | Laravel Breeze (session, Blade) |
| Frontend | Blade + Bootstrap 5.3.2 + custom CSS dark theme |
| JS | jQuery 3.6.0, Select2 4.1.0, Alpine.js (solo en nav vieja) |
| Build | Vite (`npm run dev`) |
| DB | MySQL vía Laragon local |

**Entorno local:** `http://grafica-app.test` (virtual host Laragon)

**Comandos frecuentes:**
```
php artisan migrate          # correr migraciones
php artisan route:list       # ver rutas
php artisan config:clear     # limpiar cache
composer run dev             # inicia server + queue + logs + vite juntos
```

---

## Roles de usuario

Campo `users.rol` (string). Tres valores posibles:

| Rol | Acceso |
|---|---|
| `admin` | Todo |
| `ventas` | Órdenes, trabajos, clientes, configuración (tipos/materiales/máquinas) |
| `produccion` | Solo producción (muy limitado por ahora) |

**Middleware:** `RolMiddleware` → se usa en rutas como `['auth', 'rol:admin,ventas']`

**En Blade:** `auth()->user()->rol === 'admin'` o `in_array(auth()->user()->rol, ['admin','ventas'])`

---

## Layout principal

**Archivo: `resources/views/layouts/app.blade.php`**

Este es EL layout real. Tiene un sidebar fijo a la izquierda (220px) con navegación por módulo y control de roles. Todas las vistas lo usan con `@extends('layouts.app')`.

> `layouts/navigation.blade.php` existe pero es el navbar viejo de Breeze — ya no se usa activamente.

**Secciones disponibles en las vistas:**
```blade
@section('page-title', 'Nombre de la página')   ← título en el topbar breadcrumb
@section('topbar-actions')                        ← botones en la esquina top-right
@section('content')                               ← contenido principal
@section('scripts')                               ← JS al final del body
```

**Flash messages:** el layout ya maneja `session('success')`, `session('ok')` y `session('error')`. También muestra `$errors->all()`. No repetir en las vistas.

---

## Sistema de diseño (CSS custom — dark theme)

Todo está definido en el `<style>` de `layouts/app.blade.php`. Bootstrap también está cargado pero el sistema propio es preferido.

### Variables CSS
```css
--bg: #0f0f0f        /* fondo base */
--bg-s: #141414      /* fondo sidebar / cards */
--bg-h: #1a1a1a      /* hover */
--b: #1e1e1e         /* border oscuro */
--bm: #2a2a2a        /* border medio */
--tx: #e8e4dc        /* texto principal */
--txm: #555          /* texto muted */
--txd: #888          /* texto dim */
--ac: #e6502a        /* acento (naranja/rojo) */
--mono: 'DM Mono'
--sans: 'DM Sans'
```

### Clases de componentes

**Botones:**
```html
<a class="gbtn gbtn-primary">Principal</a>
<a class="gbtn gbtn-ghost">Secundario</a>
<a class="gbtn gbtn-danger">Peligro</a>
<a class="gbtn gbtn-primary gbtn-sm">Pequeño</a>
<a class="gbtn gbtn-ghost gbtn-xs">Muy pequeño</a>
```

**Cards:**
```html
<div class="gcard">
  <div class="gcard-hd">
    <span class="gcard-title">Título</span>
    <a class="gbtn gbtn-ghost gbtn-sm">Acción</a>
  </div>
  <div class="gcard-bd">contenido</div>
</div>
```

**Tablas:**
```html
<table class="gtable">
  <thead><tr><th>Col</th></tr></thead>
  <tbody><tr><td>valor</td></tr></tbody>
</table>
```

**Formularios:**
```html
<div class="gfg">
  <label class="glabel">Campo *</label>
  <input type="text" class="ginput">
  <div class="gerr">mensaje de error</div>
</div>
<select class="gselect">...</select>
<textarea class="gtextarea"></textarea>
```

**Badges de estado:**
```html
<span class="badge-estado be-pendiente">Pendiente</span>
<span class="badge-estado be-en_produccion">En producción</span>
<span class="badge-estado be-lista">Lista</span>
<span class="badge-estado be-entregada">Entregada</span>
<span class="badge-estado be-cancelada">Cancelada</span>
<span class="badge-estado be-terminado">Terminado</span>
```

**Progress bar:**
```html
<div class="gprog"><div class="gprog-fill" style="width:{{ $pct }}%"></div></div>
```

**Helpers de texto:**
```html
<span class="mono">código</span>
<span class="txm">muted</span>
<span class="txd">dim</span>
```

**Select2** ya está cargado globalmente. Inicializar así:
```js
$('.mi-select').select2({ width: 'resolve', placeholder: 'Seleccione...' });
```

---

## Módulos y archivos clave

### Modelos (`app/Models/`)

| Modelo | Tabla | Relaciones importantes |
|---|---|---|
| `OrdenTrabajo` | `orden_trabajos` | hasMany Trabajo, belongsTo Cliente, hasMany OrdenFoto |
| `Trabajo` | `trabajos` | belongsTo OrdenTrabajo (nullable), Cliente, TipoTrabajo, Material, Maquina, Producto |
| `Cliente` | `clientes` | hasMany OrdenTrabajo |
| `Producto` | `productos` | hasMany Trabajo |
| `ListaPrecio` | `lista_precios` | pertenece a Cliente, tiene `multiplicador` |
| `TipoTrabajo` | `tipo_trabajos` | lookup dinámico con `activo` |
| `Material` | `materiales` | lookup dinámico con `activo` |
| `Maquina` | `maquinas` | lookup dinámico con `activo` |
| `Empleado` | `empleados` | SoftDeletes, hasMany Fichada |
| `Fichada` | `fichadas` | belongsTo Empleado |

### Campos de `trabajos` (tabla completa)
```
id, orden_trabajo_id (nullable FK), cliente_id (FK), tipo_trabajo_id (FK),
material_id (FK), maquina_id (FK), producto_id (FK),
tipo (string legacy), descripcion, medidas, cantidad, estado,
fecha_entrega, fecha_carga (timestamp), precio_unitario, ancho, alto,
created_at, updated_at
```
`orden_trabajo_id` es nullable — un trabajo puede existir sin orden.

### Estados de `trabajos`
`pendiente` | `en_produccion` | `terminado`

### Estados de `orden_trabajos`
`borrador` | `en_produccion` | `lista` | `entregada` | `cancelada`

---

## Rutas y controllers

### Naming convention
- Rutas: kebab-case (`ordenes-trabajo`, `tipo-trabajos`, `trabajos-libres`)
- Vistas: carpeta con nombre del módulo, archivos `index / create / edit / show`

### Mapa rápido

| Ruta (name prefix) | Controller | Rol mínimo |
|---|---|---|
| `dashboard` | `DashboardController` | auth |
| `ordenes-trabajo.*` | `OrdenTrabajoController` | admin, ventas |
| `trabajos.*` | `TrabajoController` | admin, ventas |
| `trabajos-libres.*` | `TrabajoLibreController` | admin, ventas |
| `tipo-trabajos.*` | `TipoTrabajoController` | admin, ventas |
| `materiales.*` | `MaterialController` | admin, ventas |
| `maquinas.*` | `MaquinaController` | admin, ventas |
| `clientes.*` | `ClienteController` | admin |
| `productos.*` | `ProductoController` | admin, ventas |
| `listas-precios.*` | `ListaPrecioController` | admin, ventas |
| `rrhh.*` | `EmpleadoController`, `FichadaController` | admin |
| `fichar.*` | `FichadaController` | público (tablet) |

### Rutas AJAX helper (search para Select2)
```
GET /clientes/search?q=     → clientes.search
GET /productos/search?q=    → productos.search
```

### Rutas especiales de trabajos
```
POST /trabajos/ajax-store              → trabajos.ajax-store
POST /trabajos/store-multiples         → trabajos.store-multiples
POST /trabajos/{id}/terminar           → trabajos.terminar
GET  /trabajos/crear-para/{orden}      → trabajos.create-para-orden  (ruta especial, no usar trabajos.create)
POST /trabajos-libres/asignar-orden    → trabajos-libres.asignar-orden
```

---

## Vistas — estructura de carpetas

```
resources/views/
├── layouts/
│   ├── app.blade.php          ← LAYOUT PRINCIPAL (sidebar + dark theme)
│   └── guest.blade.php        ← login/register
├── ordenes-trabajo/           ← OTs con trabajos embebidos
├── trabajos-libres/           ← trabajos sin orden (index + create)
├── trabajos/                  ← edit/show de trabajo individual
├── tipo-trabajos/             ← ABM tipos de trabajo
├── materiales/                ← ABM materiales
├── maquinas/                  ← ABM máquinas
├── clientes/
├── productos/
├── listas-precios/
└── rrhh/
    ├── empleados/
    └── fichadas/
```

---

## Sidebar — cómo agregar links

El sidebar está hardcodeado en `layouts/app.blade.php` (líneas ~150–180).
Cuando se agrega un módulo nuevo, agregar el link allí manualmente.

Estructura de un link:
```html
<a href="{{ route('mi-ruta.index') }}"
   class="s-item {{ request()->routeIs('mi-ruta.*') ? 'on' : '' }}">
    <span class="dot"></span> Nombre
</a>
```

---

## Convenciones del proyecto

- **Soft delete SIEMPRE:** TODOS los modelos usan `SoftDeletes`. Nunca usar delete físico. Los registros se conservan para auditoría de admin. Para recuperar usar `withTrashed()` / `onlyTrashed()` en Eloquent. Los lookups (tipos, materiales, máquinas) tienen además campo `activo` boolean para habilitar/deshabilitar sin eliminar.
- **`activo` en orden_trabajos:** campo legacy, ya no se usa como flag de borrado (ahora usa `deleted_at`). Se mantiene en la tabla pero no tiene lógica activa.
- **Fechas en vistas:** siempre formatear con `\Carbon\Carbon::parse($fecha)->format('d/m/Y')` o `->isoFormat('D MMM YYYY')`.
- **Precios:** se calculan multiplicando `producto->precio * lista->multiplicador`. Siempre `round(..., 2)`.
- **m²:** se calcula en JS en tiempo real: `ancho * alto * cantidad`.
- **`orden_trabajo_id` nullable:** trabajo puede existir sin orden. Nunca asumir que siempre tiene orden.
- **Sin tests automáticos** por ahora. Verificar manualmente en el browser.
- **Migraciones:** nombrar con fecha `YYYY_MM_DD_HHMMSS_descripcion.php`.
- **Storage:** archivos en `storage/app/public/`. Acceso público vía `Storage::disk('public')->url($ruta)`. Symlink ya creado (`public/storage`).

## Archivos adjuntos en trabajos

Tabla `trabajo_archivos` (con SoftDeletes):

| Campo | Descripción |
|---|---|
| `trabajo_id` | FK a trabajos |
| `tipo` | `imprimir` (archivo productivo) o `referencia` (guía visual) |
| `nombre_original` | nombre del archivo subido |
| `ruta` | path relativo en disco public |
| `mime_type` | MIME del archivo |
| `tamanio` | bytes |

**Extensiones aceptadas:** jpg, jpeg, png, gif, bmp, webp, tif, tiff, pdf, ai, eps, svg, psd, cdr, indd

**Límite:** 100 MB por archivo (requiere ajustar `upload_max_filesize` y `post_max_size` en php.ini de Laragon si los defaults son menores).

**Rutas:**
```
POST   /trabajos/{trabajo}/archivos        → trabajo-archivos.store
DELETE /trabajo-archivos/{trabajoArchivo}  → trabajo-archivos.destroy
```

**Relaciones en Trabajo:**
- `archivos()` → todos
- `archivosImprimir()` → tipo = imprimir
- `referencias()` → tipo = referencia

---

## Deploy — Producción (plote.ar)

**Dominio:** plote.ar
**Servidor:** VPS Wiroos, acceso SSH
**IP pública:** 148.113.192.65
**Hostname:** c1116.cloud.wiroos.net
**DNS:** administrados en panel Wiroos (ns1.wiroos.com / ns2.wiroos.com)
**PHP en VPS:** 8.4.5
**Servidor web:** Nginx (a instalar — no estaba al 2026-05-26)
**OS:** por confirmar

### Estado al 2026-05-26 — CASI LISTO
- DNS registros A configurados en Wiroos (@ y www → 148.113.192.65) ✓
- Nginx instalado y configurado ✓ — server block en `/etc/nginx/sites-enabled/plote-ar`
- App Laravel en `/var/www/grafica/` ✓
- `.env` de producción configurado ✓
- `vendor/` y assets (`public/build/`) presentes ✓
- Symlink `public/storage` creado ✓
- Migraciones corridas ✓
- Config/route/view cache generados ✓
- App Django (123millas.com.ar) eliminada del servidor ✓
- Laravel responde HTTP 200 ✓ (verificado con curl -H "Host: plote.ar")
- **Pendiente:** DNS propagación + SSL con Certbot

### Pendiente
1. Esperar propagación DNS (chequear en https://dnschecker.org/#A/plote.ar)
2. Una vez que resuelva, correr Certbot:
```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d plote.ar -d www.plote.ar
```

---

## Gotchas conocidos

1. **`materiales` resource:** el parámetro de ruta debe ser `material` (no `materiale`). Se fuerza con `.parameters(['materiales' => 'material'])` en `web.php`.
2. **`tipo-trabajos` resource:** parámetro es `tipoTrabajo` (camelCase) forzado con `.parameters(['tipo-trabajos' => 'tipoTrabajo'])`.
3. **Select2 en filas dinámicas:** inicializar SOLO dentro del scope de la fila nueva, no con `$('.select2')` global, para evitar doble-init.
4. **`navigation.blade.php`:** archivo viejo de Breeze, casi no se usa. El sidebar real está en `app.blade.php`.
5. **ABM lookups (tipos/materiales/máquinas):** al eliminar, verificar primero que no haya trabajos asociados (`->exists()` check en el controller).
6. **`listas-precios` resource:** parámetro es `listaPrecio` forzado con `.parameters(['listas-precios' => 'listaPrecio'])` en `web.php`.

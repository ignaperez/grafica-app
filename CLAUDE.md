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
| `Producto` | `productos` | hasMany Trabajo, belongsTo TipoTrabajo, belongsTo Material |
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
| `clientes.*` | `ClienteController` | admin, ventas |
| `presupuestos.*` | `PresupuestoController` | admin, ventas |
| `catalogo.*` | `CatalogoController` | admin, ventas |
| `productos.*` | `ProductoController` | admin, ventas |
| `listas-precios.*` | `ListaPrecioController` | admin, ventas |
| `configuracion.*` | `ConfiguracionController` | admin |
| `rrhh.*` | `EmpleadoController`, `FichadaController` | admin |
| `fichar.*` | `FichadaController` | público (tablet) |

### Rutas AJAX helper (search para Select2)
```
GET /clientes/search?q=     → clientes.search
GET /productos/search?q=    → productos.search
```

### Rutas especiales de presupuestos
```
GET  /presupuestos/precio-servicio?maquina_id=X&material_id=Y&cliente_id=Z  → presupuestos.precio-servicio
GET  /presupuestos/{presupuesto}/print          → presupuestos.print
PATCH /presupuestos/{presupuesto}/estado        → presupuestos.estado
POST  /presupuestos/{presupuesto}/convertir-ot  → presupuestos.convertir-ot
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
├── presupuestos/          ← index / create / show / print
├── catalogo/              ← index / print (auto-generado maquina×material)
├── configuracion/         ← edit (MO global, datos empresa)
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
- **Precios (nuevo catálogo):** fórmula = `(material.costo_X + maquina.costo_X + producto.costo_mano_obra) × cantidad × lista.multiplicador`. El campo `X` depende de `producto.unidad` (m2 → `costo_m2`, ml → `costo_ml`, unidad → `costo_unidad`). Siempre `round(..., 2)`.
- **Precios (legacy):** `producto->precio * lista->multiplicador` — todavía en la tabla pero nullable.
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

### Estado al 2026-05-27 — CASI LISTO
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
- **Pendiente:** DNS (ticket abierto a Wiroos) + SSL con Certbot

### Problema DNS (2026-05-27)
Wiroos no permite gestionar la zona DNS desde el panel del VPS. Los nameservers
responden "Query refused" para plote.ar. Ticket de soporte abierto.

**Plan B si Wiroos no resuelve:** mover DNS a Cloudflare (gratis).
1. Agregar plote.ar en cloudflare.com → obtener 2 nameservers
2. En NIC.ar cambiar nameservers a los de Cloudflare
3. En Cloudflare: A @ → 148.113.192.65 y A www → 148.113.192.65 (proxy gris)
4. Una vez que resuelva, correr Certbot en el VPS:
```bash
apt install -y certbot python3-certbot-nginx
certbot --nginx -d plote.ar -d www.plote.ar
```

### SSL — certificados y subdominios de tenants (2026-06-08)
**Acceso SSH:** `ssh -i ~/.ssh/plote_vps_claude root@148.113.192.65` (key dedicada de Claude).

**Nginx ya sirve cualquier subdominio:** el server block `/etc/nginx/sites-available/plote-ar`
tiene `server_name plote.ar www.plote.ar *.plote.ar` (HTTP 80 → 301 a HTTPS, y bloque 443).
O sea, a nivel HTTP **no hay que tocar Nginx** para un tenant nuevo — el wildcard ya matchea.
Apunta al cert `live/plote.ar/`.

**Lo que SÍ falta por subdominio = el certificado.** El cert `plote.ar` NO es wildcard (un
wildcard requeriría challenge DNS-01 + API DNS, que Wiroos no da). Cada subdominio de tenant
hay que agregarlo al cert `plote.ar` por HTTP-01 (funciona porque el DNS ya resuelve a la IP).

**Receta para habilitar HTTPS en un subdominio nuevo `NUEVO.plote.ar`:**
1. Verificar que el DNS resuelva: debe haber un A record `NUEVO.plote.ar → 148.113.192.65`.
2. Expandir el cert existente (incluir SIEMPRE los dominios ya presentes + el nuevo):
```bash
certbot certonly --nginx --cert-name plote.ar \
  -d plote.ar -d www.plote.ar -d app.plote.ar -d NUEVO.plote.ar \
  --expand --non-interactive --agree-tos
nginx -t && systemctl reload nginx
```
   Tip: hacer primero un `--dry-run` para no gastar el rate limit de Let's Encrypt.
3. Verificar: `curl -sI https://NUEVO.plote.ar` (302 = OK) y
   `echo | openssl s_client -connect 127.0.0.1:443 -servername NUEVO.plote.ar 2>/dev/null | openssl x509 -noout -ext subjectAltName`.

Como es el MISMO cert `plote.ar`, la renovación automática de Certbot ya cubre todos los SAN.

**Estado al 2026-06-08** — cert `plote.ar` cubre: `plote.ar`, `www.plote.ar`, `app.plote.ar`,
`123ploteos.plote.ar` (subdominio agregado este día, HTTPS OK). Vence 2026-09-06, renovación
automática activa. Otros certs en el VPS: `mail.plote.ar`, `webmail.plote.ar`
(`123millas.com.ar` quedó EXPIRED/sin uso).

---

## Módulo de Facturación Electrónica (ARCA/AFIP) — EN DESARROLLO

### Objetivo
Emitir **Facturas A, B y C con CAE** directamente desde la app, vinculadas a presupuestos.
Remito: documento interno sin CAE (no aplica REM para imprenta).

### Stack técnico
```bash
composer require afip/sdk   # SDK oficial AFIP/ARCA
```
O alternativa más mantenida:
```bash
composer require multinexo/php-afip-ws
```

### Servicios ARCA que se usan
| Servicio | Función |
|---|---|
| **WSAA** | Autenticación — genera ticket (TA) con duración 12hs usando el certificado |
| **WSFE** | Facturación electrónica — pide nro comprobante, envía datos, recibe CAE |

### Flujo de emisión
1. WSAA: firma XML con clave privada → ARCA devuelve Token + Sign (cachear 12hs)
2. WSFE: con T+S, consultar último nro comprobante → enviar factura → recibir CAE + vencimiento
3. Guardar CAE en DB
4. Generar PDF con QR obligatorio (datos: CUIT, tipo, nro, fecha, importe, CAE, vto CAE)

### Archivos del certificado (NO commitear nunca)
```
storage/app/arca/
├── private.key      ← clave privada RSA 2048 (NUNCA al repo)
├── cert.crt         ← certificado firmado por ARCA
└── cert_homo.crt    ← certificado de homologación (testing)
```
Agregar a `.gitignore`: `storage/app/arca/`

### Variables de entorno (.env)
```
ARCA_CUIT=20XXXXXXXXX
ARCA_CERT=storage/app/arca/cert.crt
ARCA_KEY=storage/app/arca/private.key
ARCA_PRODUCTION=false          # true en producción
ARCA_PUNTO_VENTA=1             # punto de venta registrado en ARCA
```

### Modelos y tablas a crear
| Modelo | Tabla | Campos clave |
|---|---|---|
| `Factura` | `facturas` | presupuesto_id (nullable), cliente_id, tipo (A/B/C), punto_venta, numero, cae, cae_vencimiento, total, estado, created_by |
| `FacturaItem` | `factura_items` | factura_id, descripcion, cantidad, precio_unitario, subtotal, alicuota_iva |

### Tipos de comprobante ARCA (cbte_tipo)
| Código | Tipo |
|---|---|
| 1 | Factura A |
| 6 | Factura B |
| 11 | Factura C (monotributistas) |
| 3 | Nota de Crédito A |
| 8 | Nota de Crédito B |

### Rutas planeadas
```
GET  /facturas              → facturas.index
GET  /facturas/create       → facturas.create  (manual)
POST /facturas              → facturas.store
GET  /facturas/{factura}    → facturas.show
GET  /facturas/{factura}/pdf → facturas.pdf
POST /presupuestos/{presupuesto}/facturar → presupuestos.facturar  (desde presupuesto aprobado)
```

### Estado del certificado ARCA
- [x] Generar CSR y clave privada ✓
- [x] Subir CSR a ARCA producción → cert.crt obtenido ✓ (alias: plotear, vence 2028-05-29)
- [x] Autorizar computador "plotear" al servicio "Facturación Electrónica" en ARCA ✓
- [x] Autenticación WSAA OK en producción ✓
- [x] Consulta WSFE OK — PV6 Factura B último nro: 0 (sin facturas aún) ✓
- [ ] Implementar módulo Laravel completo
- [ ] Deploy y prueba emisión real

### Factura create — validación cliente + retención de datos en error (2026-06-05)
Cambios en `resources/views/facturas/create.blade.php`:
- **Cliente obligatorio (cliente-side):** el botón "Emitir" abre un modal y el "Sí, emitir"
  hace `form.submit()` por JS, lo que **saltea la validación HTML5** (`required`). Por eso
  `confirmarEmision()` ahora valida a mano ANTES de abrir el modal: cliente seleccionado,
  N° documento (salvo Consumidor Final / doc_tipo 99) y al menos un ítem con descripción +
  cantidad. Si falta algo muestra `alert()` y abre el buscador de cliente. (Server-side ya
  validaba con `cliente_id => required|exists`, esto es el aviso temprano.)
- **No perder datos en error (form):** antes la vista solo re-renderizaba el ítem índice 0
  (o los del presupuesto), así que las filas agregadas por JS se perdían al volver de un error
  de validación o de ARCA. Ahora el `<tbody>` arma `$filasItems` en este orden de prioridad:
  `old('items')` (vuelta de error → restaura TODAS las filas) → items del presupuesto → una
  fila vacía. `rowIndex` JS arranca contando `#items-body tr.item-row` en el DOM. El controller
  ya usaba `back()->withInput()` tanto en validación como en el `catch` de ARCA.

### Borradores de factura persistidos en DB (2026-06-05)
Para que la carga **sobreviva aunque se cierre la pestaña** (no solo `old()` en sesión), se
persiste un borrador en DB cuando una emisión falla.

- **Tabla `factura_borradores`** (per-tenant, SoftDeletes) — migración en
  `database/migrations/tenant/` Y `database/migrations/` (se mantienen sincronizadas).
  Campos: `created_by`, `cliente_id` (ambos nullable, nullOnDelete), `datos` (JSON con TODO el
  request: cliente_id, tipo, fecha, concepto, doc_tipo, doc_nro, observaciones, items[], nc_*),
  `total` (estimado, para listar), `error` (último motivo).
- **Modelo `App\Models\FacturaBorrador`** — `datos` casteado a `array`.
- **Flujo (`FacturaController`):**
  - Todos los `return back()->withInput()->with('error', ...)` de `store()` (reglas de negocio
    y `catch` de ARCA) se reemplazaron por `volverConBorrador($request, $msg)`, que llama a
    `guardarBorrador()` (crea o actualiza si ya viene `borrador_id`) y vuelve al form con el
    `borrador_id` en `old()`.
  - **Emisión exitosa** → borra el borrador (`borrador_id` → `FacturaBorrador::delete()`).
  - `create()` con `?borrador_id=X` → flashea `borrador->datos` como `old()` y redirige a
    `facturas.create` (reusa el mismo render que la vuelta de error). Muestra aviso `session('info')`.
- **Vista create:** `<input type="hidden" name="borrador_id" value="{{ old('borrador_id') }}">`
  tras el `@csrf`, y banner `session('info')` (el layout NO maneja `info`, se renderiza a mano).
- **Vista index:** card "💾 Borradores pendientes" arriba de las facturas, con **Retomar**
  (`facturas.create?borrador_id=`) y **Eliminar** (`DELETE facturas.borradores.destroy`).
- **Ruta nueva:** `DELETE /facturas/borradores/{borrador}` → `facturas.borradores.destroy`
  (registrada ANTES del `Route::resource('facturas')` en `routes/tenant.php`; no choca con
  `show` porque es DELETE).
- Las migraciones se corren con `php artisan tenants:migrate` (tabla per-tenant, NO `migrate`).

### Factura PDF A4 con mPDF (2026-06-08)
PDF fiscal generado **en el servidor** con `mpdf/mpdf` (reemplaza el `window.print()` viejo).
Premisas: A4 fijo sin deformar, paginación real (ítems nunca atrás del pie), encabezado + pie
repetidos en cada hoja, N° de hoja `X/Y`, y **total solo en la última hoja**.

- **Librerías:** `mpdf/mpdf ^8.3` (PHP puro, sin binarios) + `endroid/qr-code ^6.0` (QR local,
  NO se usa más `api.qrserver.com`). Requieren `gd` + `mbstring` (presentes en local y VPS).
- **Servicio:** `App\Services\FacturaPdfService::generar(Factura): Mpdf`.
  - `SetHTMLHeader` (emisor + letra + comprobante + cliente) y `SetHTMLFooter`
    (CAE + QR + código de barras AFIP + `Pág. {PAGENO}/{nbpg}`) → se repiten en TODA hoja.
  - Cuerpo = tabla de ítems (mPDF pagina solo y repite el `<thead>`) + totales/transparencia/
    monto-en-letras al final → caen en la última hoja.
  - `margin_top: 55`, `margin_bottom: 40` reservan el alto del header/footer. **Gotcha clave:**
    el QR debe llevar tamaño explícito (`style="width:20mm;height:20mm"` en el `<img>`), si no
    mPDF lo renderiza a su tamaño natural (~200px ≈ 53mm) y el pie pisa los últimos ítems.
  - Código de barras: `<barcode type="I25">` nativo de mPDF; contenido AFIP =
    CUIT+TipoCbte+PV+CAE+VtoCAE+DV (dígito verificador módulo 10 base 3).
  - Toda la lógica fiscal (letra, desglose IVA, monto en letras, filename) está portada de
    `facturas/print.blade.php`.
- **Vistas mPDF** (HTML compatible: tablas, sin flex/`var()`/`position:absolute`):
  `resources/views/facturas/pdf/{styles,header,body,footer}.blade.php`.
- **Ruta:** `GET /facturas/{factura}/pdf` → `facturas.pdf` (inline; `?download=1` fuerza descarga).
  Registrada ANTES del `Route::resource('facturas')`. Botones de `index`/`show` ya apuntan acá.
- **U. Medida por ítem:** se agregó `unidad` a `factura_items` (default `unidad`, opciones
  `unidad`/`m2`/`ml`) + selector en `facturas/create.blade`. Migración en `tenant/` y central.
- **tempDir mPDF:** `storage/app/mpdf` (el servicio lo crea si falta). En contexto tenant el
  storage se suffixa (`storage/tenant<id>/...`), pero el servicio usa `storage_path()` que ya
  resuelve el path del tenant.
- **Deploy:** requiere `composer install` en el VPS (trae mpdf+endroid) + `tenants:migrate`.

### Remito PDF A4 con mPDF (2026-06-08)
Mismo formato/concepto que la factura PDF, pero **sin precios, sin total y sin QR/CAE**: el
remito usa su **propio código** (CAI papel, autorización electrónica ARCA, o nada si es interno).
- **Servicio:** `App\Services\RemitoPdfService` (espeja a `FacturaPdfService`).
  - Header repetido: emisor + letra **R** + `REMITO` + nº + fecha + tipo + destinatario.
  - Body: tabla `# · Descripción · Cantidad · Unidad` (sin precios). Sin total.
  - Cierre (solo última hoja): **Observaciones** + firma **"Recibí conforme"**.
  - Footer repetido: bloque de código fiscal según `tipo` →
    `tieneCai()` → CAI (código + vto + nº + barcode), `tieneAutorizacion()` → Código de
    Autorización ARCA (+ barcode), interno → nada (solo nota). Más empresa + `Pág {PAGENO}/{nbpg}`.
- **Gotcha barcode:** el `<barcode type="C128">` de mPDF **no renderiza** en ese contexto;
  se usa **`type="I25"`** (el mismo que la factura). I25 requiere longitud PAR → el servicio
  antepone `0` si el código tiene dígitos impares.
- **Vistas:** `resources/views/remitos/pdf/{styles,header,body,footer}.blade.php`.
- **Ruta:** `GET /remitos/{remito}/pdf` → `remitos.pdf` (inline; `?download=1`). Botones de
  `index`/`show` ya apuntan acá. NO necesita migración (no toca DB).

### Cobros (cobranza interna, NO fiscal) (2026-06-17)
Control interno de qué facturas están cobradas y cómo. **Nada que ver con ARCA.**
- **Tabla `cobros`** (per-tenant + central sincronizadas, SoftDeletes): `factura_id`,
  `created_by`, `monto`, `forma_pago`, `fecha`, `observaciones`. Se eligió **tabla aparte**
  (no campos en factura) porque cta cte/cheque/echeq implican **pagos parciales/múltiples**.
- **Columna nueva `facturas.forma_pago`** (nullable) = forma de pago ACORDADA al emitir
  (pre-carga el cobro y se ve en el listado). Migraciones `2026_06_17_000001/000002`.
- **Modelo `App\Models\Cobro`** con const `FORMAS` (efectivo, transferencia, cuenta_corriente,
  cheque, echeq, tarjeta, otro). **`Factura`**: `cobros()`, `totalCobrado()`, `saldoPendiente()`,
  `estadoCobro()` (pendiente|parcial|cobrada), `estadoCobroLabel/Color()`, `formaPagoLabel()`,
  `esFactura()` (tipo 1/6/11; las NC 3/8/13 NO se cobran).
- **`CobroController`**: `store(Factura)` (valida monto ≤ saldo) + `destroy(Cobro)`. Rutas
  `POST /facturas/{factura}/cobros` → `facturas.cobros.store` y `DELETE /cobros/{cobro}` →
  `cobros.destroy` (grupo `rol:admin,ventas`).
- **UI:** selector "Forma de pago acordada" en `facturas/create`; columna **Cobro** (badge
  Pendiente/Parcial/Cobrada + saldo) y botón **COBRAR** (modal pre-cargado con saldo+forma) en
  `facturas/index`; panel **Cobranza** (resumen + lista + form registrar/eliminar) en
  `facturas/show`. El estado de cobro se DERIVA de Σ cobros vs `imp_total` (no es columna).

### Comprobantes relacionados (2026-06-17)
Las FK ya existían (`facturas.presupuesto_id`, `remitos.presupuesto_id/factura_id`). Solo se
agregaron relaciones inversas + UI, **sin migración**:
- `Presupuesto::facturas()` y `::remitos()`; `Factura::remitos()`.
- `presupuestos/show`: card "Comprobantes relacionados" (facturas + remitos linkeados por N°).
- `facturas/show`: lista de remitos relacionados (el presupuesto ya estaba en el encabezado).
- `remitos/show`: ya tenía links a presupuesto + factura (no se tocó).

### Dashboard — Facturado vs Cobrado del mes (2026-06-17)
`DashboardController@renderView` calcula `facturadoMes` (Σ facturas 1/6/11 − Σ NC 3/8/13 del
mes, por `fecha`) y `cobradoMes` (Σ `cobros.monto` del mes). Panel en `dashboard.blade` (solo
dashboard **admin**): Facturado / Cobrado / Por cobrar + barra de % cobrado.

### Preview de factura = PDF real (2026-06-17)
Antes el preview era una vista HTML aparte (`facturas/preview.blade`, ELIMINADA) con otro
diseño. Ahora `FacturaController@preview` arma una **`Factura` en memoria** (numero=0, cae=null)
+ items y la pasa por **`FacturaPdfService::generar($f, preview: true)`** → PDF inline idéntico
al emitido. Flag `$preview` en el service: `SetWatermarkText('PREVISUALIZACIÓN')`, header muestra
`????????` y el pie "se asigna al emitir" (el QR ya era null sin CAE). El botón "Vista previa"
(POST a `facturas.preview` en pestaña nueva) ahora abre ese PDF.

### PDF gráfica — encabezados + cierre al fondo (2026-06-17)
- **Encabezados más grandes** (~+1px) en `facturas/pdf/styles` y `remitos/pdf/styles`: datos de
  emisor/comprobante/cliente, títulos y cód. de letra.
- **Bloque de cierre anclado SIEMPRE al fondo de la última hoja** (sin importar la cantidad de
  ítems): se separó en su propio partial (`facturas/pdf/cierre.blade` = Obs + Total/SEUO +
  transparencia; `remitos/pdf/cierre.blade` = Obs + "Recibí conforme"). Los `body.blade`
  quedaron solo con la tabla de ítems. En `FacturaPdfService`/`RemitoPdfService`: tras escribir
  los ítems se mide el alto del cierre con un mPDF descartable (`alturaMm()`, mismo ancho 196mm)
  y se hace `SetY($limiteY - $cierreH - 3mm)` para apoyarlo justo sobre el pie. Si los ítems ya
  llenan la hoja, el cierre fluye normal.

### Remitos editables + obs sin leyenda default + tipografía PDF remito (2026-06-23)
Tres ajustes sobre remitos/facturas. **Commiteado a main (`3e4cdcd` + `e91c4b6`) y DEPLOYADO
a prod** (git pull + view:clear + route:cache; health check 123ploteos `/remitos` HTTP 302).

1. **Remitos editables (fiscales Y comunes):** se agregaron `edit`/`update` al
   `Route::resource('remitos')` de `routes/tenant.php` (`->only([... 'edit','update' ...])`).
   `RemitoController@edit/@update` editan SOLO el **contenido**: `cliente_id`, `fecha`,
   `observaciones` e ítems (se reemplazan: `items()->delete()` —RemitoItem NO usa SoftDeletes,
   borrado físico— + recrear). **NO se toca la numeración fiscal** (`numero`, `numero_fiscal`,
   `remito_cai_id`, `cod_autorizacion`/`_vto`) ni el `tipo` → editar un oficial/electrónico
   conserva su CAI/ARCA. Producción solo edita internos (`abort(403)` si `tipo !== 'interno'`,
   coherente con el filtro del index). Vista nueva `resources/views/remitos/edit.blade.php`
   (espeja `create`: N° y Tipo se muestran read-only; el cliente precargado; ítems desde
   `old('items')` → ítems guardados → fila vacía). Botón **✎ Editar** en el topbar de
   `remitos/show` y en cada fila de `remitos/index`.
   > Decisión: al editar NO se regenera número fiscal (no se re-emite contra ARCA/CAI). Si en el
   > futuro se quiere eso, es un cambio aparte.

2. **Observaciones sin leyenda por defecto (remito + factura):** el textarea de observaciones
   en `remitos/create` y `facturas/create` arrastraba `$presupuesto?->observaciones`
   (= `Presupuesto::CONDICIONES_DEFAULT`, "Precios expresados en pesos... seña del 50%..."). Se
   cambió a `{{ old('observaciones') }}` a secas → arranca **vacío**. (Los borradores de factura
   siguen recuperando lo cargado vía `old()`.)

3. **Tipografía del PDF de remito agrandada:** `remitos/pdf/styles.blade` subió todos los
   tamaños a un mínimo ≥ factura (body 8.5→10px, ítems 8→10px, emisor/cliente/encabezados, etc.;
   `.letra-cod` quedó en 7px para que "REMITO" no parta en 2 líneas en la columna de 14mm). En
   `RemitoPdfService` se subió `margin_top` 50→**57mm** (reserva para el header más alto, evita
   que pise el cuerpo). **Y se sacó el recuadro de la caja Observaciones del cierre**
   (`.obs-box`: se eliminó `border: 0.3mm solid #9a9a9a` → queda limpio, solo título + texto).

**Sin migración.** Deploy = `git pull` + `php artisan view:clear` (+ `route:cache` por las rutas
nuevas).

### Seguimiento — tabla de control de facturación (2026-07-02)
Planilla interna (estilo Excel del cliente) que se auto-alimenta de presupuestos y facturas.
**Solo admin.**
- **Tabla `seguimientos`** (tenant + central sincronizadas, SoftDeletes): `presupuesto_id`
  (única, cascadeOnDelete), `factura_id` (nullOnDelete) + campos manuales `area_oficina`,
  `detalle`, `orden_compra` (máx 4), `monto_op`, `estado`, `observaciones`, `pasado_a`,
  `fecha_pago`. Fecha/monto/N° NO se guardan: se leen por relación (siempre sincronizados).
  Migración `2026_06_17_000003` con **backfill** (una fila por presupuesto existente + factura
  vinculada + estado inicial cobrado/facturado/presupuestado).
- **Auto-alimentación por eventos de modelo:** `Presupuesto::created` → crea la fila;
  `Factura::created` (si tiene `presupuesto_id`) → setea `factura_id` en la fila. (El preview
  arma una Factura en memoria, no se guarda → no dispara el evento.)
- **Modelo `App\Models\Seguimiento`**: const `ESTADOS` (7, con label+bg+text) —
  presupuestado/suministro/orden_compra/devengado/facturado/orden_pago/cobrado. Cálculos:
  `iva21()` = monto×0.21/1.21 (IVA contenido), `cinco()` = **monto×0.79×0.05** (5% sobre el
  monto con el 21% ya descontado), `totalHernan()` = 21%+5%; se muestran solo con `fecha_pago`
  cargada (`mostrarCalculos()`).
- **`SeguimientoController`**: `index` (filtro por año — default actual —, paginación 30,
  panel de totales presupuestado/facturado/cobrado/**pendiente=presupuestado−cobrado**),
  `print` (vista apaisada A4), `update` (edición en línea AJAX de los campos manuales).
- **Rutas** (grupo `rol:admin`): `GET /seguimiento` (index), `GET /seguimiento/print` (ANTES
  del update), `PATCH /seguimiento/{seguimiento}` (update).
- **Vistas:** `seguimientos/index.blade` (tabla editable inline, chip de estado coloreado,
  fila **verde pastel** al pasar a Cobrado + `confirm()` SOLO al pasar a Cobrado, auto-guardado
  por fila) y `seguimientos/print.blade` (`@page size:A4 landscape`).
- **Sidebar:** link "Seguimiento" en grupo Ventas, dentro de `@if($rol === 'admin')`.
- Deploy = `git pull` + `migrate` + `tenants:migrate` + `view:clear` + `route:cache`.

### Remito — código de barras del PDF agrandado (2026-07-02)
`remitos/pdf/footer.blade`: el `<barcode type="I25">` pasó de `size=0.5 height=0.7` a
`size=0.6 height=1.5` (crece sobre todo en alto, más escaneable) y la celda de 55→62mm. Aplica
a remitos oficiales (CAI) y electrónicos (autorización ARCA); el interno no lleva barcode.

### Presupuesto ya facturado — no facturar dos veces (2026-07-02)
`presupuestos/index`: el botón "⚡ Factura" se reemplaza por "✓ Facturado" (deshabilitado) si el
presupuesto ya tiene una factura NO anulada. `PresupuestoController@index` agrega
`withCount(['facturas as facturado_count' => fn($q) => $q->where('estado','!=','anulada')])`.
Guard server-side en `FacturaController@store`: si viene `presupuesto_id` (y no es NC) y ya hay
una factura no anulada de ese presupuesto → redirige a `presupuestos.index` con error (no llega
a ARCA). Así no se puede facturar dos veces ni entrando por URL directa.

### Presupuesto — botón Descargar PDF (2026-07-02)
`presupuestos/print.blade` (vista HTML `window.print()`, NO mPDF) ahora arma `$fileNombre` =
`P{numero}_CLIENTE(26)_OBS(5)` (mismo criterio que la factura) y tiene botón **⬇ Descargar PDF**
que hace `document.title = fileNombre; window.print()` → el "Guardar como PDF" del navegador
sugiere ese nombre. Soporta `?auto=1` para auto-disparar la descarga al cargar. Botones **⬇** en
`presupuestos/index` (por fila) y `presupuestos/show` (topbar) apuntan a `presupuestos.print?auto=1`.
No usa mPDF a propósito: conserva la estética de la vista de impresión existente. Sin migración.

### Permisos por módulo + Administrador principal + sesión única (2026-07-02)
Control de acceso granular por usuario dentro del tenant. **NO confundir con el Super Admin
central** (`plote.ar/super-admin`, que gestiona empresas) — esto es por-empresa.

**Administrador principal** (uno por tenant): `users.es_super` (bool). Acceso TOTAL siempre,
único que ve/gestiona Usuarios y permisos. La migración marca al primer admin (menor id).
NO editable por UI (queda fijo; para transferirlo, cambiar `es_super` a mano en DB).

**Permisos por módulo:** `users.modulos` (JSON con las keys habilitadas). Const
`User::MODULOS` (10: ordenes, clientes, presupuestos, facturas, remitos, seguimiento, servicios,
configuracion, rrhh, papelera). `User::modulosPorRol($rol)` = plantilla default (admin=todos,
ventas=ordenes/clientes/presupuestos/facturas/remitos/servicios, produccion=ordenes). Helpers
`esSuper()`, `puedeModulo($k)`.

**Enforcement:** `ModuloAccessMiddleware` (alias `modulo.access`, aplicado al grupo externo de
`routes/tenant.php`) mapea el **nombre de ruta → módulo** (`self::MAPA`) y bloquea si el usuario
no lo tiene (super pasa siempre; ruta sin módulo = libre; `usuarios.*` = solo super). Redirige a
dashboard/inicio con error. **Los módulos RESTRINGEN dentro del rol** (las rutas siguen con su
`rol:` middleware): se puede sacar Facturas a un ventas, pero no dar una ruta admin-only a un
ventas. Es "rol como plantilla" + restricción por módulo (subtractivo).

**Sidebar** (`layouts/app.blade`): reescrito con flags `$verX = $rolPermite && $u->puedeModulo(X)`;
grupos se ocultan si quedan vacíos. Usuarios solo si `esSuper()`.

**UI usuarios:** partial `usuarios/_modulos.blade` (checkboxes + hidden `modulos_marcado`).
`create` tiene JS que auto-tilda los defaults al elegir rol. `edit` precarga los actuales (al
super le muestra "acceso total", sin checkboxes). `index` muestra badge ★ Principal / "X de N
módulos" + botón **⎋ Cerrar sesión**. `UserController@modulosDesde()` guarda lo tildado (o el
default del rol si no vino la sección); al super NO se le tocan módulos.

**Sesión única:** `users.session_id` guarda el id de la sesión activa. En login
(`AuthenticatedSessionController@store`) se setea; en logout se limpia. `SingleSessionMiddleware`
(alias `single.session`, grupo externo) cierra la sesión si `session_id` no coincide → el nuevo
login expulsa al anterior (se cae en su próximo request). **Gotcha:** las sesiones se guardan en
la **DB central** compartida (no en el tenant) y los `user_id` colisionan entre empresas, por eso
NO se borra de `sessions` por `user_id` — se usa el `session_id` en el usuario (tenant-safe).
Forzar cierre: `UserController@cerrarSesiones` setea `session_id` a un sentinel random →
`POST /usuarios/{usuario}/cerrar-sesiones` (`usuarios.cerrar-sesiones`, grupo `rol:admin`).

**Migraciones** `2026_07_02_000002` (es_super + modulos, con backfill) y `000003` (session_id),
tenant + central. Deploy = `git pull` + `migrate` + `tenants:migrate` + `optimize:clear` +
cache. **Al deployar, el primer admin de cada tenant queda como principal y el resto con los
módulos de su rol.**

### Fichaje — foto en cada fichada (2026-07-02)
Anti-fraude (evita el "fichaje por otro"): la tablet saca una foto de quien ficha y la guarda
junto a la fichada, como evidencia auditable. **NO es un bloqueo automático** (el código sigue
identificando al empleado); es disuasivo + prueba. Se descartó QR dinámico/link porque el
secreto se puede compartir; lo único infalsificable sería biometría (reconocimiento facial),
que quedó para más adelante.
- **`fichadas.foto`** (string nullable, migración `2026_07_02_000004` tenant + central).
- **Kiosco** (`fichadas/kiosk`): la cámara arranca en `facingMode: "user"` (frontal) con
  html5-qrcode (que ya estaba para leer QR). Al hacer submit, un handler captura un frame del
  `#reader video` a un canvas → `toDataURL('image/jpeg',0.6)` → input oculto `foto`. Best-effort:
  si no hay cámara, ficha sin foto.
- **`FichadaController@store`**: valida `foto` (nullable string dataURL) y
  `guardarFoto()` decodea el base64 (cap 3 MB), guarda en `storage/app/public/fichadas/{emp}/…jpg`
  (disco public, per-tenant) y setea la ruta en la fichada. `Fichada::fotoUrl()` da la URL.
- **RRHH** (`rrhh/fichadas/index`): columna **Foto** con miniatura (clic → amplía).
- **GOTCHA cámara:** `getUserMedia` solo corre en **HTTPS** (o localhost). En local por HTTP
  (`grafica-app.test`) la cámara NO arranca → hay que probar la foto en producción (HTTPS) o en
  la tablet real. La lógica de guardado/display se probó inyectando la dataURL directo.

### Arquitectura ARCA confirmada
- **WSAA**: usar paquete `multinexo/php-afip-ws` SOLO para autenticación (maneja firma XML y cache TA)
- **WSFE**: SoapClient directo — el paquete tiene bugs en PHP 8.3 (dynamic properties, reset() en objeto, count() en stdClass)
- **CUIT**: 23252997679
- **Punto de venta**: 6
- **Cert**: `storage/app/arca/cert.crt` (alias: plotear)
- **Key**: `storage/app/arca/private.key`
- **TA cache**: `storage/app/arca/xml/TA-23252997679-wsfe.xml` (dura 12hs, lo maneja el paquete)
- **URL WSAA prod**: `https://wsaa.afip.gov.ar/ws/services/LoginCms`
- **URL WSFE prod**: `https://servicios1.afip.gov.ar/wsfev1/service.asmx`
- **WSDL WSFE local**: `vendor/multinexo/php-afip-ws/src/Multinexo/Afip/WSFE/wsfe.wsdl`

### Cómo crear el certificado en ARCA

#### Paso 1 — Generar clave privada y CSR (en Laragon o Git Bash)
```bash
# En C:\laragon\www\grafica-app\storage\app\arca\ (crear la carpeta primero)
openssl genrsa -out private.key 2048

openssl req -new -key private.key -out request.csr \
  -subj "/C=AR/O=NOMBRE_EMPRESA/CN=CUIT XXXXXXXXXX/serialNumber=CUIT XXXXXXXXXX"
# Reemplazar NOMBRE_EMPRESA y CUIT (con los espacios tal como está)
```

#### Paso 2 — Subir el CSR a ARCA
1. Ir a **https://auth.afip.gob.ar** → ingresar con CUIT + Clave Fiscal (nivel 3)
2. Buscar el servicio **"Administración de Certificados Digitales"**
   (si no aparece, habilitarlo desde "Administrador de Relaciones de Clave Fiscal")
3. Clic en **"Nueva relación"** → seleccionar el servicio **"wsfe"** (o "wsfev1")
4. En **"Generar certificado"** → pegar el contenido del archivo `request.csr`
5. Descargar el `.crt` resultante → guardarlo como `storage/app/arca/cert.crt`

> Para **homologación** (testing): ir a **https://wsaahomo.afip.gov.ar** — es un entorno separado con su propio certificado. Repetir el proceso ahí con `cert_homo.crt`.

#### Paso 3 — Crear punto de venta en ARCA
1. En ARCA → **"Mis Aplicaciones Web"** → **"Administración de Puntos de Venta"**
2. Agregar punto de venta → tipo: **"Web Services"** → anotar el número asignado
3. Ese número va en `ARCA_PUNTO_VENTA` del `.env`

---

## Presupuestos — catálogo Grupo → Ítem (servicios/paquetes) (2026-06-10)

El selector de "Servicio" en `presupuestos/create` y `edit` pasó de un solo dropdown a una
**cascada de dos pasos: Grupo → Ítem**. El catálogo ahora junta DOS fuentes en la misma
estructura de grupos:

1. **Combos Máquina × Material** (`fuente: 'combo'`) — precio CALCULADO vía AJAX
   `presupuestos.precio-servicio` `(costo_maq + costo_mat) × mult + MO`. **Igual que antes.**
2. **Servicios / paquetes** (`fuente: 'producto'`, tabla `productos`) — precio FIJO opcional.
   Al elegirlo trae **descripción completa + unidad + precio** (si `productos.precio` está
   cargado; si no, se pone a mano). Pensado para trabajos cerrados tipo "Ploteo cabina camión
   Iveco Tector" (unidad `unidad`, un precio por vehículo).

**El GRUPO = `producto.tipo_trabajo_id` (TipoTrabajo).** Productos sin tipo caen en
"Otros servicios". Los combos se agrupan por el TipoTrabajo de la máquina.

### Archivos
- `PresupuestoController@buildCatalogo()` — arma el array unificado. Cada ítem:
  `fuente, grupo, label (texto del dropdown), descripcion (autofill), unidad, maquina_id,
  material_id, producto_id, precio`. (Mantiene clave legacy `tipo` = `grupo`.)
- `presupuestos/create.blade` + `edit.blade` — la celda "Servicio" tiene `.sel-grupo` +
  `.sel-item` (ambos Select2). JS: `GRUPOS` agrupa `CATALOGO` por `grupo`; `poblarGrupos(tr)`
  llena el 1er select, `poblarItems(tr, grupo)` llena el 2do al elegir grupo. Al elegir ítem:
  si `fuente==='producto'` → autofill desc/unidad/precio; si `'combo'` → comportamiento viejo
  (fetch precio con cliente). En `edit`, al precargar ítems existentes (`aplicarDatos`) los
  selects Grupo/Ítem quedan SIN elegir (solo rellena los campos + hidden ids); el ítem ya
  viene cargado. **No se guarda `producto_id` en `presupuesto_items`** (los selects son solo
  ayuda de carga) → sin migración.
- `productos/create.blade` + `edit.blade` — se agregó el campo **Precio del paquete**
  (`name="precio"`, opcional). El `ProductoController` ya validaba/guardaba `precio` (legacy).
- `layouts/app.blade` — se agregó el link **"Servicios"** (`productos.index`) en el grupo
  Ventas del sidebar (antes NO había punto de entrada al ABM de productos) + `productos.*`
  sumado a `$ventasOn`.

### Cómo carga el usuario los ítems
- **Vehículo/servicio nuevo** = un Producto nuevo (menú Ventas → **Servicios** → Nuevo),
  con Tipo de trabajo = el grupo, unidad, precio y descripción larga.
- **Grupo nuevo** = un TipoTrabajo nuevo (Configuración → Tipos de trabajo).

**Sin migración.** Deploy = `git pull` + `php artisan view:clear`.

---

## Gotchas conocidos

1. **`materiales` resource:** el parámetro de ruta debe ser `material` (no `materiale`). Se fuerza con `.parameters(['materiales' => 'material'])` en `web.php`.
2. **`tipo-trabajos` resource:** parámetro es `tipoTrabajo` (camelCase) forzado con `.parameters(['tipo-trabajos' => 'tipoTrabajo'])`.
3. **Select2 en filas dinámicas:** inicializar SOLO dentro del scope de la fila nueva, no con `$('.select2')` global, para evitar doble-init.
4. **`navigation.blade.php`:** archivo viejo de Breeze, casi no se usa. El sidebar real está en `app.blade.php`.
5. **ABM lookups (tipos/materiales/máquinas):** al eliminar, verificar primero que no haya trabajos asociados (`->exists()` check en el controller).
6. **`listas-precios` resource:** parámetro es `listaPrecio` forzado con `.parameters(['listas-precios' => 'listaPrecio'])` en `web.php`.
7. **`renovate_productos_table` migration:** todos los nuevos campos (tipo_trabajo_id, material_id, unidad, costo_mano_obra, activo, deleted_at) ya existían en la tabla cuando se corrió — se agregaron guards `if (!Schema::hasColumn(...))` para cada uno. OK al 2026-05-26.
8. **Catálogo de servicios:** NO es la tabla `productos`. Es una vista auto-generada desde la tabla pivote `maquina_material`. El catálogo = todas las combinaciones (maquina × material) compatibles. Fórmula: `(maquina.costo_X + material.costo_X) × multiplicador + MO_efectiva`. MO viene de `configuracion` (global) o se pisa en `lista_precios` (por campo mo_m2/ml/unidad nullable).
9. **Tabla `maquina_material`:** pivote sin datos extra — solo `maquina_id`, `material_id`. **Sin columnas de MO** (se eliminaron en migración `remove_mo_from_maquina_material_add_mo_to_lista_precios`). Los modelos Maquina y Material tienen `belongsToMany` con `->withTimestamps()` solamente, SIN `->withPivot(...)`.
10. **Compatibilidad material↔máquina:** se define en el create/edit de `Material` (checkboxes de máquinas). En Maquina::edit se muestran los materiales asignados como chips de solo lectura.
11. **Presupuestos — snapshot de precios:** al crear un presupuesto se snapshot `multiplicador`, `mo_m2`, `mo_ml`, `mo_unidad` en la tabla `presupuestos`. Los ítems guardan `precio_unitario` y `subtotal`. Cambios futuros de costos/listas no afectan presupuestos existentes.
12. **Presupuesto → OT:** al aprobar un presupuesto se puede convertir a OT con la acción `convertirAOT`. Crea una `OrdenTrabajo` vacía y la vincula con `presupuesto.orden_trabajo_id`. La OT NO tiene trabajos precargados — se agregan manualmente.
13. **route:cache:** si las rutas no aparecen en `route:list`, correr `php artisan route:clear` primero.
14. **Remitos — número correlativo `numero`:** es una secuencia interna SEPARADA por `tipo` (interno / oficial / electronico). El unique en DB es **compuesto `(tipo, numero)`** (migración `fix_remitos_numero_unique_per_tipo`, 2026-06-08) — antes era un unique global sobre `numero` que tiraba `Duplicate entry '1'` al chocar interno#1 con oficial#1. `Remito::proximoNumero($tipo)` da el siguiente de cada tipo (cuenta `withTrashed()`). El `numero` es solo referencia interna (R-XXXX); para oficial/electronico el número que vale es `numero_fiscal` (CAI/ARCA). `RemitoController@store` valida choque dentro del mismo tipo ANTES de pegarle a ARCA/CAI para no gastar número fiscal ni tirar 500.
15. **Remitos oficiales — vigencia del CAI por FECHA del remito (no por hoy):** `RemitoCai::vigenteParaFecha($fecha)` evalúa `vencimiento >= fecha_del_remito` (+ activo + con stock). `vigente()` = `vigenteParaFecha(now())`. `RemitoController@store` usa `vigenteParaFecha($request->fecha)` para que un remito **back-dated** (ej. fecha día 4) use el CAI que vencía ese día aunque hoy esté vencido. Si no hay CAI válido para esa fecha → error amistoso (antes creaba el oficial **sin** CAI silenciosamente → salía como R-XXXX en vez del número fiscal). En `create.blade` el label "Oficial (CAI)" se actualiza por JS según la fecha elegida (`#oficial-info`, array `cais` con mismo criterio que el server).

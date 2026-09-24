# Facturación — doble emisión e IVA exento

**Fecha:** 2026-09-23 · **Tenant afectado:** 123ploteos (plote.ar)

---

## Resumen

Un doble clic en el botón "Sí, emitir" emitió **dos facturas idénticas con dos CAE reales**
ante ARCA. Al investigarlo apareció además un segundo problema, independiente: esas facturas
se informaron como **operación exenta de IVA** cuando correspondía gravarlas al 21%.

Los dos se arreglaron y están deployados. Queda pendiente resolver los comprobantes ya
emitidos con notas de crédito.

---

## 1. La doble emisión

### Qué pasó

| # | Comprobante | Hora | CAE | Total | Presupuesto |
|---|---|---|---|---|---|
| 20 | `0003-00000038` | 19:37:**49** | 86384446924244 | $8.650.000 | 9 |
| 19 | `0003-00000037` | 19:37:**46** | 86384446914204 | $8.650.000 | 9 |

Tres segundos de diferencia, mismo presupuesto, mismo usuario, mismos ítems. Los dos
requests enviados a ARCA son idénticos salvo el número de comprobante, y los dos volvieron
aprobados (`Resultado=A`).

### Por qué pasó

Dos fallas que se sumaron:

1. **En el navegador:** el botón del modal de confirmación era un `form.submit()` sin ninguna
   protección. Dos clics = dos envíos.
2. **En el servidor:** la protección existente consultaba "¿este presupuesto ya tiene
   factura?" y después insertaba. Entre esas dos operaciones hay una ventana: dos pedidos
   casi simultáneos la atravesaron los dos antes de que ninguno llegara a insertar. ARCA
   otorgó los dos CAE.

### Cómo se arregló

**En el navegador** — el modal usa ahora un cerrojo (`emitirAhora()`) que deshabilita los
botones al primer clic. Se libera si la navegación no se concreta, para que el botón "Atrás"
no deje el formulario trabado.

**En el servidor** — cada vez que se abre el formulario se genera un identificador único
(`emision_token`). Antes de llamar a ARCA se toma un **candado atómico**: el segundo pedido
no lo consigue y **nunca llega a ARCA**. Si el primero ya terminó bien, redirige a esa
factura; si sigue en curso, avisa que espere. Hay un candado adicional por presupuesto, para
el caso de dos pestañas abiertas. Si la emisión falla, el candado se libera para poder
reintentar.

**Verificado en producción**, en los dos tenants:

| Tenant | 1er pedido | 2do pedido | Reintento tras liberar |
|---|---|---|---|
| 123ploteos | pasa | **bloqueado** | pasa |
| sistemas-integrales | pasa | **bloqueado** | pasa |

---

## 2. El IVA exento

### Qué se informó a ARCA

```
ImpTotal      8650000
ImpTotConc    0            (no gravado)
ImpNeto       0            (gravado)
ImpOpEx       8650000      <-- EXENTO
ImpIVA        0
CondicionIVAReceptorId  4  (Sujeto Exento)
```

Sin bloque `<Iva>`: la factura entera se declaró como operación exenta.

### Por qué

El cliente (MUNICIPALIDAD DE SAN FERNANDO, CUIT 30999262542) está cargado con
`condicion_iva = exento`, y el formulario aplicaba esta regla:

> si el cliente es exento → marcar todos los ítems como exentos

### Por qué es incorrecto

Son dos cosas distintas. Que el **receptor** sea un sujeto exento no hace que la **operación**
sea exenta.

> **ABC AFIP, consulta 3701004** — *"En virtud del artículo 4 de la Ley 23.349 (t.o. 1997 y
> modif.) la venta de bienes, prestación o locación de servicios que realice un sujeto
> comprendido en el mismo, se encuentra alcanzada por el gravamen de la citada ley, en la
> medida que dicha venta o prestación no goce, por una norma especial, de un tratamiento
> exentivo particular."*

Un servicio de ploteo a un municipio es una operación gravada. Lo exento es el comprador, no
la venta.

### El dato que lo confirma

Para el **mismo CUIT**, la factura `0003-00000035` del 01/09 se había emitido **gravada al
21%**: neto $7.419.008,26 + IVA $1.557.991,74. Mismo cliente, criterio opuesto tres semanas
después.

### Alcance

Se revisaron **todas** las facturas de los dos tenants en producción. Las únicas emitidas con
ítems exentos o no gravados son la `0003-00000037` y la `0003-00000038` — las mismas que
salieron duplicadas. Ninguna otra factura está afectada.

### Cómo se arregló

La condición IVA del cliente pasa a definir **solo** la letra del comprobante y el
`CondicionIVAReceptorId`. El IVA arranca siempre en 21% y se cambia a mano por ítem, cuando
esa operación puntual sí tenga exención por norma especial. Elegir un cliente ya no pisa el
IVA de los ítems cargados, y el formulario avisa el caso exento.

---

## 3. Notas de crédito precargadas

Antes, hacer una nota de crédito exigía cargar todo de nuevo a mano. Ahora:

- Botón **⊘ NC** en el listado de facturas y en el detalle de cada una.
- Buscador dentro del bloque de nota de crédito (por número o por cliente).

Cualquiera de los dos trae automáticamente cliente, datos del receptor, todos los ítems con
su IVA, y la referencia fiscal del comprobante original que ARCA exige.

Copia el tipo de IVA **guardado** en cada ítem, así la nota de crédito espeja exactamente lo
que se declaró y cancela bien.

---

## 4. Revisión del código

Antes de dar el arreglo por bueno se corrió una revisión automatizada: seis revisores sobre
distintos ángulos del cambio, y cada hallazgo sometido a tres verificadores independientes
cuya tarea era refutarlo. 32 hallazgos en total.

**Encontró un defecto crítico en el propio arreglo.** En este sistema multi-empresa, el
componente de cache no es el estándar de Laravel sino uno propio de la librería de
multi-tenancy, que reescribe las llamadas de una forma incompatible con la versión actual del
framework. Consecuencia: el candado **nunca se tomaba** (fallaba en silencio) y una llamada
posterior **reventaba justo después de obtener el CAE**, dejando la factura a medio guardar
con el número ya consumido en AFIP.

Habría quedado peor que el problema original. Se corrigió y se verificó sobre la base real.

Otros defectos encontrados y corregidos: se podía emitir una Factura A real creyendo emitir
una nota de crédito; no se validaba el comprobante de origen; el buscador no encontraba por
el formato que muestra la propia app; el cerrojo del navegador podía trabar el formulario.

---

## 5. Qué quedó verificado

**Sí:** el candado en los dos tenants de producción, los cuatro guardas de la precarga de
nota de crédito (nota de crédito, factura anulada, factura válida, id inexistente), el
buscador en sus distintos formatos, y que todos los endpoints respondan sin error.

**No:** no se hizo una emisión real de prueba de punta a punta — habría generado un
comprobante fiscal verdadero. El recorrido visual conviene probarlo la primera vez a mano.

---

## 6. Pendiente

1. **Nota de crédito de la `0003-00000038`** — duplicado puro, documenta una operación que no
   existió. Por el total, **exenta**, para espejar lo declarado.
2. **Nota de crédito de la `0003-00000037`** — un comprobante con CAE no se puede corregir; si
   el criterio correcto es el gravado, el único mecanismo es nota de crédito + reemitir.
3. **Reemitir la operación real gravada al 21%** — si los $8.650.000 eran precio final:
   neto **$7.148.760,33** + IVA **$1.501.239,67**. Si eran netos, el total pasa a $10.466.500;
   eso lo define el presupuesto / la orden de compra del municipio.

**La decisión sobre el punto 2 es del contador.** Lo que sí es un hecho técnico: el sistema no
tiene otra forma de corregir el IVA de un comprobante ya autorizado.

Dos cosas juegan a favor: las facturas son **del mismo día**, así que no hay declaración
presentada todavía; y al ser Facturas **B** el IVA no va discriminado en el papel, así que el
municipio ve el mismo importe en los tres documentos. Cero fricción con el cliente.

---

## 7. Cambios deployados

```
ac5622c  docs: cert plote.ar ahora cubre sistemas-integrales.plote.ar
0033471  merge: anti doble emisión, NC precargada y fix de IVA exento
cb59542  docs: gotchas de cache tenant
01d2f19  fix: el IVA de los ítems no depende de la condición del cliente
fdabe35  fix: evitar doble emisión y precargar la NC desde el comprobante
```

Sin migración de base de datos. Deploy: `git pull` + `view:clear` + `route:cache`.

**Aparte:** se agregó `sistemas-integrales.plote.ar` al certificado SSL. Hasta hoy ese
subdominio servía la app correctamente pero el navegador mostraba advertencia de seguridad,
porque el certificado no lo incluía. Ya entra con candado normal. De paso el certificado se
renovó: vence el 2026-12-22 y cubre los cinco dominios.

# Módulo Cajero — Celeste Boutique

## Descripción general

El módulo de cajero es la interfaz principal para el registro de ventas en el punto de venta. Permite al cajero gestionar el carrito de compras, procesar ventas, emitir recibos, registrar devoluciones y consultar el historial de transacciones propias.

El acceso está restringido exclusivamente a usuarios con rol `cajero` o `administrador`. Cualquier intento de acceso sin sesión activa o con rol diferente redirige al login.

---

## Archivos involucrados

| Archivo | Tipo | Responsabilidad |
|---|---|---|
| `views/dashboard/cajero.php` | Vista | Interfaz principal del módulo (tabs, carrito, formularios) |
| `controllers/VentaController.php` | Controlador | Procesa el registro de ventas y devoluciones |
| `controllers/ReciboController.php` | Controlador | Devuelve el HTML del recibo vía AJAX |
| `models/Venta.php` | Modelo | Queries a la base de datos (ventas, detalle, devoluciones, historial) |
| `views/partials/recibo_template.php` | Partial | Plantilla HTML reutilizable del recibo de venta |

---

## Estructura de la vista

La vista `cajero.php` está organizada en **4 pestañas (tabs)**:

```
┌─────────────────────────────────────────────────────┐
│  Nueva Venta │ Devoluciones │ Recibo │ Historial     │
└─────────────────────────────────────────────────────┘
```

La pestaña activa se controla mediante el parámetro GET `?tab=<nombre>` y se persiste en la URL con `history.replaceState`.

---

## Tab 1 — Nueva Venta

### Funcionamiento

1. Se carga el catálogo de productos activos con stock disponible (`obtenerProductos()`).
2. El cajero puede buscar productos por nombre con filtrado en tiempo real.
3. Al hacer clic en **+**, el producto se agrega al carrito en memoria (objeto JS `carrito`).
4. El cajero puede ajustar cantidades o eliminar ítems del carrito.
5. Al confirmar la venta, se muestra un diálogo de confirmación (SweetAlert2) con el total.
6. El formulario envía los datos por POST a `VentaController.php?accion=registrar`.
7. Si la venta es exitosa, redirige automáticamente al tab de **Recibo** con el ID de la venta.

### Validaciones en el cliente

- No permite agregar más unidades de las disponibles en stock.
- No permite registrar una venta con el carrito vacío.

### Campos del formulario

| Campo | Descripción |
|---|---|
| `cliente_nombre` | Nombre del cliente (opcional, por defecto "Cliente general") |
| `items[n][id_producto]` | ID del producto en la línea n |
| `items[n][cantidad]` | Cantidad solicitada en la línea n |

---

## Tab 2 — Devoluciones

### Funcionamiento

1. El cajero ingresa el **número de venta** a devolver y un motivo opcional.
2. Se muestra un diálogo de confirmación antes de enviar.
3. El formulario envía por POST a `VentaController.php?accion=devolucion`.
4. Al procesar la devolución:
   - La venta cambia de estado a `anulada`.
   - El stock de cada producto del detalle se repone automáticamente.
   - Se registra un registro en la tabla `devoluciones`.
5. En el panel derecho se muestra el historial de devoluciones del cajero actual.

### Restricciones

- No se puede devolver una venta que ya esté en estado `anulada`.
- No se puede registrar más de una devolución por venta.

---

## Tab 3 — Recibo

### Funcionamiento

- Si se llega desde una venta recién registrada (`?tab=recibo&id=N`), el recibo se renderiza directamente en el servidor.
- También permite buscar cualquier recibo por número de venta mediante una llamada **AJAX** a `ReciboController.php?id=N`.
- El recibo cargado puede imprimirse con `window.print()`. Los elementos con clase `no-print` se ocultan automáticamente al imprimir.

### Contenido del recibo

| Campo | Descripción |
|---|---|
| N° Venta | Identificador único de la venta |
| Fecha | Fecha y hora de la transacción |
| Cliente | Nombre del cliente |
| Cajero | Nombre del usuario que registró la venta |
| Estado | `Completada` o `Anulada` |
| Detalle | Tabla con producto, cantidad, precio unitario y subtotal |
| Total | Monto total de la venta |

---

## Tab 4 — Historial

### Funcionamiento

- Muestra las últimas 200 ventas del cajero autenticado, ordenadas de más reciente a más antigua.
- Permite filtrar por **rango de fechas** y **estado** (completada / anulada) directamente en el cliente (sin recarga de página).
- Desde cada fila se puede acceder al recibo de esa venta con el botón **Recibo**.
- El historial completo puede imprimirse con el botón **Imprimir**.

---

## Controlador — VentaController.php

Actúa como dispatcher según el parámetro GET `accion`:

### `accion=registrar` (POST)

```
POST /controllers/VentaController.php?accion=registrar
```

**Flujo:**
1. Valida sesión y rol.
2. Recibe `cliente_nombre` e `items[]`.
3. Llama a `Venta::crearVenta()`.
4. Si es exitoso → redirige a `cajero.php?tab=recibo&id={id}`.
5. Si falla → guarda alerta en sesión y redirige al tab de ventas.

### `accion=devolucion` (POST)

```
POST /controllers/VentaController.php?accion=devolucion
```

**Flujo:**
1. Valida sesión y rol.
2. Recibe `id_venta` y `motivo`.
3. Llama a `Venta::registrarDevolucion()`.
4. Redirige al tab de devoluciones con alerta de éxito o error.

---

## Controlador — ReciboController.php

```
GET /controllers/ReciboController.php?id={id_venta}
```

Devuelve el HTML del recibo para ser inyectado vía AJAX en el tab de Recibo. Verifica sesión y rol antes de responder. Si la venta no existe, devuelve un mensaje de error en HTML.

---

## Modelo — Venta.php

### Métodos principales

| Método | Descripción |
|---|---|
| `obtenerProductos()` | Retorna productos activos con stock > 0, con su categoría |
| `obtenerProductoPorId(int $id)` | Retorna un producto activo por ID |
| `crearVenta(int $idUsuario, string $cliente, array $items)` | Crea la venta en transacción: inserta en `ventas`, `venta_detalle` y descuenta stock |
| `obtenerVentaPorId(int $id)` | Retorna los datos de una venta con el nombre del cajero |
| `obtenerDetalleVenta(int $idVenta)` | Retorna el detalle de productos de una venta |
| `historial(int $idUsuario, ...)` | Retorna el historial de ventas con filtros opcionales de fecha y estado |
| `registrarDevolucion(int $idVenta, int $idUsuario, string $motivo)` | Anula la venta, repone stock y registra la devolución en transacción |
| `obtenerDevoluciones(int $idUsuario)` | Retorna las devoluciones registradas por el cajero |

### Transacciones

Tanto `crearVenta()` como `registrarDevolucion()` utilizan transacciones PDO (`beginTransaction` / `commit` / `rollBack`) para garantizar la integridad de los datos ante cualquier error.

---

## Tablas de base de datos utilizadas

| Tabla | Uso |
|---|---|
| `ventas` | Cabecera de cada venta (cliente, total, estado, cajero) |
| `venta_detalle` | Líneas de producto por venta (cantidad, precio unitario, subtotal) |
| `productos` | Catálogo de productos; el stock se actualiza en cada venta/devolución |
| `categorias_productos` | Categoría asociada a cada producto |
| `devoluciones` | Registro de devoluciones (venta anulada, monto, motivo, cajero) |
| `usuarios` | Datos del cajero autenticado |

---

## Flujo completo de una venta

```
Cajero selecciona productos
        │
        ▼
  Carrito en memoria (JS)
        │
        ▼
  Confirma con SweetAlert2
        │
        ▼
  POST → VentaController (accion=registrar)
        │
        ▼
  Venta::crearVenta() — transacción
  ├── Valida stock por producto
  ├── INSERT ventas
  ├── INSERT venta_detalle (por cada ítem)
  └── UPDATE productos (descuenta stock)
        │
        ▼
  Redirige a cajero.php?tab=recibo&id={N}
        │
        ▼
  Cajero imprime el recibo
```

---

## Flujo completo de una devolución

```
Cajero ingresa N° de venta
        │
        ▼
  Confirma con SweetAlert2
        │
        ▼
  POST → VentaController (accion=devolucion)
        │
        ▼
  Venta::registrarDevolucion() — transacción
  ├── Verifica que la venta exista y no esté anulada
  ├── Verifica que no tenga devolución previa
  ├── INSERT devoluciones
  ├── UPDATE productos (repone stock por cada ítem)
  └── UPDATE ventas SET estado='anulada'
        │
        ▼
  Redirige a cajero.php?tab=devoluciones
```

---

## Seguridad

- La sesión se verifica al inicio de cada archivo (vista y controladores).
- El rol debe ser `cajero` o `administrador`; cualquier otro rol es rechazado.
- Los datos de entrada se sanitizan con `htmlspecialchars()` en la vista y se usan **prepared statements** con PDO en todos los queries del modelo.
- Las cantidades y IDs se castean explícitamente a `int` antes de usarse en queries.

---

## Dependencias externas

| Librería | Uso |
|---|---|
| [SweetAlert2](https://sweetalert2.github.io/) | Diálogos de confirmación y alertas en el cliente |
| [Font Awesome](https://fontawesome.com/) | Iconografía de la interfaz |

# Módulo Bodeguero — Celeste Boutique

## Descripción general

El módulo de bodeguero centraliza la gestión del inventario físico del almacén. Permite registrar entradas de mercancía, aplicar ajustes por pérdidas o mermas, crear y editar productos, consultar el historial completo de movimientos y visualizar reportes de stock crítico y actividad por proveedor.

El acceso está restringido a usuarios con rol `bodeguero` o `administrador`. Cualquier intento de acceso sin sesión activa o con rol diferente redirige al login.

---

## Archivos involucrados

| Archivo | Tipo | Responsabilidad |
|---|---|---|
| `views/dashboard/bodeguero.php` | Vista | Interfaz principal del módulo (tabs, tablas, modales) |
| `controllers/InventarioController.php` | Controlador | Procesa creación/edición de productos, entradas y ajustes |
| `controllers/ProveedorController.php` | Controlador | CRUD de proveedores (solo administrador) |
| `models/Inventario.php` | Modelo | Queries de productos, movimientos, categorías, proveedores y reportes |
| `models/Proveedor.php` | Modelo | Queries CRUD de la tabla `proveedores` |

---

## Estructura de la vista

La vista `bodeguero.php` está organizada en **5 pestañas (tabs)**:

```
┌──────────────────────────────────────────────────────────────────┐
│  Inventario │ Entradas │ Ajustes │ Movimientos │ Reportes        │
└──────────────────────────────────────────────────────────────────┘
```

La pestaña activa se controla mediante el parámetro GET `?tab=<nombre>` y se persiste en la URL con `history.replaceState`.

---

## Tab 1 — Inventario

### Funcionamiento

- Muestra la tabla completa de productos con nombre, categoría, precio, stock y estado (activo/inactivo).
- Si hay productos con stock ≤ 5, se muestra un **banner de alerta** en la parte superior indicando la cantidad de productos en estado crítico.
- El bodeguero puede buscar productos por nombre o categoría con filtrado en tiempo real.
- Desde la columna de acciones se puede abrir el modal de **edición** de cualquier producto.
- El botón **Nuevo Producto** abre el modal de creación.

### Indicadores de stock

| Condición | Badge |
|---|---|
| Stock > 5 | Verde (OK) |
| Stock 1–5 | Amarillo (advertencia) |
| Stock = 0 | Rojo (sin stock) |

### Modal — Crear Producto

Campos del formulario enviado a `InventarioController.php?accion=crear_producto`:

| Campo | Tipo | Requerido |
|---|---|---|
| `nombre` | Texto | Sí |
| `id_categoria` | Select | Sí |
| `precio` | Decimal | Sí |
| `stock` | Entero | No (default 0) |
| `descripcion` | Textarea | No |

### Modal — Editar Producto

Campos del formulario enviado a `InventarioController.php?accion=editar_producto`:

| Campo | Tipo | Requerido |
|---|---|---|
| `id_producto` | Hidden | Sí |
| `nombre` | Texto | Sí |
| `id_categoria` | Select | Sí |
| `precio` | Decimal | Sí |
| `descripcion` | Textarea | No |

> El stock **no se edita** desde este modal. Solo se modifica mediante entradas o ajustes para mantener trazabilidad.

---

## Tab 2 — Entradas

### Funcionamiento

1. El bodeguero selecciona el producto, ingresa la cantidad recibida, el proveedor (opcional) y un motivo.
2. El formulario envía por POST a `InventarioController.php?accion=entrada`.
3. Se registra un movimiento de tipo `entrada` en `movimientos_inventario` y se incrementa el stock del producto.
4. En el panel derecho se muestran las últimas 15 entradas registradas.

### Campos del formulario

| Campo | Descripción |
|---|---|
| `id_producto` | Producto que recibe la mercancía |
| `cantidad` | Unidades recibidas (mínimo 1) |
| `id_proveedor` | Proveedor asociado (opcional) |
| `motivo` | Observación de la entrada (ej: "Compra a proveedor") |

---

## Tab 3 — Ajustes

### Funcionamiento

1. El bodeguero selecciona el producto, el tipo de ajuste, la cantidad y un motivo obligatorio.
2. El formulario envía por POST a `InventarioController.php?accion=ajuste`.
3. Según el tipo, el stock se incrementa o decrementa.
4. En el panel derecho se muestran los últimos 15 ajustes registrados.

### Tipos de ajuste

| Tipo | Efecto en stock | Descripción |
|---|---|---|
| `perdida` | Negativo (−) | Producto extraviado o robado |
| `merma` | Negativo (−) | Deterioro, vencimiento o daño |
| `ajuste_negativo` | Negativo (−) | Corrección manual a la baja |
| `ajuste_positivo` | Positivo (+) | Corrección manual al alza |

> Los ajustes negativos validan que el stock disponible sea suficiente antes de aplicarse.

---

## Tab 4 — Movimientos

### Funcionamiento

- Muestra el historial completo de movimientos (hasta 300 registros), ordenados del más reciente al más antiguo.
- Incluye: producto, tipo de movimiento, cantidad (con signo + o −), motivo, usuario que lo registró y fecha.
- Permite filtrar por tipo de movimiento desde un selector sin recargar la página.
- El historial puede imprimirse con el botón **Imprimir**.

### Colores de los badges por tipo

| Tipo | Color |
|---|---|
| `entrada` | Azul |
| `perdida`, `merma`, `ajuste_negativo` | Rojo |
| `ajuste_positivo` | Verde |

---

## Tab 5 — Reportes

### Stock crítico

Tabla de productos activos con stock ≤ 5 unidades, ordenados de menor a mayor stock. Muestra nombre, categoría, stock actual y nivel de alerta (sin stock / stock bajo).

### Reporte por proveedor

Tabla agrupada por proveedor con:
- Cantidad de productos distintos recibidos
- Total de unidades ingresadas
- Fecha de la última entrada registrada

Ambos reportes pueden imprimirse directamente desde el navegador.

---

## Controlador — InventarioController.php

Actúa como dispatcher según el parámetro GET `accion`:

### `accion=crear_producto` (POST)

```
POST /controllers/InventarioController.php?accion=crear_producto
```

Llama a `Inventario::crearProducto()`. Redirige al tab `inventario` con alerta de éxito o error.

### `accion=editar_producto` (POST)

```
POST /controllers/InventarioController.php?accion=editar_producto
```

Llama a `Inventario::actualizarProducto()`. Redirige al tab `inventario`.

### `accion=entrada` (POST)

```
POST /controllers/InventarioController.php?accion=entrada
```

Llama a `Inventario::registrarEntrada()`. Redirige al tab `entradas`.

### `accion=ajuste` (POST)

```
POST /controllers/InventarioController.php?accion=ajuste
```

Llama a `Inventario::registrarAjuste()`. Redirige al tab `ajustes`.

---

## Controlador — ProveedorController.php

Gestiona el CRUD completo de proveedores. **Accesible únicamente por el administrador.** Redirige siempre a `views/dashboard/proveedores.php`.

| Acción GET | Método | Descripción |
|---|---|---|
| `accion=crear` | POST | Crea un nuevo proveedor. Valida nombre y unicidad de RUC |
| `accion=editar` | POST | Actualiza datos de un proveedor existente |
| `accion=toggleEstado` | GET | Activa o desactiva un proveedor (`activo` 0/1) |
| `accion=eliminar` | GET | Elimina un proveedor (falla si tiene registros asociados) |

---

## Modelo — Inventario.php

### Métodos principales

| Método | Descripción |
|---|---|
| `obtenerProductos(string $buscar)` | Lista todos los productos con categoría y proveedor; acepta filtro de búsqueda |
| `obtenerProductoPorId(int $id)` | Retorna un producto con su categoría por ID |
| `crearProducto(array $d)` | Inserta un nuevo producto en la tabla `productos` |
| `actualizarProducto(int $id, array $d)` | Actualiza nombre, categoría, precio y descripción de un producto |
| `registrarEntrada(int $idProducto, int $cantidad, int $idUsuario, string $motivo, ?int $idProveedor)` | Registra movimiento de entrada y suma stock en transacción |
| `registrarAjuste(int $idProducto, int $cantidad, string $tipo, int $idUsuario, string $motivo)` | Registra ajuste y suma/resta stock según tipo, en transacción |
| `obtenerMovimientos(string $tipo, int $idProducto)` | Historial de movimientos con filtros opcionales (hasta 300 registros) |
| `productosStockBajo(int $umbral)` | Productos activos con stock ≤ umbral (default 5) |
| `reportePorProveedor()` | Agrupado de entradas por proveedor con totales |
| `obtenerCategorias()` | Lista de categorías activas para selects |
| `obtenerProveedores()` | Lista de proveedores activos para selects |

### Transacciones

`registrarEntrada()` y `registrarAjuste()` usan transacciones PDO para garantizar que el movimiento y la actualización de stock sean atómicos.

---

## Modelo — Proveedor.php

| Método | Descripción |
|---|---|
| `obtenerTodos()` | Lista todos los proveedores ordenados por nombre |
| `obtenerPorId(int $id)` | Retorna un proveedor por ID |
| `existeRuc(string $ruc, int $excluirId)` | Verifica si el RUC ya está registrado (excluye el propio ID al editar) |
| `crear(array $d)` | Inserta un nuevo proveedor |
| `actualizar(int $id, array $d)` | Actualiza los datos de un proveedor |
| `cambiarEstado(int $id, int $activo)` | Activa o desactiva un proveedor |
| `eliminar(int $id)` | Elimina un proveedor (lanza error si tiene registros asociados) |

---

## Tablas de base de datos utilizadas

| Tabla | Uso |
|---|---|
| `productos` | Catálogo de productos; el stock se actualiza en cada entrada o ajuste |
| `categorias_productos` | Categoría asociada a cada producto |
| `movimientos_inventario` | Registro de todas las entradas y ajustes con tipo, cantidad, motivo, usuario y proveedor |
| `proveedores` | Datos de los proveedores; se asocian a entradas de mercancía |
| `usuarios` | Nombre del bodeguero que registró cada movimiento |

---

## Flujo completo de una entrada de mercancía

```
Bodeguero selecciona producto, cantidad y proveedor
        │
        ▼
  POST → InventarioController (accion=entrada)
        │
        ▼
  Inventario::registrarEntrada() — transacción
  ├── INSERT movimientos_inventario (tipo='entrada')
  └── UPDATE productos SET stock = stock + cantidad
        │
        ▼
  Redirige a bodeguero.php?tab=entradas
  con alerta de éxito o error
```

---

## Flujo completo de un ajuste de inventario

```
Bodeguero selecciona producto, tipo de ajuste y cantidad
        │
        ▼
  POST → InventarioController (accion=ajuste)
        │
        ▼
  Inventario::registrarAjuste() — transacción
  ├── Verifica stock suficiente (si es ajuste negativo)
  ├── INSERT movimientos_inventario (tipo=perdida|merma|ajuste_*)
  └── UPDATE productos SET stock = stock ± cantidad
        │
        ▼
  Redirige a bodeguero.php?tab=ajustes
  con alerta de éxito o error
```

---

## Seguridad

- La sesión se verifica al inicio de la vista y del controlador.
- El rol debe ser `bodeguero` o `administrador` para acceder al inventario; solo `administrador` puede gestionar proveedores.
- Todos los queries del modelo usan **prepared statements** con PDO.
- Los valores numéricos (IDs, cantidades, precios) se castean explícitamente antes de usarse.
- Los datos de salida en la vista se sanitizan con `htmlspecialchars()`.

---

## Dependencias externas

| Librería | Uso |
|---|---|
| [SweetAlert2](https://sweetalert2.github.io/) | Alertas y notificaciones en el cliente |
| [Font Awesome](https://fontawesome.com/) | Iconografía de la interfaz |

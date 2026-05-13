# Módulo Administrador — Celeste Boutique

**Sistema:** Sistema de Gestión Celeste Boutique  
**Versión:** 1.0  
**Fecha:** Mayo 2026  
**Rol requerido:** `administrador`

---

## Tabla de Contenidos

1. [Descripción general](#1-descripción-general)
2. [Estructura de archivos](#2-estructura-de-archivos)
3. [Base de datos](#3-base-de-datos)
4. [Submódulo: Gestión de Usuarios](#4-submódulo-gestión-de-usuarios)
5. [Submódulo: Categorías y Productos](#5-submódulo-categorías-y-productos)
6. [Submódulo: Proveedores](#6-submódulo-proveedores)
7. [Submódulo: Reportes y Estadísticas](#7-submódulo-reportes-y-estadísticas)
8. [Autenticación y control de acceso](#8-autenticación-y-control-de-acceso)
9. [Flujo de navegación](#9-flujo-de-navegación)
10. [Convenciones y patrones usados](#10-convenciones-y-patrones-usados)

---

## 1. Descripción general

El módulo de administrador es el núcleo de gestión del sistema. Permite al usuario con rol `administrador` controlar todos los aspectos operativos: usuarios del sistema, catálogo de productos, proveedores y visualización de estadísticas en tiempo real.

**Acceso:** Tras iniciar sesión con rol `administrador`, el sistema redirige automáticamente a `views/dashboard/admin.php`.

**Sidebar — secciones disponibles:**

| Sección | Enlace | Descripción |
|---|---|---|
| Gestión | `admin.php` | Gestión de usuarios |
| Gestión | `productos.php` | Categorías y productos |
| Gestión | `proveedores.php` | Proveedores |
| Análisis | `reportes.php` | Reportes y estadísticas |

---

## 2. Estructura de archivos

```
sistema-boutique/
├── config/
│   └── database.php                  # Conexión PDO a MySQL
│
├── models/
│   ├── Usuario.php                   # CRUD de usuarios
│   ├── Categoria.php                 # CRUD de categorías
│   ├── Proveedor.php                 # CRUD de proveedores
│   └── Reporte.php                   # Consultas de estadísticas
│
├── controllers/
│   ├── AdminUsuarioController.php    # Acciones sobre usuarios
│   ├── CategoriaController.php       # Acciones sobre categorías
│   ├── ProveedorController.php       # Acciones sobre proveedores
│   └── ReporteController.php         # Endpoint JSON de reportes
│
├── views/
│   ├── dashboard/
│   │   ├── admin.php                 # Vista: gestión de usuarios
│   │   ├── productos.php             # Vista: categorías y productos
│   │   ├── proveedores.php           # Vista: proveedores
│   │   └── reportes.php              # Vista: reportes y estadísticas
│   └── layouts/
│       ├── header.php                # Cabecera HTML + sesión
│       ├── sidebar.php               # Menú lateral por rol
│       └── footer.php                # Pie de página
```

---

## 3. Base de datos

### Tabla `usuarios`

| Columna | Tipo | Descripción |
|---|---|---|
| `id_usuario` | INT PK AI | Identificador único |
| `nombre` | VARCHAR(100) | Nombre completo |
| `correo` | VARCHAR(150) | Correo electrónico (único) |
| `password` | VARCHAR(255) | Hash bcrypt |
| `id_rol` | INT | 1=Administrador, 2=Cajero, 3=Bodeguero |
| `telefono` | VARCHAR(30) | Teléfono opcional |
| `activo` | TINYINT(1) | 1=activo, 0=inactivo |
| `created_at` | TIMESTAMP | Fecha de registro |

### Tabla `categorias_productos`

| Columna | Tipo | Descripción |
|---|---|---|
| `id_categoria` | INT PK AI | Identificador único |
| `nombre` | VARCHAR(100) | Nombre de la categoría |
| `descripcion` | VARCHAR(255) | Descripción opcional |
| `cantidad` | INT | Cantidad referencial de productos |
| `activo` | TINYINT(1) | Estado |
| `created_at` | TIMESTAMP | Fecha de creación |

### Tabla `productos`

| Columna | Tipo | Descripción |
|---|---|---|
| `id_producto` | INT PK AI | Identificador único |
| `id_categoria` | INT FK | Referencia a `categorias_productos` |
| `nombre` | VARCHAR(150) | Nombre del producto |
| `descripcion` | VARCHAR(255) | Descripción opcional |
| `precio` | DECIMAL(10,2) | Precio unitario |
| `stock` | INT | Unidades disponibles |
| `activo` | TINYINT(1) | Estado |
| `created_at` | TIMESTAMP | Fecha de registro |

### Tabla `proveedores`

| Columna | Tipo | Descripción |
|---|---|---|
| `id_proveedor` | INT PK AI | Identificador único |
| `nombre` | VARCHAR(150) | Nombre del proveedor |
| `ruc` | VARCHAR(20) | RUC/NIT (único, opcional) |
| `telefono` | VARCHAR(30) | Teléfono |
| `correo` | VARCHAR(150) | Correo electrónico |
| `direccion` | VARCHAR(255) | Dirección física |
| `activo` | TINYINT(1) | 1=activo, 0=inactivo |
| `created_at` | TIMESTAMP | Fecha de registro |

---

## 4. Submódulo: Gestión de Usuarios

### Vista
`views/dashboard/admin.php`

Muestra una tabla con todos los usuarios registrados. Cada fila incluye nombre, correo, rol con badge de color, estado activo/inactivo y botones de acción.

**Badges de rol:**

| id_rol | Etiqueta | Color |
|---|---|---|
| 1 | Administrador | Azul |
| 2 | Cajero | Naranja |
| 3 | Bodeguero | Verde |

### Controlador
`controllers/AdminUsuarioController.php`

Acceso restringido: solo `rol === 'administrador'`.

| Acción (`?accion=`) | Método HTTP | Descripción |
|---|---|---|
| `crear` | POST | Registra un nuevo usuario |
| `editar` | POST | Actualiza nombre, rol y contraseña (opcional) |
| `toggleEstado` | GET | Activa o desactiva un usuario |
| `eliminar` | GET | Elimina permanentemente un usuario |

**Parámetros POST — crear:**

| Campo | Requerido | Descripción |
|---|---|---|
| `nombres` | ✅ | Nombres del usuario |
| `apellidos` | ✅ | Apellidos del usuario |
| `email` | ✅ | Correo electrónico único |
| `password` | ✅ | Contraseña (se hashea con bcrypt) |
| `rol` | ✅ | `administrador` / `cajero` / `bodeguero` |
| `telefono` | ❌ | Teléfono opcional |

**Parámetros POST — editar:**

| Campo | Requerido | Descripción |
|---|---|---|
| `id_usuario` | ✅ | ID del usuario a editar |
| `nombres` | ✅ | Nuevos nombres |
| `apellidos` | ✅ | Nuevos apellidos |
| `rol` | ✅ | Nuevo rol |
| `password` | ❌ | Nueva contraseña (solo si se proporciona) |

> **Nota:** El correo electrónico **no se puede modificar** una vez creado el usuario.

### Modelo
`models/Usuario.php`

| Método | Descripción |
|---|---|
| `existeCorreo($email)` | Verifica si el correo ya está registrado |
| `obtenerPorEmail($email)` | Busca usuario activo por correo (usado en login) |
| `registrar($datos)` | Inserta nuevo usuario con transacción |
| `obtenerTodos()` | Retorna todos los usuarios ordenados por fecha |
| `obtenerPorId($id)` | Retorna un usuario por su ID |
| `actualizar($id, $datos)` | Actualiza nombre, rol y contraseña opcional |
| `cambiarEstado($id, $activo)` | Activa o desactiva un usuario |
| `eliminar($id)` | Elimina un usuario por ID |

---

## 5. Submódulo: Categorías y Productos

### Vista
`views/dashboard/productos.php`

Organizada en dos pestañas:

- **Categorías** — tabla con nombre, descripción, cantidad de productos y acciones (editar, eliminar)
- **Productos** — módulo en desarrollo (implementado por el bodeguero)

### Controlador
`controllers/CategoriaController.php`

Acceso restringido: solo `rol === 'administrador'`.  
Redirige siempre a `views/dashboard/productos.php`.

| Acción (`?accion=`) | Método HTTP | Descripción |
|---|---|---|
| `crear` | POST | Crea una nueva categoría |
| `editar` | POST | Actualiza nombre, descripción y cantidad |
| `eliminar` | GET | Elimina la categoría (falla si tiene productos) |

**Parámetros POST — crear / editar:**

| Campo | Requerido | Descripción |
|---|---|---|
| `nombre` | ✅ | Nombre único de la categoría |
| `descripcion` | ❌ | Descripción opcional |
| `cantidad` | ✅ | Cantidad referencial de productos |
| `id_categoria` | ✅ (editar) | ID de la categoría a editar |

**Validaciones:**
- El nombre no puede estar vacío.
- No se permiten nombres duplicados (se excluye el propio ID al editar).
- No se puede eliminar una categoría que tenga productos asociados (restricción de FK).

### Modelo
`models/Categoria.php`

| Método | Descripción |
|---|---|
| `obtenerTodas()` | Retorna todas las categorías ordenadas por nombre |
| `obtenerActivas()` | Retorna solo categorías activas (para selects) |
| `obtenerPorId($id)` | Retorna una categoría por ID |
| `existeNombre($nombre, $excluirId)` | Verifica nombre duplicado |
| `crear($datos)` | Inserta nueva categoría |
| `actualizar($id, $datos)` | Actualiza nombre, descripción y cantidad |
| `eliminar($id)` | Elimina categoría (captura error de FK) |

---

## 6. Submódulo: Proveedores

### Vista
`views/dashboard/proveedores.php`

Tabla con todos los proveedores. Muestra nombre, RUC, teléfono, correo y estado. Permite crear, editar, activar/desactivar y eliminar proveedores mediante modales.

### Controlador
`controllers/ProveedorController.php`

Acceso restringido: solo `rol === 'administrador'`.  
Redirige siempre a `views/dashboard/proveedores.php`.

| Acción (`?accion=`) | Método HTTP | Descripción |
|---|---|---|
| `crear` | POST | Registra un nuevo proveedor |
| `editar` | POST | Actualiza datos del proveedor |
| `toggleEstado` | GET | Activa o desactiva el proveedor |
| `eliminar` | GET | Elimina el proveedor |

**Parámetros POST — crear / editar:**

| Campo | Requerido | Descripción |
|---|---|---|
| `nombre` | ✅ | Nombre del proveedor |
| `ruc` | ❌ | RUC/NIT (único si se proporciona) |
| `telefono` | ❌ | Teléfono de contacto |
| `correo` | ❌ | Correo electrónico |
| `direccion` | ❌ | Dirección física |
| `id_proveedor` | ✅ (editar) | ID del proveedor a editar |

**Parámetros GET — toggleEstado:**

| Parámetro | Descripción |
|---|---|
| `id` | ID del proveedor |
| `estado` | Estado actual (0 o 1); el controlador lo invierte |

**Validaciones:**
- El nombre es obligatorio.
- El RUC no puede estar duplicado (se excluye el propio ID al editar).
- No se puede eliminar un proveedor con registros asociados.

### Modelo
`models/Proveedor.php`

| Método | Descripción |
|---|---|
| `obtenerTodos()` | Retorna todos los proveedores ordenados por nombre |
| `obtenerPorId($id)` | Retorna un proveedor por ID |
| `existeRuc($ruc, $excluirId)` | Verifica RUC duplicado |
| `crear($datos)` | Inserta nuevo proveedor |
| `actualizar($id, $datos)` | Actualiza datos del proveedor |
| `cambiarEstado($id, $activo)` | Activa o desactiva |
| `eliminar($id)` | Elimina proveedor (captura error de FK) |

---

## 7. Submódulo: Reportes y Estadísticas

### Vista
`views/dashboard/reportes.php`

Carga todos los datos vía **AJAX** (fetch) al endpoint `ReporteController.php`. No recarga la página. Muestra:

**Tarjetas KPI:**

| Indicador | Fuente |
|---|---|
| Usuarios registrados | `COUNT(*)` en `usuarios` |
| Productos activos | `COUNT(*)` en `productos WHERE activo=1` |
| Proveedores activos | `COUNT(*)` en `proveedores WHERE activo=1` |
| Categorías activas | `COUNT(*)` en `categorias_productos WHERE activo=1` |
| Valor del inventario | `SUM(precio * stock)` en `productos` |
| Stock crítico (≤5) | `COUNT(*)` en `productos WHERE stock<=5` |

**Gráficos (Chart.js v4.4.3):**

| Gráfico | Tipo | Datos |
|---|---|---|
| Productos por categoría | Doughnut | `productosPorCategoria()` |
| Usuarios por rol | Barras | `usuariosPorRol()` |
| Nuevos usuarios / mes | Línea | `usuariosPorMes()` (últimos 12 meses) |

**Tablas con tabs:**

| Tab | Datos |
|---|---|
| Top Stock | Top 10 productos con mayor stock |
| Stock Crítico | Productos con stock ≤ 5 |
| Proveedores | Proveedores activos con fecha de registro |

### Controlador
`controllers/ReporteController.php`

Endpoint JSON. Acceso restringido: solo `rol === 'administrador'`.  
Devuelve `Content-Type: application/json`.

| Acción (`?accion=`) | Respuesta |
|---|---|
| `resumen` | Objeto con 6 KPIs numéricos |
| `productos_por_categoria` | Array `[{categoria, total}]` |
| `usuarios_por_rol` | Array `[{rol, total}]` |
| `usuarios_por_mes` | Array `[{mes, mes_orden, total}]` |
| `top_productos_stock` | Array con top 10 productos |
| `stock_critico` | Array con productos stock ≤ 5 |
| `proveedores_activos` | Array con proveedores activos |

**Respuesta de error:**
```json
{ "error": "Descripción del error" }
```
Códigos HTTP: `400` acción no reconocida, `403` acceso denegado, `500` error interno.

### Modelo
`models/Reporte.php`

| Método | Retorno | Descripción |
|---|---|---|
| `totalUsuarios()` | `int` | Total de usuarios |
| `totalProductos()` | `int` | Productos activos |
| `totalProveedores()` | `int` | Proveedores activos |
| `totalCategorias()` | `int` | Categorías activas |
| `valorInventario()` | `float` | Valor total del inventario |
| `productosStockBajo()` | `int` | Productos con stock ≤ 5 |
| `productosPorCategoria()` | `array` | Agrupación por categoría |
| `usuariosPorRol()` | `array` | Agrupación por rol (con etiqueta) |
| `usuariosPorMes()` | `array` | Registros por mes últimos 12 meses |
| `topProductosStock()` | `array` | Top 10 por stock |
| `productosStockCritico()` | `array` | Productos con stock ≤ 5 |
| `proveedoresActivos()` | `array` | Proveedores activos con fecha |

---

## 8. Autenticación y control de acceso

Todos los archivos del módulo administrador verifican la sesión al inicio:

```php
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'administrador') {
    header('Location: ../views/usuarios/login.php');
    exit;
}
```

La sesión se inicia en `AuthController.php` y almacena:

```php
$_SESSION['usuario'] = [
    'id_usuario' => int,
    'nombre'     => string,
    'correo'     => string,
    'id_rol'     => int,      // 1, 2 o 3
    'rol'        => string,   // 'administrador', 'cajero', 'bodeguero'
];
```

Las contraseñas se almacenan con `password_hash($password, PASSWORD_DEFAULT)` y se verifican con `password_verify()`.

---

## 9. Flujo de navegación

```
Login (login.php)
    │
    ▼ id_rol = 1
Dashboard Admin (admin.php)
    │
    ├── Gestión de Usuarios ──────── AdminUsuarioController.php
    │       ├── Crear usuario
    │       ├── Editar usuario
    │       ├── Activar / Desactivar
    │       └── Eliminar
    │
    ├── Categorías y Productos ───── CategoriaController.php
    │       ├── Crear categoría
    │       ├── Editar categoría
    │       └── Eliminar categoría
    │
    ├── Proveedores ──────────────── ProveedorController.php
    │       ├── Crear proveedor
    │       ├── Editar proveedor
    │       ├── Activar / Desactivar
    │       └── Eliminar
    │
    └── Reportes ─────────────────── ReporteController.php (JSON)
            ├── KPIs (6 tarjetas)
            ├── Gráfico doughnut
            ├── Gráfico barras
            ├── Gráfico línea
            └── Tablas (3 tabs)
```

---

## 10. Convenciones y patrones usados

### Patrón MVC
El módulo sigue el patrón **Modelo–Vista–Controlador**:
- **Modelos** (`models/`) — solo lógica de datos y queries SQL con PDO.
- **Controladores** (`controllers/`) — reciben la petición, validan, llaman al modelo y redirigen.
- **Vistas** (`views/dashboard/`) — presentación HTML con PHP embebido mínimo.

### Alertas con SweetAlert2
Todos los controladores guardan el resultado en sesión:
```php
$_SESSION['alert'] = ['icon' => 'success', 'title' => 'Éxito', 'text' => 'Mensaje'];
```
Las vistas leen `$_SESSION['alert']` y disparan `Swal.fire()` al cargar el DOM.

### Consultas preparadas (PDO)
Todas las queries usan `prepare()` + `bindParam()` o parámetros nombrados para prevenir inyección SQL:
```php
$stmt = $this->conn->prepare("SELECT * FROM usuarios WHERE correo = :correo");
$stmt->bindParam(':correo', $email);
$stmt->execute();
```

### Estilos
- **Tailwind CSS** (CDN) para utilidades de layout.
- **Font Awesome 6** (CDN) para iconografía.
- Paleta de colores del sistema:

| Variable | Hex | Uso |
|---|---|---|
| Navy | `#1a2d47` | Texto principal, botones |
| Blue | `#253E63` | Hover de botones |
| Sky | `#8FB7C7` | Sidebar, encabezados de tabla |
| Light | `#D6E0E4` | Fondos suaves |

### Seguridad
- Sesión verificada en cada controlador y vista.
- Contraseñas hasheadas con `PASSWORD_DEFAULT` (bcrypt).
- Salida HTML escapada con `htmlspecialchars()`.
- Queries parametrizadas en todos los modelos.
- El correo del usuario no es editable tras el registro.

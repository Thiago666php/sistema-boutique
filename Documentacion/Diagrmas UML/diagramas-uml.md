# Diagramas UML — Celeste Boutique

> Todos los diagramas están escritos en **Mermaid**. Se renderizan automáticamente en GitHub, GitLab y en la extensión **Markdown Preview Mermaid Support** de VS Code.

---

## 1. Diagrama de Casos de Uso

```mermaid
flowchart TD
    subgraph Actores
        U1(["👤 Cajero"])
        U2(["👤 Bodeguero"])
        U3(["👤 Administrador"])
    end

    subgraph Sistema Celeste Boutique
        %% Casos de uso comunes
        CU0([Iniciar Sesión])

        %% Cajero
        CU1([Registrar Venta])
        CU2([Agregar Productos al Carrito])
        CU3([Procesar Devolución])
        CU4([Imprimir Recibo])
        CU5([Consultar Historial de Ventas])

        %% Bodeguero
        CU6([Gestionar Productos])
        CU7([Registrar Entrada de Mercancía])
        CU8([Registrar Ajuste de Inventario])
        CU9([Consultar Movimientos])
        CU10([Ver Reportes de Stock])

        %% Administrador
        CU11([Gestionar Usuarios])
        CU12([Crear / Editar Usuario])
        CU13([Activar / Desactivar Usuario])
        CU14([Gestionar Proveedores])
        CU15([Ver Reportes y Estadísticas])
    end

    U1 --> CU0
    U2 --> CU0
    U3 --> CU0

    U1 --> CU1
    CU1 --> CU2
    U1 --> CU3
    U1 --> CU4
    U1 --> CU5

    U2 --> CU6
    U2 --> CU7
    U2 --> CU8
    U2 --> CU9
    U2 --> CU10

    U3 --> CU11
    CU11 --> CU12
    CU11 --> CU13
    U3 --> CU14
    U3 --> CU15
    U3 --> CU6
    U3 --> CU1
```

---

## 2. Diagrama de Clases

```mermaid
classDiagram
    direction TB

    class Usuario {
        +int id_usuario
        +string nombre
        +string correo
        +string password
        +int id_rol
        +string telefono
        +int activo
        +datetime created_at
        +existeCorreo(email) bool
        +obtenerPorEmail(email) array
        +registrar(datos) bool
        +obtenerTodos() array
        +obtenerPorId(id) array
        +actualizar(id, datos) bool
        +cambiarEstado(id, activo) bool
        +eliminar(id) bool
    }

    class Venta {
        +int id_venta
        +int id_usuario
        +string cliente_nombre
        +float total
        +string estado
        +datetime created_at
        +obtenerProductos() array
        +crearVenta(idUsuario, cliente, items) int|string
        +obtenerVentaPorId(id) array
        +obtenerDetalleVenta(idVenta) array
        +historial(idUsuario, desde, hasta, estado) array
        +registrarDevolucion(idVenta, idUsuario, motivo) bool|string
        +obtenerDevoluciones(idUsuario) array
    }

    class Inventario {
        +obtenerProductos(buscar) array
        +obtenerProductoPorId(id) array
        +crearProducto(datos) bool|string
        +actualizarProducto(id, datos) bool|string
        +registrarEntrada(idProducto, cantidad, idUsuario, motivo, idProveedor) bool|string
        +registrarAjuste(idProducto, cantidad, tipo, idUsuario, motivo) bool|string
        +obtenerMovimientos(tipo, idProducto) array
        +productosStockBajo(umbral) array
        +reportePorProveedor() array
        +obtenerCategorias() array
        +obtenerProveedores() array
    }

    class Proveedor {
        +int id_proveedor
        +string nombre
        +string ruc
        +string telefono
        +string correo
        +string direccion
        +int activo
        +datetime created_at
        +obtenerTodos() array
        +obtenerPorId(id) array
        +existeRuc(ruc, excluirId) bool
        +crear(datos) bool|string
        +actualizar(id, datos) bool|string
        +cambiarEstado(id, activo) bool|string
        +eliminar(id) bool|string
    }

    class Reporte {
        +totalUsuarios() int
        +totalProductos() int
        +totalProveedores() int
        +totalCategorias() int
        +valorInventario() float
        +productosStockBajo() int
        +productosPorCategoria() array
        +usuariosPorRol() array
        +usuariosPorMes() array
        +topProductosStock() array
        +productosStockCritico() array
        +proveedoresActivos() array
    }

    class AuthController {
        +login() void
        +logout() void
        -obtenerNombreRol(id_rol) string
    }

    class VentaController {
        +registrar() void
        +devolucion() void
    }

    class InventarioController {
        +crearProducto() void
        +editarProducto() void
        +entrada() void
        +ajuste() void
    }

    class AdminUsuarioController {
        +crear() void
        +editar() void
        +toggleEstado() void
        +eliminar() void
    }

    class ProveedorController {
        +crear() void
        +editar() void
        +toggleEstado() void
        +eliminar() void
    }

    class ReporteController {
        +resumen() json
        +productosPorCategoria() json
        +usuariosPorRol() json
        +usuariosPorMes() json
        +topProductosStock() json
        +stockCritico() json
        +proveedoresActivos() json
    }

    AuthController --> Usuario
    VentaController --> Venta
    InventarioController --> Inventario
    AdminUsuarioController --> Usuario
    ProveedorController --> Proveedor
    ReporteController --> Reporte

    Venta --> Usuario : "registrada por"
    Inventario --> Proveedor : "entrada asociada a"
    Reporte --> Usuario : "consulta"
    Reporte --> Inventario : "consulta"
    Reporte --> Proveedor : "consulta"
```

---

## 3. Diagrama de Secuencia — Registrar Venta

```mermaid
sequenceDiagram
    actor Cajero
    participant Vista as cajero.php
    participant JS as JavaScript (Carrito)
    participant Ctrl as VentaController
    participant Model as Venta.php
    participant DB as Base de Datos

    Cajero->>Vista: Abre módulo cajero
    Vista->>Model: obtenerProductos()
    Model->>DB: SELECT productos activos con stock > 0
    DB-->>Model: Lista de productos
    Model-->>Vista: Renderiza tabla de productos

    Cajero->>JS: Clic en "+" (agregar producto)
    JS->>JS: agregarAlCarrito(id, nombre, precio, stock)
    JS->>JS: renderCarrito() — actualiza total

    Cajero->>JS: Clic "Registrar Venta"
    JS->>JS: confirmarVenta() — SweetAlert2
    Cajero->>JS: Confirma en el diálogo

    JS->>Ctrl: POST items[] + cliente_nombre
    Ctrl->>Model: crearVenta(idUsuario, cliente, items)
    Model->>DB: BEGIN TRANSACTION
    Model->>DB: Valida stock por producto
    Model->>DB: INSERT INTO ventas
    Model->>DB: INSERT INTO venta_detalle (por cada ítem)
    Model->>DB: UPDATE productos SET stock = stock - cantidad
    Model->>DB: COMMIT
    DB-->>Model: id_venta generado
    Model-->>Ctrl: Retorna id_venta (int)

    Ctrl->>Vista: Redirect cajero.php?tab=recibo&id={N}
    Vista->>Model: obtenerVentaPorId(N) + obtenerDetalleVenta(N)
    Model->>DB: SELECT venta + detalle
    DB-->>Model: Datos del recibo
    Model-->>Vista: Renderiza recibo_template.php
    Vista-->>Cajero: Muestra recibo listo para imprimir
```

---

## 4. Diagrama de Secuencia — Registrar Devolución

```mermaid
sequenceDiagram
    actor Cajero
    participant Vista as cajero.php
    participant JS as JavaScript
    participant Ctrl as VentaController
    participant Model as Venta.php
    participant DB as Base de Datos

    Cajero->>Vista: Tab Devoluciones — ingresa N° de venta
    Cajero->>JS: Clic "Procesar Devolución"
    JS->>JS: confirmarDevolucion() — SweetAlert2
    Cajero->>JS: Confirma en el diálogo

    JS->>Ctrl: POST id_venta + motivo
    Ctrl->>Model: registrarDevolucion(idVenta, idUsuario, motivo)

    Model->>DB: SELECT venta por id
    DB-->>Model: Datos de la venta

    alt Venta no existe
        Model-->>Ctrl: "Venta #N no encontrada"
        Ctrl-->>Vista: Redirect con alerta error
    else Venta ya anulada
        Model-->>Ctrl: "Esta venta ya fue anulada"
        Ctrl-->>Vista: Redirect con alerta error
    else Venta ya tiene devolución
        Model-->>Ctrl: "Ya tiene una devolución registrada"
        Ctrl-->>Vista: Redirect con alerta error
    else Venta válida
        Model->>DB: BEGIN TRANSACTION
        Model->>DB: INSERT INTO devoluciones
        Model->>DB: UPDATE productos SET stock = stock + cantidad (por cada ítem)
        Model->>DB: UPDATE ventas SET estado = 'anulada'
        Model->>DB: COMMIT
        DB-->>Model: OK
        Model-->>Ctrl: true
        Ctrl-->>Vista: Redirect con alerta éxito
        Vista-->>Cajero: Muestra confirmación
    end
```

---

## 5. Diagrama de Actividades — Flujo General del Sistema

```mermaid
flowchart TD
    A([Inicio]) --> B[Usuario accede al sistema]
    B --> C{¿Tiene sesión activa?}
    C -- No --> D[Muestra login.php]
    D --> E[Ingresa correo y contraseña]
    E --> F{¿Credenciales válidas?}
    F -- No --> G[Alerta de error]
    G --> D
    F -- Sí --> H{¿Usuario activo?}
    H -- No --> I[Alerta: usuario inactivo]
    I --> D
    H -- Sí --> J{Verificar rol}

    C -- Sí --> J

    J -- id_rol = 1 --> K[Panel Administrador]
    J -- id_rol = 2 --> L[Panel Cajero]
    J -- id_rol = 3 --> M[Panel Bodeguero]

    %% Flujo Cajero
    L --> L1{¿Qué acción?}
    L1 -- Nueva Venta --> L2[Selecciona productos]
    L2 --> L3[Agrega al carrito]
    L3 --> L4[Confirma venta]
    L4 --> L5[Venta registrada + stock descontado]
    L5 --> L6[Muestra recibo]
    L1 -- Devolución --> L7[Ingresa N° de venta]
    L7 --> L8[Venta anulada + stock repuesto]
    L1 -- Historial --> L9[Consulta ventas propias]

    %% Flujo Bodeguero
    M --> M1{¿Qué acción?}
    M1 -- Entrada --> M2[Selecciona producto y cantidad]
    M2 --> M3[Stock incrementado + movimiento registrado]
    M1 -- Ajuste --> M4[Selecciona tipo de ajuste]
    M4 --> M5[Stock ajustado + movimiento registrado]
    M1 -- Inventario --> M6[Crea o edita producto]
    M1 -- Movimientos --> M7[Consulta historial]

    %% Flujo Admin
    K --> K1{¿Qué acción?}
    K1 -- Usuarios --> K2[Crear / Editar / Activar / Eliminar]
    K1 -- Proveedores --> K3[Crear / Editar / Activar / Eliminar]
    K1 -- Reportes --> K4[Ver KPIs y gráficos]

    L6 --> N([Fin de operación])
    L8 --> N
    L9 --> N
    M3 --> N
    M5 --> N
    M6 --> N
    M7 --> N
    K2 --> N
    K3 --> N
    K4 --> N
```

---

## 6. Diagrama de Tablas / Entidades (MER)

```mermaid
erDiagram
    usuarios {
        int id_usuario PK
        string nombre
        string correo
        string password
        int id_rol
        string telefono
        tinyint activo
        timestamp created_at
    }

    ventas {
        int id_venta PK
        int id_usuario FK
        string cliente_nombre
        decimal total
        string estado
        timestamp created_at
    }

    venta_detalle {
        int id_detalle PK
        int id_venta FK
        int id_producto FK
        int cantidad
        decimal precio_unit
        decimal subtotal
    }

    devoluciones {
        int id_devolucion PK
        int id_venta FK
        int id_usuario FK
        string motivo
        decimal monto
        timestamp created_at
    }

    productos {
        int id_producto PK
        int id_categoria FK
        string nombre
        string descripcion
        decimal precio
        int stock
        tinyint activo
        timestamp created_at
    }

    categorias_productos {
        int id_categoria PK
        string nombre
        string descripcion
        int cantidad
        tinyint activo
        timestamp created_at
    }

    proveedores {
        int id_proveedor PK
        string nombre
        string ruc
        string telefono
        string correo
        string direccion
        tinyint activo
        timestamp created_at
    }

    movimientos_inventario {
        int id_movimiento PK
        int id_producto FK
        int id_usuario FK
        int id_proveedor FK
        string tipo
        int cantidad
        string motivo
        timestamp created_at
    }

    usuarios ||--o{ ventas : "registra"
    ventas ||--|{ venta_detalle : "contiene"
    productos ||--o{ venta_detalle : "incluido en"
    ventas ||--o| devoluciones : "puede tener"
    usuarios ||--o{ devoluciones : "procesa"
    categorias_productos ||--o{ productos : "clasifica"
    productos ||--o{ movimientos_inventario : "afecta"
    usuarios ||--o{ movimientos_inventario : "registra"
    proveedores ||--o{ movimientos_inventario : "suministra en"
```

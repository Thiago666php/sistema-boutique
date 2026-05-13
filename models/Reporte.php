<?php
/**
 * Model: Reporte — Celeste Boutique
 *
 * Esquema real de bdboutique:
 *   usuarios            → id_usuario, nombre, correo, id_rol, activo, created_at
 *   categorias_productos→ id_categoria, nombre, descripcion, cantidad, activo, created_at
 *   productos           → id_producto, id_categoria, nombre, descripcion, precio, stock, activo, created_at
 *   proveedores         → id_proveedor, nombre, ruc, telefono, correo, activo, created_at
 */
class Reporte
{
    private PDO $conn;

    private array $roles = [
        1 => 'Administrador',
        2 => 'Cajero',
        3 => 'Bodeguero',
    ];

    public function __construct(PDO $db)
    {
        $this->conn = $db;
        $this->crearTablaProductosSiNoExiste();
        $this->asegurarColumnasProductos();
    }

    private function asegurarColumnasProductos(): void
    {
        try {
            $cols = $this->conn->query("DESCRIBE productos")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('stock', $cols)) {
                $this->conn->exec("ALTER TABLE `productos` ADD COLUMN `stock` INT NOT NULL DEFAULT 0 AFTER `precio`");
            }
            if (!in_array('activo', $cols)) {
                $this->conn->exec("ALTER TABLE `productos` ADD COLUMN `activo` TINYINT(1) NOT NULL DEFAULT 1");
            }
        } catch (\Throwable $e) { /* tabla no existe aún, se creará después */ }
    }

    /** Crea la tabla productos si el bodeguero aún no la ha generado */
    private function crearTablaProductosSiNoExiste(): void
    {
        $this->conn->exec("
            CREATE TABLE IF NOT EXISTS `productos` (
                `id_producto`  INT           PRIMARY KEY AUTO_INCREMENT,
                `id_categoria` INT           NOT NULL,
                `nombre`       VARCHAR(150)  NOT NULL,
                `descripcion`  VARCHAR(255)  DEFAULT NULL,
                `precio`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                `stock`        INT           NOT NULL DEFAULT 0,
                `activo`       TINYINT(1)    NOT NULL DEFAULT 1,
                `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    }

    // ── KPI ──────────────────────────────────────────────────

    public function totalUsuarios(): int
    {
        return (int) $this->conn
            ->query("SELECT COUNT(*) FROM usuarios")
            ->fetchColumn();
    }

    public function totalProductos(): int
    {
        return (int) $this->conn
            ->query("SELECT COUNT(*) FROM productos WHERE activo = 1")
            ->fetchColumn();
    }

    public function totalProveedores(): int
    {
        return (int) $this->conn
            ->query("SELECT COUNT(*) FROM proveedores WHERE activo = 1")
            ->fetchColumn();
    }

    public function totalCategorias(): int
    {
        return (int) $this->conn
            ->query("SELECT COUNT(*) FROM categorias_productos WHERE activo = 1")
            ->fetchColumn();
    }

    public function valorInventario(): float
    {
        return (float) $this->conn
            ->query("SELECT COALESCE(SUM(precio * stock), 0) FROM productos WHERE activo = 1")
            ->fetchColumn();
    }

    public function productosStockBajo(): int
    {
        return (int) $this->conn
            ->query("SELECT COUNT(*) FROM productos WHERE activo = 1 AND stock <= 5")
            ->fetchColumn();
    }

    // ── Gráficos ─────────────────────────────────────────────

    /**
     * Productos activos por categoría.
     * Si la tabla productos está vacía, usa la columna `cantidad`
     * de categorias_productos como fallback.
     */
    public function productosPorCategoria(): array
    {
        $hayProductos = (int) $this->conn
            ->query("SELECT COUNT(*) FROM productos")
            ->fetchColumn();

        if ($hayProductos > 0) {
            $sql = "SELECT c.nombre AS categoria, COUNT(p.id_producto) AS total
                    FROM categorias_productos c
                    LEFT JOIN productos p
                           ON p.id_categoria = c.id_categoria AND p.activo = 1
                    WHERE c.activo = 1
                    GROUP BY c.id_categoria, c.nombre
                    ORDER BY total DESC";
        } else {
            // Fallback: usar la columna cantidad de categorias_productos
            $sql = "SELECT nombre AS categoria, COALESCE(cantidad, 0) AS total
                    FROM categorias_productos
                    WHERE activo = 1
                    ORDER BY total DESC";
        }

        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Usuarios agrupados por rol (id_rol → etiqueta) */
    public function usuariosPorRol(): array
    {
        $sql  = "SELECT id_rol, COUNT(*) AS total
                 FROM usuarios
                 GROUP BY id_rol
                 ORDER BY id_rol ASC";
        $rows = $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn($r) => [
            'rol'   => $this->roles[$r['id_rol']] ?? 'Rol ' . $r['id_rol'],
            'total' => $r['total'],
        ], $rows);
    }

    /** Nuevos usuarios por mes — últimos 12 meses */
    public function usuariosPorMes(): array
    {
        $sql = "SELECT DATE_FORMAT(created_at, '%b %Y') AS mes,
                       DATE_FORMAT(created_at, '%Y-%m')  AS mes_orden,
                       COUNT(*)                          AS total
                FROM usuarios
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                GROUP BY mes_orden, mes
                ORDER BY mes_orden ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Tablas de detalle ─────────────────────────────────────

    /** Top 10 productos con mayor stock */
    public function topProductosStock(): array
    {
        $sql = "SELECT p.nombre,
                       c.nombre             AS categoria,
                       p.precio,
                       p.stock,
                       (p.precio * p.stock) AS valor_total
                FROM productos p
                JOIN categorias_productos c ON c.id_categoria = p.id_categoria
                WHERE p.activo = 1
                ORDER BY p.stock DESC
                LIMIT 10";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Productos con stock ≤ 5 */
    public function productosStockCritico(): array
    {
        $sql = "SELECT p.nombre,
                       c.nombre AS categoria,
                       p.precio,
                       p.stock
                FROM productos p
                JOIN categorias_productos c ON c.id_categoria = p.id_categoria
                WHERE p.activo = 1 AND p.stock <= 5
                ORDER BY p.stock ASC
                LIMIT 20";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Proveedores activos */
    public function proveedoresActivos(): array
    {
        $sql = "SELECT nombre,
                       ruc,
                       telefono,
                       correo,
                       DATE_FORMAT(created_at, '%d/%m/%Y') AS fecha_registro
                FROM proveedores
                WHERE activo = 1
                ORDER BY nombre ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<?php
/**
 * Model: Inventario — Celeste Boutique
 * Gestión de productos, entradas, ajustes, traslados y reportes para el bodeguero.
 */
class Inventario
{
    private PDO $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    // ── Productos ─────────────────────────────────────────────

    public function obtenerProductos(string $buscar = ''): array
    {
        $sql = "SELECT p.id_producto, p.nombre, p.descripcion, p.precio,
                       p.stock, p.activo, p.imagen, c.nombre AS categoria,
                       pv.nombre AS proveedor
                FROM productos p
                JOIN categorias_productos c ON c.id_categoria = p.id_categoria
                LEFT JOIN proveedores pv ON pv.id_proveedor = p.id_proveedor
                WHERE 1=1";
        $params = [];
        if ($buscar) {
            $sql .= " AND (p.nombre LIKE :b OR c.nombre LIKE :b2)";
            $params[':b']  = "%$buscar%";
            $params[':b2'] = "%$buscar%";
        }
        $sql .= " ORDER BY p.nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProductoPorId(int $id): array|false
    {
        $stmt = $this->conn->prepare(
            "SELECT p.*, c.nombre AS categoria
             FROM productos p
             JOIN categorias_productos c ON c.id_categoria = p.id_categoria
             WHERE p.id_producto = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crearProducto(array $d): bool|string
    {
        try {
            // Asegurar que la columna imagen exista
            $this->asegurarColumnaImagen();

            $stmt = $this->conn->prepare(
                "INSERT INTO productos (id_categoria, nombre, descripcion, precio, stock, imagen, activo)
                 VALUES (:cat, :nom, :desc, :precio, :stock, :imagen, 1)"
            );
            $stmt->execute([
                ':cat'    => $d['id_categoria'],
                ':nom'    => $d['nombre'],
                ':desc'   => $d['descripcion'] ?? '',
                ':precio' => $d['precio'],
                ':stock'  => $d['stock'] ?? 0,
                ':imagen' => $d['imagen'] ?? null,
            ]);
            return true;
        } catch (\Throwable $e) { return $e->getMessage(); }
    }

    public function actualizarProducto(int $id, array $d): bool|string
    {
        try {
            $this->asegurarColumnaImagen();

            // Si viene imagen nueva la actualiza, si no conserva la anterior
            if (!empty($d['imagen'])) {
                $stmt = $this->conn->prepare(
                    "UPDATE productos SET id_categoria=:cat, nombre=:nom,
                     descripcion=:desc, precio=:precio, imagen=:imagen
                     WHERE id_producto=:id"
                );
                $stmt->execute([
                    ':cat'    => $d['id_categoria'],
                    ':nom'    => $d['nombre'],
                    ':desc'   => $d['descripcion'] ?? '',
                    ':precio' => $d['precio'],
                    ':imagen' => $d['imagen'],
                    ':id'     => $id,
                ]);
            } else {
                $stmt = $this->conn->prepare(
                    "UPDATE productos SET id_categoria=:cat, nombre=:nom,
                     descripcion=:desc, precio=:precio WHERE id_producto=:id"
                );
                $stmt->execute([
                    ':cat'    => $d['id_categoria'],
                    ':nom'    => $d['nombre'],
                    ':desc'   => $d['descripcion'] ?? '',
                    ':precio' => $d['precio'],
                    ':id'     => $id,
                ]);
            }
            return true;
        } catch (\Throwable $e) { return $e->getMessage(); }
    }

    /** Agrega la columna imagen si aún no existe en la tabla productos */
    private function asegurarColumnaImagen(): void
    {
        try {
            $cols = $this->conn->query("DESCRIBE productos")->fetchAll(PDO::FETCH_COLUMN);
            if (!in_array('imagen', $cols)) {
                $this->conn->exec(
                    "ALTER TABLE `productos` ADD COLUMN `imagen` VARCHAR(255) DEFAULT NULL AFTER `stock`"
                );
            }
        } catch (\Throwable) { /* silencioso */ }
    }

    // ── Entradas al almacén ───────────────────────────────────

    public function registrarEntrada(int $idProducto, int $cantidad, int $idUsuario, string $motivo, ?int $idProveedor): bool|string
    {
        try {
            $this->conn->beginTransaction();

            $this->conn->prepare(
                "INSERT INTO movimientos_inventario
                 (id_producto, tipo, cantidad, motivo, id_usuario, id_proveedor)
                 VALUES (:ip, 'entrada', :cant, :mot, :iu, :iprov)"
            )->execute([':ip'=>$idProducto,':cant'=>$cantidad,':mot'=>$motivo,':iu'=>$idUsuario,':iprov'=>$idProveedor]);

            $this->conn->prepare(
                "UPDATE productos SET stock = stock + :cant WHERE id_producto = :id"
            )->execute([':cant'=>$cantidad,':id'=>$idProducto]);

            $this->conn->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return $e->getMessage();
        }
    }

    // ── Ajustes (pérdidas / mermas / correcciones) ────────────

    public function registrarAjuste(int $idProducto, int $cantidad, string $tipo, int $idUsuario, string $motivo): bool|string
    {
        // tipo: 'perdida' | 'merma' | 'ajuste_positivo' | 'ajuste_negativo'
        try {
            $prod = $this->obtenerProductoPorId($idProducto);
            if (!$prod) return "Producto no encontrado.";

            $esNegativo = in_array($tipo, ['perdida','merma','ajuste_negativo']);
            if ($esNegativo && $prod['stock'] < $cantidad)
                return "Stock insuficiente (disponible: {$prod['stock']}).";

            $this->conn->beginTransaction();

            $this->conn->prepare(
                "INSERT INTO movimientos_inventario
                 (id_producto, tipo, cantidad, motivo, id_usuario)
                 VALUES (:ip, :tipo, :cant, :mot, :iu)"
            )->execute([':ip'=>$idProducto,':tipo'=>$tipo,':cant'=>$cantidad,':mot'=>$motivo,':iu'=>$idUsuario]);

            $op = $esNegativo ? '-' : '+';
            $this->conn->prepare(
                "UPDATE productos SET stock = stock {$op} :cant WHERE id_producto = :id"
            )->execute([':cant'=>$cantidad,':id'=>$idProducto]);

            $this->conn->commit();
            return true;
        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return $e->getMessage();
        }
    }

    // ── Historial de movimientos ──────────────────────────────

    public function obtenerMovimientos(string $tipo = '', int $idProducto = 0): array
    {
        $sql = "SELECT m.id_movimiento, m.tipo, m.cantidad, m.motivo,
                       DATE_FORMAT(m.created_at,'%d/%m/%Y %H:%i') AS fecha,
                       p.nombre AS producto, u.nombre AS usuario,
                       pv.nombre AS proveedor
                FROM movimientos_inventario m
                JOIN productos  p  ON p.id_producto  = m.id_producto
                JOIN usuarios   u  ON u.id_usuario   = m.id_usuario
                LEFT JOIN proveedores pv ON pv.id_proveedor = m.id_proveedor
                WHERE 1=1";
        $params = [];
        if ($tipo)       { $sql .= " AND m.tipo = :tipo";           $params[':tipo'] = $tipo; }
        if ($idProducto) { $sql .= " AND m.id_producto = :ip";      $params[':ip']   = $idProducto; }
        $sql .= " ORDER BY m.created_at DESC LIMIT 300";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Alertas de stock bajo ─────────────────────────────────

    public function productosStockBajo(int $umbral = 5): array
    {
        $stmt = $this->conn->prepare(
            "SELECT p.id_producto, p.nombre, p.stock, c.nombre AS categoria
             FROM productos p
             JOIN categorias_productos c ON c.id_categoria = p.id_categoria
             WHERE p.activo = 1 AND p.stock <= :u
             ORDER BY p.stock ASC"
        );
        $stmt->execute([':u' => $umbral]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Reporte por proveedor ─────────────────────────────────

    public function reportePorProveedor(): array
    {
        $sql = "SELECT pv.nombre AS proveedor,
                       COUNT(DISTINCT m.id_producto) AS productos_distintos,
                       SUM(m.cantidad)               AS total_unidades,
                       MAX(DATE_FORMAT(m.created_at,'%d/%m/%Y')) AS ultima_entrada
                FROM movimientos_inventario m
                JOIN proveedores pv ON pv.id_proveedor = m.id_proveedor
                WHERE m.tipo = 'entrada'
                GROUP BY pv.id_proveedor, pv.nombre
                ORDER BY total_unidades DESC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Categorías y proveedores (para selects) ───────────────

    public function obtenerCategorias(): array
    {
        return $this->conn->query(
            "SELECT id_categoria, nombre FROM categorias_productos WHERE activo=1 ORDER BY nombre"
        )->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProveedores(): array
    {
        return $this->conn->query(
            "SELECT id_proveedor, nombre FROM proveedores WHERE activo=1 ORDER BY nombre"
        )->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

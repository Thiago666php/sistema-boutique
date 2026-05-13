<?php
/**
 * Model: Venta — Celeste Boutique
 * Solo hace queries. Las tablas se crean con instalar_cajero.php
 */
class Venta
{
    private PDO $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    // ── Productos disponibles ─────────────────────────────────

    public function obtenerProductos(): array
    {
        $sql = "SELECT p.id_producto, p.nombre, p.precio, p.stock,
                       c.nombre AS categoria
                FROM productos p
                JOIN categorias_productos c ON c.id_categoria = p.id_categoria
                WHERE p.activo = 1 AND p.stock > 0
                ORDER BY p.nombre ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerProductoPorId(int $id): array|false
    {
        $stmt = $this->conn->prepare(
            "SELECT * FROM productos WHERE id_producto = :id AND activo = 1 LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ── Crear venta ───────────────────────────────────────────

    public function crearVenta(int $idUsuario, string $clienteNombre, array $items): int|string
    {
        try {
            $this->conn->beginTransaction();
            $total  = 0.0;
            $lineas = [];

            foreach ($items as $item) {
                $prod = $this->obtenerProductoPorId((int)$item['id_producto']);
                if (!$prod) throw new \Exception("Producto #{$item['id_producto']} no encontrado.");
                $cant = (int)$item['cantidad'];
                if ($cant <= 0) throw new \Exception("Cantidad inválida para «{$prod['nombre']}».");
                if ($prod['stock'] < $cant) throw new \Exception("Stock insuficiente para «{$prod['nombre']}» (disponible: {$prod['stock']}).");
                $subtotal = round($prod['precio'] * $cant, 2);
                $total   += $subtotal;
                $lineas[] = ['prod' => $prod, 'cant' => $cant, 'subtotal' => $subtotal];
            }

            $stmt = $this->conn->prepare(
                "INSERT INTO ventas (id_usuario, cliente_nombre, total, estado)
                 VALUES (:uid, :cli, :total, 'completada')"
            );
            $stmt->execute([':uid' => $idUsuario, ':cli' => $clienteNombre ?: 'Cliente general', ':total' => $total]);
            $idVenta = (int) $this->conn->lastInsertId();

            $stmtDet   = $this->conn->prepare("INSERT INTO venta_detalle (id_venta, id_producto, cantidad, precio_unit, subtotal) VALUES (:iv, :ip, :cant, :pu, :sub)");
            $stmtStock = $this->conn->prepare("UPDATE productos SET stock = stock - :cant WHERE id_producto = :id");

            foreach ($lineas as $l) {
                $stmtDet->execute([':iv' => $idVenta, ':ip' => $l['prod']['id_producto'], ':cant' => $l['cant'], ':pu' => $l['prod']['precio'], ':sub' => $l['subtotal']]);
                $stmtStock->execute([':cant' => $l['cant'], ':id' => $l['prod']['id_producto']]);
            }

            $this->conn->commit();
            return $idVenta;

        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return $e->getMessage();
        }
    }

    // ── Consultar venta ───────────────────────────────────────

    public function obtenerVentaPorId(int $id): array|false
    {
        $stmt = $this->conn->prepare(
            "SELECT v.*, u.nombre AS cajero
             FROM ventas v
             JOIN usuarios u ON u.id_usuario = v.id_usuario
             WHERE v.id_venta = :id LIMIT 1"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerDetalleVenta(int $idVenta): array
    {
        $stmt = $this->conn->prepare(
            "SELECT d.*, p.nombre AS producto
             FROM venta_detalle d
             JOIN productos p ON p.id_producto = d.id_producto
             WHERE d.id_venta = :id"
        );
        $stmt->execute([':id' => $idVenta]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Historial ─────────────────────────────────────────────

    public function historial(int $idUsuario, string $desde = '', string $hasta = '', string $estado = ''): array
    {
        $sql = "SELECT v.id_venta, v.cliente_nombre, v.total, v.estado,
                       DATE_FORMAT(v.created_at,'%d/%m/%Y %H:%i') AS fecha,
                       u.nombre AS cajero
                FROM ventas v
                JOIN usuarios u ON u.id_usuario = v.id_usuario
                WHERE 1=1";
        $params = [];

        if ($idUsuario > 0) { $sql .= " AND v.id_usuario = :uid";         $params[':uid']   = $idUsuario; }
        if ($desde)         { $sql .= " AND DATE(v.created_at) >= :desde"; $params[':desde'] = $desde; }
        if ($hasta)         { $sql .= " AND DATE(v.created_at) <= :hasta"; $params[':hasta'] = $hasta; }
        if ($estado)        { $sql .= " AND v.estado = :estado";           $params[':estado'] = $estado; }

        $sql .= " ORDER BY v.created_at DESC LIMIT 200";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Devoluciones ──────────────────────────────────────────

    public function registrarDevolucion(int $idVenta, int $idUsuario, string $motivo): bool|string
    {
        try {
            $venta = $this->obtenerVentaPorId($idVenta);
            if (!$venta)                        return "Venta #{$idVenta} no encontrada.";
            if ($venta['estado'] === 'anulada') return "Esta venta ya fue anulada.";

            $check = $this->conn->prepare("SELECT id_devolucion FROM devoluciones WHERE id_venta = :id LIMIT 1");
            $check->execute([':id' => $idVenta]);
            if ($check->rowCount() > 0) return "Esta venta ya tiene una devolución registrada.";

            $this->conn->beginTransaction();

            $this->conn->prepare("INSERT INTO devoluciones (id_venta, id_usuario, motivo, monto) VALUES (:iv, :iu, :mot, :monto)")
                       ->execute([':iv' => $idVenta, ':iu' => $idUsuario, ':mot' => $motivo, ':monto' => $venta['total']]);

            $stmtStock = $this->conn->prepare("UPDATE productos SET stock = stock + :cant WHERE id_producto = :id");
            foreach ($this->obtenerDetalleVenta($idVenta) as $d) {
                $stmtStock->execute([':cant' => $d['cantidad'], ':id' => $d['id_producto']]);
            }

            $this->conn->prepare("UPDATE ventas SET estado='anulada' WHERE id_venta=:id")->execute([':id' => $idVenta]);
            $this->conn->commit();
            return true;

        } catch (\Throwable $e) {
            if ($this->conn->inTransaction()) $this->conn->rollBack();
            return $e->getMessage();
        }
    }

    public function obtenerDevoluciones(int $idUsuario): array
    {
        $sql = "SELECT d.id_devolucion, d.id_venta, d.motivo, d.monto,
                       DATE_FORMAT(d.created_at,'%d/%m/%Y %H:%i') AS fecha,
                       v.cliente_nombre, u.nombre AS cajero
                FROM devoluciones d
                JOIN ventas   v ON v.id_venta   = d.id_venta
                JOIN usuarios u ON u.id_usuario = d.id_usuario
                WHERE 1=1";
        $params = [];
        if ($idUsuario > 0) { $sql .= " AND d.id_usuario = :uid"; $params[':uid'] = $idUsuario; }
        $sql .= " ORDER BY d.created_at DESC LIMIT 100";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

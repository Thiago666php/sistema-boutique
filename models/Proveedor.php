<?php
class Proveedor
{
    private $conn;
    private $tabla = 'proveedores';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function obtenerTodos(): array
    {
        $sql  = "SELECT * FROM {$this->tabla} ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId(int $id): array|false
    {
        $sql  = "SELECT * FROM {$this->tabla} WHERE id_proveedor = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function existeRuc(string $ruc, int $excluirId = 0): bool
    {
        $sql  = "SELECT id_proveedor FROM {$this->tabla}
                 WHERE ruc = :ruc AND id_proveedor != :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':ruc', $ruc);
        $stmt->bindParam(':id',  $excluirId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function crear(array $d): bool|string
    {
        try {
            $sql  = "INSERT INTO {$this->tabla}
                        (nombre, ruc, telefono, correo, direccion, activo)
                     VALUES
                        (:nombre, :ruc, :telefono, :correo, :direccion, 1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre',    $d['nombre']);
            $stmt->bindParam(':ruc',       $d['ruc']);
            $stmt->bindParam(':telefono',  $d['telefono']);
            $stmt->bindParam(':correo',    $d['correo']);
            $stmt->bindParam(':direccion', $d['direccion']);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return 'Error al crear: ' . $e->getMessage();
        }
    }

    public function actualizar(int $id, array $d): bool|string
    {
        try {
            $sql  = "UPDATE {$this->tabla}
                     SET nombre = :nombre, ruc = :ruc, telefono = :telefono,
                         correo = :correo, direccion = :direccion
                     WHERE id_proveedor = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre',    $d['nombre']);
            $stmt->bindParam(':ruc',       $d['ruc']);
            $stmt->bindParam(':telefono',  $d['telefono']);
            $stmt->bindParam(':correo',    $d['correo']);
            $stmt->bindParam(':direccion', $d['direccion']);
            $stmt->bindParam(':id',        $id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return 'Error al actualizar: ' . $e->getMessage();
        }
    }

    public function cambiarEstado(int $id, int $activo): bool|string
    {
        try {
            $sql  = "UPDATE {$this->tabla} SET activo = :activo WHERE id_proveedor = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
            $stmt->bindParam(':id',     $id,     PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return 'Error al cambiar estado: ' . $e->getMessage();
        }
    }

    public function eliminar(int $id): bool|string
    {
        try {
            $sql  = "DELETE FROM {$this->tabla} WHERE id_proveedor = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return 'No se puede eliminar: el proveedor tiene registros asociados.';
        }
    }
}
?>

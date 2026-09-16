<?php
class Categoria
{
    private $conn;
    private $tabla = 'categorias_productos';

    public function __construct($db)
    {
        $this->conn = $db;
    }

    /** Devuelve todas las categorías ordenadas por nombre */
    public function obtenerTodas(): array
    {
        $sql  = "SELECT * FROM {$this->tabla} ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Devuelve solo las categorías activas (para selects) */
    public function obtenerActivas(): array
    {
        $sql  = "SELECT id_categoria, nombre FROM {$this->tabla}
                 WHERE activo = 1 ORDER BY nombre ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Obtiene una categoría por su ID */
    public function obtenerPorId(int $id): array|false
    {
        $sql  = "SELECT * FROM {$this->tabla} WHERE id_categoria = :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Verifica si ya existe una categoría con ese nombre (excluyendo un ID) */
    public function existeNombre(string $nombre, int $excluirId = 0): bool
    {
        $sql  = "SELECT id_categoria FROM {$this->tabla}
                 WHERE nombre = :nombre AND id_categoria != :id LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id',     $excluirId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    /** Crea una nueva categoría */
    public function crear(array $datos): bool|string
    {
        try {
            $sql  = "INSERT INTO {$this->tabla} (nombre, descripcion, cantidad, activo)
                     VALUES (:nombre, :descripcion, :cantidad, 1)";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre',      $datos['nombre']);
            $stmt->bindParam(':descripcion', $datos['descripcion']);
            $stmt->bindParam(':cantidad',    $datos['cantidad'], PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return 'Error al crear: ' . $e->getMessage();
        }
    }

    /** Actualiza nombre, descripción y cantidad de una categoría */
    public function actualizar(int $id, array $datos): bool|string
    {
        try {
            $sql  = "UPDATE {$this->tabla}
                     SET nombre = :nombre, descripcion = :descripcion, cantidad = :cantidad
                     WHERE id_categoria = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':nombre',      $datos['nombre']);
            $stmt->bindParam(':descripcion', $datos['descripcion']);
            $stmt->bindParam(':cantidad',    $datos['cantidad'], PDO::PARAM_INT);
            $stmt->bindParam(':id',          $id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return 'Error al actualizar: ' . $e->getMessage();
        }
    }

    /** Elimina una categoría (falla si tiene productos asociados) */
    public function eliminar(int $id): bool|string
    {
        try {
            $sql  = "DELETE FROM {$this->tabla} WHERE id_categoria = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            // FK violation → tiene productos asociados
            return 'No se puede eliminar: la categoría tiene productos asociados.';
        }
    }
}
?>
pene
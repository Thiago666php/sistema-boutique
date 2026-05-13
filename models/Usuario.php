<?php
class Usuario {
    private $conn;
    private $tabla = "usuarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function existeCorreo($email) {
        $sql = "SELECT id_usuario FROM " . $this->tabla . " WHERE correo = :correo LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":correo", $email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function obtenerPorEmail($email) {
        $sql = "SELECT * FROM " . $this->tabla . " WHERE correo = :correo AND activo = 1 LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":correo", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function registrar($datos) {
        try {
            $this->conn->beginTransaction();

            $sqlUsuario = "INSERT INTO usuarios
                (nombre, correo, password, id_rol, telefono, activo, created_at)
                VALUES
                (:nombre, :correo, :password, :id_rol, :telefono, 1, NOW())";

            $stmtUsuario = $this->conn->prepare($sqlUsuario);
            $stmtUsuario->bindParam(":nombre",   $datos['nombre']);
            $stmtUsuario->bindParam(":correo",   $datos['correo']);
            $stmtUsuario->bindParam(":password", $datos['password']);
            $stmtUsuario->bindParam(":id_rol",   $datos['id_rol']);
            $stmtUsuario->bindParam(":telefono", $datos['telefono']);
            $stmtUsuario->execute();

            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            return "Error al registrar: " . $e->getMessage();
        }
    }

    public function obtenerTodos() {
        $sql = "SELECT id_usuario, nombre, correo, id_rol, activo, created_at 
                FROM " . $this->tabla . " ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id_usuario) {
        $sql = "SELECT * FROM " . $this->tabla . " WHERE id_usuario = :id_usuario LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizar($id_usuario, $datos) {
        try {
            // ✅ CORRECCIÓN: el correo NO se actualiza, solo nombre, rol y password (opcional)
            $sql = "UPDATE " . $this->tabla . " 
                    SET nombre = :nombre, id_rol = :id_rol";

            if (!empty($datos['password'])) {
                $sql .= ", password = :password";
            }

            $sql .= " WHERE id_usuario = :id_usuario";

            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":nombre",     $datos['nombre']);
            $stmt->bindParam(":id_rol",     $datos['id_rol']);
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);

            if (!empty($datos['password'])) {
                $stmt->bindParam(":password", $datos['password']);
            }

            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return "Error al actualizar: " . $e->getMessage();
        }
    }

    public function cambiarEstado($id_usuario, $activo) {
        try {
            $sql = "UPDATE " . $this->tabla . " SET activo = :activo WHERE id_usuario = :id_usuario";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":activo",     $activo,     PDO::PARAM_INT);
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return "Error al cambiar estado: " . $e->getMessage();
        }
    }

    public function eliminar($id_usuario) {
        try {
            $sql = "DELETE FROM " . $this->tabla . " WHERE id_usuario = :id_usuario";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (Exception $e) {
            return "Error al eliminar: " . $e->getMessage();
        }
    }
}
?>
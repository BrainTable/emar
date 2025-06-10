<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// registrar_usuario.php
require_once __DIR__ . '/../database/conexion.php';

class Medidor
{
    public static function obtenerTodos($pdo)
    {
        $stmt = $pdo->query("SELECT * FROM medidores");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function crear($pdo, $data)
    {
        $stmt = $pdo->prepare("INSERT INTO medidores (numero_serie, marca, modelo, ubicacion, estado, foto) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $data['numero_serie'],
            $data['marca'],
            $data['modelo'],
            $data['ubicacion'],
            $data['estado'],
            $data['foto']
        ]);
    }

    public static function eliminar($pdo, $id)
    {
        $stmt = $pdo->prepare("DELETE FROM medidores WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public static function actualizar($pdo, $id, $data)
    {
        $stmt = $pdo->prepare("UPDATE medidores SET numero_serie = ?, marca = ?, modelo = ?, ubicacion = ?, estado = ?, foto = ? WHERE id = ?");
        return $stmt->execute([
            $data['numero_serie'],
            $data['marca'],
            $data['modelo'],
            $data['ubicacion'],
            $data['estado'],
            $data['foto'],
            $id
        ]);
    }

    public static function sanitizarDatos($data)
    {
        foreach ($data as $key => $value) {
            $data[$key] = htmlspecialchars(strip_tags(trim($value)));
        }
        return $data;
    }
}
?>
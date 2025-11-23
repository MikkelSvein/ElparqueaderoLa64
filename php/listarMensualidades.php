<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/cors.php';

// Verificar admin
if (!isset($_SESSION['id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
  echo json_encode(["status" => "error", "message" => "Acceso denegado. Solo administradores."]);
  exit;
}

$conn = new mysqli("sql302.infinityfree.com", "if0_40478816", "ingSoftware2", "if0_40478816_parqueadero");
if ($conn->connect_error) {
  echo json_encode(["status" => "error", "message" => "Error al conectar con la base de datos."]);
  exit;
}

// Asegurar que la tabla exista
$conn->query("CREATE TABLE IF NOT EXISTS mensualidades (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NULL,
  nombre_usuario VARCHAR(100) NULL,
  tipo_vehiculo VARCHAR(20) NOT NULL,
  placa VARCHAR(10) NOT NULL,
  telefono VARCHAR(20) NOT NULL,
  precio INT NOT NULL,
  metodo_pago VARCHAR(20) NOT NULL,
  codigo_referencia VARCHAR(50) NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'pendiente',
  fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$result = $conn->query("SELECT id, usuario_id, nombre_usuario, tipo_vehiculo, placa, telefono, precio, metodo_pago, codigo_referencia, fecha_inicio, fecha_fin, estado, fecha_registro FROM mensualidades ORDER BY fecha_registro DESC");

$mensualidades = [];
if ($result) {
  while ($row = $result->fetch_assoc()) {
    $mensualidades[] = $row;
  }
}

echo json_encode(["status" => "ok", "mensualidades" => $mensualidades]);

$conn->close();
?>
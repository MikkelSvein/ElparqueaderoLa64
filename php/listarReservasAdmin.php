<?php
header('Content-Type: application/json');
session_start();

// 🔹 Verificar sesión y rol de admin
if (!isset($_SESSION['id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
  echo json_encode(["status" => "error", "message" => "Acceso denegado. Solo administradores."]);
  exit;
}

// 🔹 Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "parqueadero");
if ($conn->connect_error) {
  echo json_encode(["status" => "error", "message" => "Error al conectar con la base de datos."]);
  exit;
}

// 🔹 Obtener todas las reservas ordenadas por fecha de registro
$stmt = $conn->prepare("SELECT id, usuario_id, nombre_usuario, placa, fecha_entrada, fecha_salida, fecha_registro FROM reservas ORDER BY fecha_registro DESC");
$stmt->execute();
$result = $stmt->get_result();

$reservas = [];
while ($row = $result->fetch_assoc()) {
  $reservas[] = [
    'id' => $row['id'],
    'usuario_id' => $row['usuario_id'],
    'nombre_usuario' => $row['nombre_usuario'],
    'placa' => $row['placa'],
    'fecha_entrada' => $row['fecha_entrada'],
    'fecha_salida' => $row['fecha_salida'],
    'fecha_registro' => $row['fecha_registro']
  ];
}

echo json_encode([
  "status" => "ok",
  "reservas" => $reservas
]);

$stmt->close();
$conn->close();
?>


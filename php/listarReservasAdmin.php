<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/cors.php';
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
  echo json_encode(["status" => "error", "message" => "Acceso denegado. Solo administradores."]);
  exit;
}

$sql1 = "SELECT id, usuario_id, nombre_usuario, placa, fecha_entrada, fecha_salida, fecha_registro FROM reservas ORDER BY fecha_registro DESC";
$stmt = $conn->prepare($sql1);

if (!$stmt) {
  $sql2 = "SELECT id, usuario_id, nombre_usuario, placa, fecha_entrada, fecha_salida FROM reservas ORDER BY fecha_entrada DESC";
  $stmt = $conn->prepare($sql2);
}

if (!$stmt) {
  echo json_encode(["status" => "error", "message" => "Error preparando consulta de reservas."]);
  exit;
}

$stmt->execute();
$result = $stmt->get_result();

$reservas = [];
while ($row = $result->fetch_assoc()) {
  $reservas[] = [
    'id' => $row['id'],
    'usuario_id' => $row['usuario_id'] ?? null,
    'nombre_usuario' => $row['nombre_usuario'] ?? null,
    'placa' => $row['placa'] ?? null,
    'fecha_entrada' => $row['fecha_entrada'] ?? null,
    'fecha_salida' => $row['fecha_salida'] ?? null,
    'fecha_registro' => $row['fecha_registro'] ?? $row['fecha_entrada'] ?? null
  ];
}

echo json_encode([
  "status" => "ok",
  "reservas" => $reservas
]);

$stmt->close();
$conn->close();
?>



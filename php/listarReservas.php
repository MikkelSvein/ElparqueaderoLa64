<?php
header('Content-Type: application/json');
session_start();

// 🔹 Verificar sesión
if (!isset($_SESSION['id']) || !isset($_SESSION['nombre'])) {
  echo json_encode(["status" => "error", "message" => "Debe iniciar sesión."]);
  exit;
}

// 🔹 Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "parqueadero");
if ($conn->connect_error) {
  echo json_encode(["status" => "error", "message" => "Error al conectar con la base de datos."]);
  exit;
}

// 🔹 Obtener reservas del usuario actual
$usuario_id = $_SESSION['id'];
$stmt = $conn->prepare("SELECT id, placa, fecha_entrada, fecha_salida, fecha_registro FROM reservas WHERE usuario_id = ? ORDER BY fecha_registro DESC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$reservas = [];
while ($row = $result->fetch_assoc()) {
  $reservas[] = [
    'id' => $row['id'],
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


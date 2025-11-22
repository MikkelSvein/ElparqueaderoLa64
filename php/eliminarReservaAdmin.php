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

// 🔹 Recibir ID de la reserva a eliminar
$reserva_id = $_POST['reserva_id'] ?? '';

if (empty($reserva_id)) {
  echo json_encode(["status" => "error", "message" => "ID de reserva no proporcionado."]);
  exit;
}

// 🔹 Eliminar la reserva
$delete = $conn->prepare("DELETE FROM reservas WHERE id = ?");
$delete->bind_param("i", $reserva_id);

if ($delete->execute()) {
  // 🔹 Incrementar disponibilidad en 1
  $update = $conn->prepare("UPDATE disponibilidad SET cupos_disponibles = cupos_disponibles + 1 WHERE id = 1");
  $update->execute();
  
  // 🔹 Verificar que no exceda el total de cupos
  $check = $conn->query("SELECT total_cupos, cupos_disponibles FROM disponibilidad WHERE id = 1");
  $row = $check->fetch_assoc();
  if ($row['cupos_disponibles'] > $row['total_cupos']) {
    $fix = $conn->prepare("UPDATE disponibilidad SET cupos_disponibles = total_cupos WHERE id = 1");
    $fix->execute();
  }
  
  echo json_encode(["status" => "ok", "message" => "✅ Reserva eliminada y disponibilidad actualizada correctamente."]);
} else {
  echo json_encode(["status" => "error", "message" => "Error al eliminar la reserva."]);
}

$delete->close();
$update->close();
$conn->close();
?>


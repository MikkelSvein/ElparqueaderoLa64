<?php
header('Content-Type: application/json');
session_start();

// 🔹 Verificar sesión
if (!isset($_SESSION['id']) || !isset($_SESSION['nombre'])) {
  echo json_encode(["status" => "error", "message" => "Debe iniciar sesión."]);
  exit;
}

// 🔹 Conexión a la base de datos
$conn = new mysqli("sql302.infinityfree.com", "if0_40478816", "ingSoftware2", "if0_40478816_parqueadero");
if ($conn->connect_error) {
  echo json_encode(["status" => "error", "message" => "Error al conectar con la base de datos."]);
  exit;
}

// 🔹 Recibir ID de la reserva a eliminar
$reserva_id = $_POST['reserva_id'] ?? '';
$usuario_id = $_SESSION['id'];

if (empty($reserva_id)) {
  echo json_encode(["status" => "error", "message" => "ID de reserva no proporcionado."]);
  exit;
}

// 🔹 Verificar que la reserva pertenece al usuario actual
$stmt = $conn->prepare("SELECT id FROM reservas WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $reserva_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
  echo json_encode(["status" => "error", "message" => "Reserva no encontrada o no tiene permisos para eliminarla."]);
  $stmt->close();
  $conn->close();
  exit;
}

// 🔹 Eliminar la reserva
$delete = $conn->prepare("DELETE FROM reservas WHERE id = ? AND usuario_id = ?");
$delete->bind_param("ii", $reserva_id, $usuario_id);

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

$stmt->close();
$delete->close();
$update->close();
$conn->close();
?>



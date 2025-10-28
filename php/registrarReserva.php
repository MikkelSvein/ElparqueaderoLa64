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

// 🔹 Recibir datos del formulario
$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre'];
$placa = $_POST['placa'] ?? '';
$fecha_entrada = $_POST['fecha_entrada'] ?? '';
$fecha_salida = $_POST['fecha_salida'] ?? '';

if (empty($placa) || empty($fecha_entrada) || empty($fecha_salida)) {
  echo json_encode(["status" => "error", "message" => "Faltan datos en el formulario."]);
  exit;
}

// 🔹 Verificar disponibilidad antes de reservar
$sqlDisponibilidad = "SELECT cupos_disponibles FROM disponibilidad WHERE id = 1"; // 👈 usa el id que manejes
$result = $conn->query($sqlDisponibilidad);

if ($result->num_rows == 0) {
  echo json_encode(["status" => "error", "message" => "No se encontró registro de disponibilidad."]);
  exit;
}

$row = $result->fetch_assoc();
$cuposDisponibles = (int)$row['cupos_disponibles'];

if ($cuposDisponibles <= 0) {
  echo json_encode(["status" => "error", "message" => "❌ No hay cupos disponibles para reservar."]);
  exit;
}

// 🔹 Registrar reserva
$stmt = $conn->prepare("INSERT INTO reservas (usuario_id, nombre_usuario, placa, fecha_entrada, fecha_salida) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $usuario_id, $nombre_usuario, $placa, $fecha_entrada, $fecha_salida);

if ($stmt->execute()) {
  // 🔹 Descontar 1 cupo de disponibilidad
  $update = $conn->prepare("UPDATE disponibilidad SET cupos_disponibles = cupos_disponibles - 1 WHERE id = 1");
  $update->execute();

  echo json_encode(["status" => "ok", "message" => "✅ Reserva registrada y cupo actualizado correctamente."]);
} else {
  echo json_encode(["status" => "error", "message" => "Error al registrar la reserva."]);
}

$stmt->close();
$conn->close();
?>

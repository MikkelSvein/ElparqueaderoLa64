<?php
session_start();
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "parqueadero";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
  exit;
}

if (!isset($_SESSION['usuario_id'])) {
  echo json_encode(['success' => false, 'message' => 'Debe iniciar sesión.']);
  exit;
}

$tipo = $_POST['tipo'] ?? '';

if (empty($tipo)) {
  echo json_encode(['success' => false, 'message' => 'Tipo de vehículo no especificado.']);
  exit;
}

// 🔹 Verificar cupos disponibles
$result = $conn->query("SELECT cupos_disponibles FROM disponibilidad WHERE id = 1");
$row = $result->fetch_assoc();
$cupos = $row['cupos_disponibles'];

if ($cupos <= 0) {
  echo json_encode(['success' => false, 'message' => 'No hay cupos disponibles.']);
  exit;
}

// 🔹 Registrar reserva
$stmt = $conn->prepare("INSERT INTO reservas (usuario_id, tipo_vehiculo) VALUES (?, ?)");
$stmt->bind_param("is", $_SESSION['usuario_id'], $tipo);
$stmt->execute();

// 🔹 Actualizar disponibilidad
$conn->query("UPDATE disponibilidad SET cupos_disponibles = cupos_disponibles - 1 WHERE id = 1");

echo json_encode(['success' => true, 'message' => 'Reserva realizada correctamente.']);
$stmt->close();
$conn->close();
?>

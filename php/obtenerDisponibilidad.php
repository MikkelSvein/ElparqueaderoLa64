<?php
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "parqueadero";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
  echo json_encode(['success' => false, 'message' => 'Error de conexión']);
  exit;
}

$result = $conn->query("SELECT total_cupos, cupos_disponibles FROM disponibilidad WHERE id = 1");
$row = $result->fetch_assoc();

$porcentaje = ($row['cupos_disponibles'] / $row['total_cupos']) * 100;

echo json_encode([
  'success' => true,
  'disponibles' => $row['cupos_disponibles'],
  'porcentaje' => round($porcentaje)
]);

$conn->close();
?>

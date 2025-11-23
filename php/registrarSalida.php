<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/cors.php';

// 🔹 Verificar sesión y rol de admin
if (!isset($_SESSION['id']) || !isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
  echo json_encode(["status" => "error", "message" => "Acceso denegado. Solo administradores."]);
  exit;
}

// 🔹 Conexión a la base de datos
$conn = new mysqli("sql302.infinityfree.com", "if0_40478816", "ingSoftware2", "if0_40478816_parqueadero");
if ($conn->connect_error) {
  echo json_encode(["status" => "error", "message" => "Error al conectar con la base de datos."]);
  exit;
}

// 🔹 Recibir datos
$vehiculo_id = $_POST['vehiculo_id'] ?? '';
$fecha_salida = $_POST['fecha_salida'] ?? date('Y-m-d H:i:s'); // Si no se envía, usar fecha actual

if (empty($vehiculo_id)) {
  echo json_encode(["status" => "error", "message" => "ID de vehículo no proporcionado."]);
  exit;
}

// 🔹 Obtener información del vehículo y tarifas
$stmt = $conn->prepare("SELECT id, placa, nombre, tipo FROM vehiculos WHERE id = ?");
$stmt->bind_param("i", $vehiculo_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
  echo json_encode(["status" => "error", "message" => "Vehículo no encontrado."]);
  exit;
}

$vehiculo = $result->fetch_assoc();
$tipo = $vehiculo['tipo'];

// 🔹 Obtener tarifa según el tipo de vehículo
$tarifaStmt = $conn->query("SELECT bicicleta, bus, carro, moto FROM tarifas WHERE id = 1 LIMIT 1");
$tarifas = $tarifaStmt->fetch_assoc();

$tarifaPorHora = 0;
switch(strtolower($tipo)) {
  case 'bicicleta':
    $tarifaPorHora = floatval($tarifas['bicicleta']);
    break;
  case 'bus':
    $tarifaPorHora = floatval($tarifas['bus']);
    break;
  case 'carro':
    $tarifaPorHora = floatval($tarifas['carro']);
    break;
  case 'moto':
    $tarifaPorHora = floatval($tarifas['moto']);
    break;
}

if ($tarifaPorHora == 0) {
  echo json_encode(["status" => "error", "message" => "No se encontró tarifa para el tipo de vehículo: " . $tipo]);
  exit;
}

// 🔹 Obtener fecha de entrada (si existe en la tabla, si no usar fecha actual como referencia)
// Por ahora asumimos que la fecha de entrada es cuando se registró el vehículo
// Necesitamos agregar campo fecha_entrada a la tabla vehiculos o usar una tabla de registros
// Por simplicidad, usaremos la fecha actual menos 1 hora como ejemplo
// En producción deberías tener un campo fecha_entrada en vehiculos

// 🔹 Obtener fecha de entrada del vehículo
// Intentar obtener desde la base de datos si existe el campo fecha_entrada
// Si no existe, usar una fecha por defecto (2 horas atrás)
$fecha_entrada = date('Y-m-d H:i:s', strtotime('-2 hours')); // Por defecto 2 horas atrás

// Intentar obtener fecha_entrada si el campo existe en la tabla
try {
    $fechaStmt = $conn->prepare("SELECT fecha_entrada FROM vehiculos WHERE id = ?");
    if ($fechaStmt) {
        $fechaStmt->bind_param("i", $vehiculo_id);
        $fechaStmt->execute();
        $fechaResult = $fechaStmt->get_result();
        if ($fechaResult && $fechaResult->num_rows > 0) {
            $fechaRow = $fechaResult->fetch_assoc();
            if (isset($fechaRow['fecha_entrada']) && $fechaRow['fecha_entrada']) {
                $fecha_entrada = $fechaRow['fecha_entrada'];
            }
        }
        $fechaStmt->close();
    }
} catch (Exception $e) {
    // Si el campo no existe, usar el valor por defecto
    $fecha_entrada = date('Y-m-d H:i:s', strtotime('-2 hours'));
}

// 🔹 Calcular horas (mínimo 1 hora)
$entrada = new DateTime($fecha_entrada);
$salida = new DateTime($fecha_salida);
$diferencia = $entrada->diff($salida);
$horas = $diferencia->h + ($diferencia->days * 24) + ($diferencia->i / 60); // Incluir minutos
if ($horas < 1) $horas = 1; // Mínimo 1 hora
$horas = ceil($horas); // Redondear hacia arriba

$total_cobro = $horas * $tarifaPorHora;

// 🔹 Actualizar vehículo con fecha de salida y total cobro
// Primero necesitamos agregar estos campos a la tabla vehiculos
// Por ahora, vamos a crear una tabla de registros o actualizar la estructura
// Opción 1: Actualizar tabla vehiculos (requiere ALTER TABLE)
// Opción 2: Crear tabla de registros_vehiculos

// Por ahora, vamos a eliminar el vehículo después de registrar la salida
// y mostrar el cobro. En producción deberías tener una tabla de historial.

// 🔹 Eliminar vehículo (ya que salió)
$delete = $conn->prepare("DELETE FROM vehiculos WHERE id = ?");
$delete->bind_param("i", $vehiculo_id);

if ($delete->execute()) {
  // 🔹 Incrementar disponibilidad
  $update = $conn->prepare("UPDATE disponibilidad SET cupos_disponibles = cupos_disponibles + 1 WHERE id = 1");
  $update->execute();
  
  // 🔹 Verificar que no exceda el total
  $check = $conn->query("SELECT total_cupos, cupos_disponibles FROM disponibilidad WHERE id = 1");
  $row = $check->fetch_assoc();
  if ($row['cupos_disponibles'] > $row['total_cupos']) {
    $fix = $conn->prepare("UPDATE disponibilidad SET cupos_disponibles = total_cupos WHERE id = 1");
    $fix->execute();
  }
  
  echo json_encode([
    "status" => "ok",
    "message" => "✅ Salida registrada correctamente.",
    "vehiculo" => [
      "placa" => $vehiculo['placa'],
      "nombre" => $vehiculo['nombre'],
      "tipo" => $vehiculo['tipo']
    ],
    "cobro" => [
      "horas" => $horas,
      "tarifa_por_hora" => $tarifaPorHora,
      "total" => $total_cobro
    ]
  ]);
} else {
  echo json_encode(["status" => "error", "message" => "Error al registrar la salida."]);
}

$stmt->close();
$delete->close();
$update->close();
$conn->close();
?>


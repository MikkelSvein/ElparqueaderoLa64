<?php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/cors.php';

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

// 🔹 Recibir datos del formulario
$usuario_id = $_SESSION['id'];
$nombre_usuario = $_SESSION['nombre'];
$tipo_vehiculo = $_POST['tipo_vehiculo'] ?? '';
$metodo_pago = $_POST['metodo_pago'] ?? '';
$placa = $_POST['placa'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$precio = $_POST['precio'] ?? 0;

if (empty($tipo_vehiculo) || empty($metodo_pago) || empty($placa) || empty($telefono)) {
  echo json_encode(["status" => "error", "message" => "Faltan datos en el formulario."]);
  exit;
}

// 🔹 Validar método de pago
if ($metodo_pago === 'nequi' || $metodo_pago === 'tarjeta') {
  echo json_encode(["status" => "error", "message" => "Este método de pago no está disponible aún."]);
  exit;
}

// 🔹 Generar código de referencia único
$codigo_referencia = 'MEN-' . time() . '-' . strtoupper(substr(uniqid(), -6));

// Crear tabla mensualidades si no existe
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

// Fechas de vigencia
$fecha_inicio = date('Y-m-d');
$fecha_fin = date('Y-m-d', strtotime('+1 month'));

// 🔹 Mensaje de éxito según método de pago
if ($metodo_pago === 'efectivo') {
  // Guardar mensualidad en BD (estado pendiente hasta confirmar pago)
  $stmt = $conn->prepare("INSERT INTO mensualidades (usuario_id, nombre_usuario, tipo_vehiculo, placa, telefono, precio, metodo_pago, codigo_referencia, fecha_inicio, fecha_fin, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')");
  $stmt->bind_param("issssissss", $usuario_id, $nombre_usuario, $tipo_vehiculo, $placa, $telefono, $precio, $metodo_pago, $codigo_referencia, $fecha_inicio, $fecha_fin);
  $stmt->execute();

  $mensaje = "Solicitud de mensualidad registrada correctamente.\n\n";
  $mensaje .= "Código de referencia: " . $codigo_referencia . "\n";
  $mensaje .= "Presenta este código en nuestras instalaciones para completar el pago en efectivo.\n";
  $mensaje .= "Tu mensualidad será activada una vez confirmado el pago.\n\n";
  $mensaje .= "Vehículo: " . $tipo_vehiculo . "\n";
  $mensaje .= "Placa: " . $placa . "\n";
  $mensaje .= "Precio: $" . number_format($precio, 0, ',', '.') . "/mes\n";
  $mensaje .= "Vigencia: " . $fecha_inicio . " a " . $fecha_fin;
  
  echo json_encode([
    "status" => "ok",
    "message" => $mensaje,
    "codigo_referencia" => $codigo_referencia,
    "datos" => [
      "tipo_vehiculo" => $tipo_vehiculo,
      "placa" => $placa,
      "precio" => $precio,
      "fecha_inicio" => $fecha_inicio,
      "fecha_fin" => $fecha_fin
    ]
  ]);
} else {
  echo json_encode(["status" => "error", "message" => "Método de pago no válido."]);
}

$conn->close();
?>



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

// 🔹 En un sistema real, aquí se guardaría en una tabla de mensualidades
// Por ahora, solo simulamos el proceso exitoso
// Podrías crear una tabla: mensualidades (id, usuario_id, tipo_vehiculo, placa, telefono, precio, metodo_pago, codigo_referencia, fecha_inicio, fecha_fin, estado, fecha_registro)

// Simulación de registro exitoso
$fecha_inicio = date('Y-m-d');
$fecha_fin = date('Y-m-d', strtotime('+1 month'));

// 🔹 Mensaje de éxito según método de pago
if ($metodo_pago === 'efectivo') {
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



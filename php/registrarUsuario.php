<?php
session_start();
require_once __DIR__ . '/cors.php';

// 🔹 Datos de conexión
$host = "sql302.infinityfree.com";
$user = "if0_40478816";
$pass = "ingSoftware2";
$dbname = "if0_40478816_parqueadero"; 

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$contrasena = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);

// Verificar si ya existe el correo
$check = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
$check->bind_param("s", $correo);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
  echo json_encode(["status" => "error", "message" => "El correo ya está registrado"]);
  exit;
}

// Insertar nuevo usuario
$stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, contrasena) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $nombre, $correo, $contrasena);

if ($stmt->execute()) {
  echo json_encode(["status" => "ok", "message" => "Usuario registrado exitosamente"]);
} else {
  echo json_encode(["status" => "error", "message" => "Error al registrar el usuario"]);
}

$stmt->close();
$conn->close();
?>

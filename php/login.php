<?php
header('Content-Type: application/json');

// 🔹 Datos de conexión
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "parqueadero"; // 👈 asegúrate de que sea el nombre correcto

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// 🔹 Obtener los datos enviados desde JavaScript
$correo = $_POST['correo'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
$rol = $_POST['rol'] ?? '';

if (empty($correo) || empty($contrasena) || empty($rol)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
    exit;
}

// 🔹 Consulta SQL
$sql = "SELECT * FROM usuarios WHERE correo = ? AND contrasena = ? AND rol = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $correo, $contrasena, $rol);
$stmt->execute();
$result = $stmt->get_result();

// 🔹 Validar usuario
if ($result->num_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Correo, contraseña o rol incorrecto.']);
}

$stmt->close();
$conn->close();
?>

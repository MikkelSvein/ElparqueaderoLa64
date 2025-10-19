<?php
header('Content-Type: application/json');

// 🔹 Datos de conexión
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "parqueadero";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

// 🔹 Datos del formulario
$correo = $_POST['correo'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
$rol = $_POST['rol'] ?? '';

if (empty($correo) || empty($contrasena) || empty($rol)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
    exit;
}

// 🔹 Buscar usuario por correo
$sql = "SELECT * FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

// 🔹 Validar usuario
if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Verificar contraseña y rol
    if (password_verify($contrasena, $user['contrasena']) && $user['rol'] === $rol) {
        echo json_encode(['success' => true, 'nombre' => $user['nombre']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Correo, rol o contraseña incorrectos.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
}

$stmt->close();
$conn->close();
?>

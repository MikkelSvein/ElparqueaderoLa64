<?php
session_start();
header('Content-Type: application/json');

$host = "sql302.infinityfree.com";
$user = "if0_40478816";
$pass = "ingSoftware2";
$dbname = "if0_40478816_parqueadero";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos.']);
    exit;
}

$correo = $_POST['correo'] ?? '';
$contrasena = $_POST['contrasena'] ?? '';
$rol = $_POST['rol'] ?? '';

if (empty($correo) || empty($contrasena) || empty($rol)) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
    exit;
}

$sql = "SELECT * FROM usuarios WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    if (password_verify($contrasena, $user['contrasena']) && $user['rol'] === $rol) {
        // 🔹 Crear la sesión
        $_SESSION['id'] = $user['id'];
        $_SESSION['nombre'] = $user['nombre'];
        $_SESSION['correo'] = $user['correo'];
        $_SESSION['rol'] = $user['rol'];

        echo json_encode(['success' => true, 'nombre' => $user['nombre'], 'rol' => $user['rol']]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Correo, contraseña o rol incorrectos.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
}

$stmt->close();
$conn->close();
?>

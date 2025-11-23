<?php
session_start();

// Conectar base de datos
$host = "sql302.infinityfree.com";
$user = "if0_40478816";
$pass = "ingSoftware2";
$dbname = "if0_40478816_parqueadero";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Read incoming JSON from fetch()
$input = file_get_contents("php://input");
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid JSON"]);
    exit;
}

// Extraer la informacion del usuario del Firebase login
$firebase_uid = $data["firebaseUid"];
$correo       = $data["email"];
$nombre       = $data["displayName"];

// Revisar si el usuario existe usando Firebase UID
$sql = "SELECT * FROM usuarios WHERE firebase_uid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $firebase_uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {

    // Usuario existente
    $user = $result->fetch_assoc();

} else {

    // Crear nuevo usuario
    $sql = "INSERT INTO usuarios (nombre, correo, firebase_uid, rol)
            VALUES (?, ?, ?, 'usuario')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $nombre, $correo, $firebase_uid);
    $stmt->execute();

    $user = [
        "id" => $stmt->insert_id,
        "nombre" => $nombre,
        "correo" => $correo,
        "rol" => "usuario"
    ];
}

// Crear sesion
$_SESSION['id']     = $user['id'];
$_SESSION['nombre'] = $user['nombre'];
$_SESSION['correo'] = $user['correo'];
$_SESSION['rol']    = $user['rol'];

// Responder al front end
echo json_encode([
    "status"  => "success",
    "message" => "User saved",
    "user"    => $user
]);
exit;

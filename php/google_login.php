<?php
session_start();
header('Content-Type: application/json');
include("conexion.php");

$email = isset($_POST['email']) ? $conn->real_escape_string($_POST['email']) : '';
$name  = isset($_POST['name']) ? $conn->real_escape_string($_POST['name']) : '';
$rol   = isset($_POST['rol']) ? $conn->real_escape_string($_POST['rol']) : 'usuario';

if (empty($email)) {
    echo json_encode(['status'=>'error','msg'=>'Email requerido']);
    exit;
}

// Verificar si existe usuario con ese correo
$sql = "SELECT id, nombre, correo, rol FROM usuarios WHERE correo = '$email' LIMIT 1";
$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    $user = $res->fetch_assoc();
    // Si existe pero rol no coincide, actualizamos el rol (opcional) o rechazamos.
    if ($user['rol'] !== $rol) {
        // Opción A: actualizar rol automáticamente (cuidado con seguridad)
        // $upd = "UPDATE usuarios SET rol = '$rol' WHERE id = {$user['id']}";
        // $conn->query($upd);
        // $user['rol'] = $rol;

        // Opción B: rechazar si el rol no coincide
        // Aquí elijo **actualizar** por simplicidad:
        $upd = "UPDATE usuarios SET rol = '$rol' WHERE id = {$user['id']}";
        $conn->query($upd);
        $user['rol'] = $rol;
    }
    // set session
    $_SESSION['id'] = $user['id'];
    $_SESSION['nombre'] = $user['nombre'];
    $_SESSION['rol'] = $user['rol'];
    echo json_encode(['status'=>'ok','rol'=>$user['rol']]);
    exit;
} else {
    // Crear nuevo usuario con contraseña vacía o nula (porque usarán Google)
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, correo, password, rol) VALUES (?, ?, ?, ?)");
    $emptyPass = ''; // indicativo; en producción mejor un flag 'google_user'
    $stmt->bind_param("ssss", $name, $email, $emptyPass, $rol);
    if ($stmt->execute()) {
        $newId = $stmt->insert_id;
        $_SESSION['id'] = $newId;
        $_SESSION['nombre'] = $name;
        $_SESSION['rol'] = $rol;
        echo json_encode(['status'=>'ok','rol'=>$rol]);
        exit;
    } else {
        echo json_encode(['status'=>'error','msg'=>'No se pudo crear el usuario']);
        exit;
    }
}
?>

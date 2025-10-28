<?php
session_start();
include 'db.php';

$correo = $_POST['correo'];
$clave = md5($_POST['clave']); // Encripta con MD5

$sql = "SELECT * FROM usuarios WHERE correo='$correo' AND clave='$clave'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();

    $_SESSION['id'] = $usuario['id'];
    $_SESSION['nombre'] = $usuario['nombre'];
    $_SESSION['rol'] = $usuario['rol'];

    header("Location: buscar.php");
    exit;
} else {
    echo "❌ Credenciales incorrectas. <a href='login.php'>Volver</a>";
}
?>

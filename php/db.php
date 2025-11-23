<?php
$host = "sql302.infinityfree.com";
$user = "if0_40478816";
$pass = "ingSoftware2";
$dbname = "if0_40478816_parqueadero";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>

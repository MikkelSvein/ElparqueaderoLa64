<?php
sesion_start();
require "db.php";

$placa = $_POST["placa"] ?? "";
$nombre = $_POST["nombre"] ?? "";
$documento = $_POST["documento"] ?? "";
$tipo = $_POST["tipo"] ?? "";

if(!$placa || !$nombre || !$documento || !$tipo) {
    die("Todos los campos son obligatorios.");
}

$stmt = $conn->prepare("INSERT INTO vehiculos (placa, nombre, documento, tipo) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $placa, $nombre, $documento, $tipo);
if($stmt->execute()) {
    echo "Vehículo registrado con éxito.";
} else {
    echo "Error: " . $conn->error;
}
$stmt->close();
$conn->close();
?>

<?php
session_start();
require "db.php";

$placa = $_POST["placa"] ?? "";
$nombre = $_POST["nombre"] ?? "";
$documento = $_POST["documento"] ?? "";
$tipo = $_POST["tipo"] ?? "";

if(!$placa || !$nombre || !$documento || !$tipo) {
    die("Todos los campos son obligatorios.");
}

// Verificar si el campo fecha_entrada existe en la tabla
$campo_existe = false;
$result = $conn->query("SHOW COLUMNS FROM vehiculos LIKE 'fecha_entrada'");
if ($result && $result->num_rows > 0) {
    $campo_existe = true;
}

// Agregar fecha de entrada actual si el campo existe
$fecha_entrada = date('Y-m-d H:i:s');

// Preparar y ejecutar INSERT según si el campo existe o no
$exito = false;
$mensaje = "";

if ($campo_existe) {
    // Insertar con fecha_entrada
    $stmt = $conn->prepare("INSERT INTO vehiculos (placa, nombre, documento, tipo, fecha_entrada) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssss", $placa, $nombre, $documento, $tipo, $fecha_entrada);
        if ($stmt->execute()) {
            $exito = true;
            $mensaje = "Vehículo registrado con éxito.";
        } else {
            $mensaje = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $mensaje = "Error al preparar la consulta.";
    }
} else {
    // Insertar sin fecha_entrada
    $stmt = $conn->prepare("INSERT INTO vehiculos (placa, nombre, documento, tipo) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $placa, $nombre, $documento, $tipo);
        if ($stmt->execute()) {
            $exito = true;
            $mensaje = "Vehículo registrado con éxito.";
        } else {
            $mensaje = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $mensaje = "Error al preparar la consulta.";
    }
}

echo $mensaje;
$conn->close();
?>

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

// Verificar cupos disponibles
$cupos = 0;
$resDisp = $conn->query("SELECT cupos_disponibles, total_cupos FROM disponibilidad WHERE id = 1");
if ($resDisp && $row = $resDisp->fetch_assoc()) {
    $cupos = intval($row['cupos_disponibles']);
}
if ($cupos <= 0) {
    echo "No hay cupos disponibles.";
    $conn->close();
    exit;
}

if ($campo_existe) {
    // Insertar con fecha_entrada
    $stmt = $conn->prepare("INSERT INTO vehiculos (placa, nombre, documento, tipo, fecha_entrada) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssss", $placa, $nombre, $documento, $tipo, $fecha_entrada);
        if ($stmt->execute()) {
            $exito = true;
            $mensaje = "Vehículo registrado con éxito.";
            // Descontar 1 cupo
            $conn->query("UPDATE disponibilidad SET cupos_disponibles = cupos_disponibles - 1 WHERE id = 1");
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
            // Descontar 1 cupo
            $conn->query("UPDATE disponibilidad SET cupos_disponibles = cupos_disponibles - 1 WHERE id = 1");
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

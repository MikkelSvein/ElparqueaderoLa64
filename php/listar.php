<?php
session_start();
require "db.php";

$por_pagina = 5;
$pagina = isset($_GET["pagina"]) ? intval($_GET["pagina"]) : 1;
$inicio = ($pagina - 1) * $por_pagina;

$total = $conn->query("SELECT COUNT(*) as total FROM vehiculos")->fetch_assoc()["total"];
$paginas = ceil($total / $por_pagina);

$result = $conn->query("SELECT * FROM vehiculos LIMIT $inicio, $por_pagina");

echo "<table border='1' style='width:100%; border-collapse:collapse;'><tr><th>Placa</th><th>Nombre</th><th>Documento</th><th>Tipo</th><th>Acción</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['placa']}</td>
        <td>{$row['nombre']}</td>
        <td>{$row['documento']}</td>
        <td>{$row['tipo']}</td>
        <td><button class='btn-salida' onclick='registrarSalida({$row['id']})'>Registrar Salida</button></td>
    </tr>";
}
echo "</table>";

for($i=1; $i<=$paginas; $i++) {
    echo "<button onclick='cargarVehiculos($i)'>$i</button> ";
}
?>

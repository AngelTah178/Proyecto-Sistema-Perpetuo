<?php
include "conexion.php";
#REPORTE BY JACK NICHOLSON
$inicio = $_GET['inicio'] ?? '';
$fin = $_GET['fin'] ?? '';
$tipo = $_GET['tipo'] ?? '';

$condiciones = [];
if (!empty($inicio) && !empty($fin)) {
    $condiciones[] = "m.FECHA_REGISTRO BETWEEN '$inicio 00:00:00' AND '$fin 23:59:59'";
}
if (!empty($tipo)) {
    $tipo = (int) $tipo;
    $condiciones[] = "m.TIPO_ID = $tipo";
}

$sql = "
    SELECT
        m.FECHA_REGISTRO, 
        m.CANTIDAD,
        tm.MOVIMIENTO AS MOVIMIENTO,
        p.NOMBRE AS PRODUCTO,
        a.ALMACEN,
        pr.NOMBRE AS PROVEEDOR

    FROM movimientos m

    LEFT JOIN tipo_movimientos tm
        ON m.TIPO_ID = tm.TIPO_ID

    LEFT JOIN productos p
        ON m.PRODUCTO_ID = p.PRODUCTO_ID

    LEFT JOIN almacenes a 
        ON m.ALMACEN_ID = a.ALMACEN_ID

    LEFT JOIN proveedores pr 
        ON m.PROVEEDOR_ID = pr.PROVEEDOR_ID

    WHERE 1=1
";

if (count($condiciones) > 0) {
    $sql .= " AND " . implode(" AND ", $condiciones);
}
$sql .= " ORDER BY m.FECHA_REGISTRO DESC";


$result = $conn->query($sql);

echo "<table class='table table-bordered'>";
echo "<tr>
        <th>Tipo de movimiento</th>
        <th>Fecha</th>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Almacén</th>
<th>Proveedor</th>
      </tr>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
        <td>{$row['MOVIMIENTO']}</td>
        <td>{$row['FECHA_REGISTRO']}</td>
        <td>{$row['PRODUCTO']}</td>
<td>{$row['CANTIDAD']}</td>
<td>{$row['ALMACEN']}</td>
<td>{$row['PROVEEDOR']}</td>
      </tr>";
}

echo "</table>";
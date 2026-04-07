<?php
require_once __DIR__ . "/dompdf/autoload.inc.php";
use Dompdf\Dompdf;

include "conexion.php";

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
    tm.MOVIMIENTO,
    p.NOMBRE AS PRODUCTO,
    a.ALMACEN,
    u.NOMBRE AS USUARIO,
    pr.NOMBRE AS PROVEEDOR
FROM movimientos m
LEFT JOIN tipo_movimientos tm ON m.TIPO_ID = tm.TIPO_ID
LEFT JOIN usuarios u ON m.ID_USUARIO = u.ID_USUARIO
LEFT JOIN productos p ON m.PRODUCTO_ID = p.PRODUCTO_ID
LEFT JOIN almacenes a ON m.ALMACEN_ID = a.ALMACEN_ID
LEFT JOIN proveedores pr ON m.PROVEEDOR_ID = pr.PROVEEDOR_ID
WHERE 1=1
";

if (count($condiciones) > 0) {
    $sql .= " AND " . implode(" AND ", $condiciones);
}

$sql .= " ORDER BY m.FECHA_REGISTRO DESC";

$result = $conn->query($sql);

# ================= HTML DEL PDF =================
$html = '
<h2 style="text-align:center;">Reporte de Movimientos</h2>

<table border="1" width="100%" cellpadding="5" cellspacing="0">
<tr style="background:#eee;">
    <th>Tipo</th>
    <th>Usuario</th>
    <th>Fecha</th>
    <th>Producto</th>
    <th>Cantidad</th>
    <th>Almacén</th>
    <th>Proveedor</th>
</tr>';

while ($row = $result->fetch_assoc()) {
    $html .= "<tr>
        <td>{$row['MOVIMIENTO']}</td>
        <td>{$row['USUARIO']}</td>
        <td>{$row['FECHA_REGISTRO']}</td>
        <td>{$row['PRODUCTO']}</td>
        <td>{$row['CANTIDAD']}</td>
        <td>{$row['ALMACEN']}</td>
        <td>{$row['PROVEEDOR']}</td>
    </tr>";
}

$html .= "</table>";

# ================= GENERAR PDF =================
$dompdf = new Dompdf();
$dompdf->loadHtml($html);

$dompdf->setPaper("letter", "portrait");

$dompdf->render();

$dompdf->stream("reporte.pdf", ["Attachment" => true]);
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
# ================= HTML DEL PDF =================
$path = __DIR__ . '/assets/Logo.png';

if (file_exists($path)) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);

    $img = '<img src="' . $base64 . '" width="200">';
} else {
    $img = '<p>NO SE ENCONTRÓ EL LOGO</p>';
}
$html = '
<style>
    .header {
        width: 100%;
        margin-bottom: 20px;
    }

    .logo {
        position: absolute;
        top: 10px;
        left: 10px;
        width: 80px;
    }

    h2 {
        text-align: center;
        margin: 0;
        padding-top: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 40px;
        font-size: 12px;
    }

    th, td {
        border: 1px solid #999;
        padding: 6px;
        text-align: center;
    }

    th {
        background: #eee;
    }

    .entrada { color: green; font-weight: bold; }
    .salida  { color: purple; font-weight: bold; }
    .alta    { color: blue; font-weight: bold; }
    .baja    { color: red; font-weight: bold; }
    .edicion { color: deeppink; font-weight: bold; }
</style>

<div class="header">
' . $img . '
    <h2>Reporte de Movimientos</h2>
<table>
<tr>
    <th>Tipo</th>
    <th>Usuario</th>
    <th>Fecha</th>
    <th>Producto</th>
    <th>Cantidad</th>
    <th>Almacén</th>
    <th>Proveedor</th>
</tr>';

while ($row = $result->fetch_assoc()) {

    // color según tipo
    $tipo = strtolower($row['MOVIMIENTO']);

    if ($tipo == 'entrada')
        $clase = 'entrada';
    elseif ($tipo == 'salida')
        $clase = 'salida';
    elseif ($tipo == 'alta')
        $clase = 'alta';
    elseif ($tipo == 'baja')
        $clase = 'baja';
    elseif ($tipo == 'edicion' || $tipo == 'edición')
        $clase = 'edicion';
    else
        $clase = '';

    $html .= "<tr>
        <td class='$clase'>{$row['MOVIMIENTO']}</td>
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
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);

$dompdf->setPaper("letter", "portrait");

$dompdf->render();

$dompdf->stream("reporte.pdf", ["Attachment" => true]);
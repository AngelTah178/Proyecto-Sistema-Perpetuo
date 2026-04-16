<?php
  require_once __DIR__ . "/dompdf/autoload.inc.php";

  use Dompdf\Dompdf;
  use Dompdf\Options;

  include "conexion.php";

  // ================== CONFIG ==================
  date_default_timezone_set("America/Cancun");

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

  // ================== CONSULTA ==================
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

  // ================== TOTALES ==================
  $totales = [
    'entrada' => 0,
    'salida' => 0,
    'alta' => 0,
    'baja' => 0,
    'edicion' => 0
  ];

  $conteoMovimientos = [
    'alta' => 0,
    'baja' => 0,
    'edicion' => 0
  ];

  // ================== FECHAS ==================
  $fechaActual = date("d/m/Y H:i");
  $fechaGeneracion = date("d-m-Y");
  $inicioFormat = !empty($inicio) ? date("d/m/Y", strtotime($inicio)) : "Todos";
  $finFormat = !empty($fin) ? date("d/m/Y", strtotime($fin)) : "Todos";

  // ================== LOGO ==================
  $path = __DIR__ . '/assets/Logo.png';

  if (file_exists($path)) {
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
    $img = '<img src="' . $base64 . '" style="width:120px;">';
  } else {
    $img = '';
  }

  // ================== HTML ==================
  $html = '
  <style>
    body { font-family: Arial, sans-serif; }

    .header { position: relative; margin-bottom: 20px; }
    .logo { position: absolute; top: 0; left: 0; }

    .titulo {
      text-align: center;
      font-size: 22px;
      font-weight: bold;
    }

    .info {
      text-align: center;
      font-size: 12px;
      margin-top: 5px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
      font-size: 11px;
    }

    th, td {
      border: 1px solid #999;
      padding: 6px;
      text-align: center;
    }

    th {
      background: #0d2b3e;
      color: white;
    }

    .entrada { color: green; font-weight: bold; }
    .salida  { color: purple; font-weight: bold; }
    .alta    { color: blue; font-weight: bold; }
    .baja    { color: red; font-weight: bold; }
    .edicion { color: deeppink; font-weight: bold; }
  </style>

  <div class="header">
    <div class="logo">' . $img . '</div>

    <div style="position:absolute; top:0; right:0; font-size:10px; color:#555;">
      Reporte generado el ' . $fechaGeneracion . '
    </div>

    <div class="titulo">Reporte de Movimientos</div>
    <div class="info">
      Fecha: ' . $fechaActual . ' |
      Inicio: ' . $inicioFormat . ' |
      Fin: ' . $finFormat . '
    </div>
  </div>

  <table>
    <tr>
      <th>Tipo</th>
      <th>Usuario</th>
      <th>Fecha</th>
      <th>Producto</th>
      <th>Cantidad</th>
      <th>Almacén</th>
      <th>Proveedor</th>
    </tr>
  ';

  // ================== FILAS ==================
  while ($row = $result->fetch_assoc()) {

    $tipoMov = strtolower($row['MOVIMIENTO']);
    $cantidad = (int) $row['CANTIDAD'];

    if ($tipoMov == 'edición') {
      $tipoMov = 'edicion';
    }

    if (isset($totales[$tipoMov])) {
      $totales[$tipoMov] += $cantidad;
    }

    if (isset($conteoMovimientos[$tipoMov])) {
      $conteoMovimientos[$tipoMov]++;
    }

    $clase = $tipoMov;

    $html .= "
    <tr>
      <td class='$clase'>{$row['MOVIMIENTO']}</td>
      <td>{$row['USUARIO']}</td>
      <td>{$row['FECHA_REGISTRO']}</td>
      <td>{$row['PRODUCTO']}</td>
      <td>{$cantidad}</td>
      <td>{$row['ALMACEN']}</td>
      <td>{$row['PROVEEDOR']}</td>
    </tr>";
  }

  $html .= "</table>

  <br><br>

  <div style='width:100%;'>

    <div style='width:48%; float:left;'>

      <h3 style='text-align:center;'>Movimientos de Inventario</h3>

      <table>
        <tr>
          <th>Tipo</th>
          <th>Cantidad</th>
        </tr>
        <tr><td>Entradas</td><td>{$totales['entrada']}</td></tr>
        <tr><td>Salidas</td><td>{$totales['salida']}</td></tr>
      </table>

    </div>

    <div style='width:48%; float:right;'>

      <h3 style='text-align:center;'>Otros Movimientos</h3>

      <table>
        <tr>
          <th>Tipo</th>
          <th>Total de movimientos</th>
        </tr>
        <tr><td>Altas</td><td>{$conteoMovimientos['alta']}</td></tr>
        <tr><td>Bajas</td><td>{$conteoMovimientos['baja']}</td></tr>
        <tr><td>Edición</td><td>{$conteoMovimientos['edicion']}</td></tr>
      </table>

    </div>

    <div style='clear:both;'></div>

  </div>

  <br><br>
  ";

  // ================== TOTALES FINALES ==================
  $totalInventario = $totales['entrada'] + $totales['salida'];
  $totalMovimientos = $conteoMovimientos['alta'] + $conteoMovimientos['baja'] + $conteoMovimientos['edicion'];

  $html .= "
  <h3 style='text-align:center;'>Resumen General</h3>

  <table>
    <tr>
      <th>Total productos movidos</th>
      <th>Total de movimientos</th>
    </tr>
    <tr>
      <td>{$totalInventario}</td>
      <td>{$totalMovimientos}</td>
    </tr>
  </table>
  ";

  // ================== DOMPDF ==================
  $options = new Options();
  $options->set('isRemoteEnabled', true);
  $options->set('isHtml5ParserEnabled', true);

  $dompdf = new Dompdf($options);

  $dompdf->loadHtml($html);
  $dompdf->setPaper("letter", "portrait");

  $dompdf->render();

  $canvas = $dompdf->getCanvas();
  $canvas->page_text(450, 770, "Página {PAGE_NUM} de {PAGE_COUNT}", null, 10, [0, 0, 0]);

  $dompdf->stream("reporte.pdf", ["Attachment" => true]);
?>
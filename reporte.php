<?php
  include "conexion.php";

  $inicio = $_GET['inicio'] ?? '';
  $fin = $_GET['fin'] ?? '';
  $tipo = $_GET['tipo'] ?? '';

  $condiciones = [];

  // 
  // CORRECCIÓN AQUÍ
  if (!empty($inicio) && !empty($fin)) {
      $condiciones[] = "DATE(m.FECHA_REGISTRO) BETWEEN '$inicio' AND '$fin'";
  }

  // 

  // FILTRO TIPO BIEN VALIDADO
  if (isset($_GET['tipo']) && $_GET['tipo'] !== '') {
    $tipo = (int) $_GET['tipo'];
    $condiciones[] = "m.TIPO_ID = $tipo";
  }

  $sql = "
    SELECT
      m.FECHA_REGISTRO, 
      m.CANTIDAD,
      tm.MOVIMIENTO AS MOVIMIENTO,
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

  if (!empty($condiciones)) {
    $sql .= " AND " . implode(" AND ", $condiciones);
  }

  $sql .= " ORDER BY m.FECHA_REGISTRO DESC";

  $result = $conn->query($sql);

  echo "<table class='table table-bordered'>";
  echo "<tr>
    <th>Tipo de movimiento</th>
    <th>Usuario</th>
    <th>Fecha</th>
    <th>Producto</th>
    <th>Cantidad</th>
    <th>Almacén</th>
    <th>Proveedor</th>
  </tr>";

  if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
      echo "<tr>
        <td>{$row['MOVIMIENTO']}</td>
        <td>{$row['USUARIO']}</td>
        <td>{$row['FECHA_REGISTRO']}</td>
        <td>{$row['PRODUCTO']}</td>
        <td>{$row['CANTIDAD']}</td>
        <td>{$row['ALMACEN']}</td>
        <td>{$row['PROVEEDOR']}</td>
      </tr>";
    }
  } else {
    // CUANDO NO HAY DATOS
    echo "<tr>
      <td colspan='7' class='text-center'>No hay resultados</td>
    </tr>";
  }

  echo "</table>";
?>
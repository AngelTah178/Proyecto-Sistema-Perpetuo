<?php
  include "conexion.php";

  $codigo = $_GET['codigo'] ?? '';

  if ($codigo == '') {
      echo json_encode([]);
      exit;
  }

  $stmt = $conn->prepare("
    SELECT 
      p.PRODUCTO_ID,
      p.NOMBRE,
      pr.PROVEEDOR_ID,
      pr.NOMBRE AS PROVEEDOR,
      a.ALMACEN_ID,
      a.ALMACEN,
      s.UNIDADES
    FROM productos p
    LEFT JOIN proveedores pr ON p.PROVEEDOR_ID = pr.PROVEEDOR_ID
    LEFT JOIN stock s ON p.PRODUCTO_ID = s.PRODUCTO_ID
    LEFT JOIN almacenes a ON s.ALMACEN_ID = a.ALMACEN_ID
    WHERE p.CODIGO_BARRAS = ?
  ");

  $stmt->bind_param("s", $codigo);
  $stmt->execute();
  $result = $stmt->get_result();

  $data = [];

  while ($row = $result->fetch_assoc()) {
      $data[] = $row;
  }

  echo json_encode($data);

?>
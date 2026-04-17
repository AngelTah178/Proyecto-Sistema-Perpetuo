<?php
  include "conexion.php";

  header('Content-Type: application/json');

  $codigo = $_GET['codigo'] ?? '';

  if ($codigo === '') {
    echo json_encode([]);
    exit;
  }

  $stmt = $conn->prepare("
    SELECT 
      p.PRODUCTO_ID,
      p.NOMBRE,
      p.PROVEEDOR_ID,
      pr.NOMBRE AS PROVEEDOR,
      s.UNIDADES,
      a.ALMACEN,
      a.ALMACEN_ID
    FROM productos p
    LEFT JOIN proveedores pr ON p.PROVEEDOR_ID = pr.PROVEEDOR_ID
    LEFT JOIN stock s ON p.PRODUCTO_ID = s.PRODUCTO_ID
    LEFT JOIN almacenes a ON s.ALMACEN_ID = a.ALMACEN_ID
    WHERE p.CODIGO_BARRAS = ?
    AND p.ESTADO = 1
  ");

  $stmt->bind_param("s", $codigo);
  $stmt->execute();
  $res = $stmt->get_result();

  $data = [];

  while ($row = $res->fetch_assoc()) {
    $data[] = $row;
  }

  echo json_encode($data);
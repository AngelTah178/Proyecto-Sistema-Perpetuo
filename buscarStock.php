<?php
  include "conexion.php";

  $q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : "";

  $sql = "
  SELECT 
    p.NOMBRE,
    p.CODIGO_BARRAS,
    m.NOMBRE AS MARCA,
    l.LOTE_ID,
    a.ALMACEN,
    u.PASILLO,
    u.ESTANTE,
    u.NIVEL,
    u.SECCION,
    s.UNIDADES
  FROM stock s
  INNER JOIN productos p ON s.PRODUCTO_ID = p.PRODUCTO_ID
  LEFT JOIN marcas m ON p.MARCA_ID = m.MARCA_ID
  LEFT JOIN lotes l ON p.LOTE_ID = l.LOTE_ID
  INNER JOIN almacenes a ON s.ALMACEN_ID = a.ALMACEN_ID
  INNER JOIN ubicaciones u ON s.UBICACION_ID = u.UBICACION_ID
  WHERE 
    p.NOMBRE LIKE '%$q%' OR
    p.CODIGO_BARRAS LIKE '%$q%' OR
    m.NOMBRE LIKE '%$q%'
  ";

  $result = $conn->query($sql);

  $data = [];

  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  echo json_encode($data);
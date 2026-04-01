<?php
  include "conexion.php";

  $q = $_GET['q'] ?? '';

  $stmt = $conn->prepare("
    SELECT 
      p.PRODUCTO_ID,
      p.CODIGO_BARRAS,
      p.SKU,
      p.NOMBRE,
      p.DESCRIPCION,
      p.PRECIO,
      p.FECHA_REGISTRO,
      p.LOTE_ID,
      m.NOMBRE AS MARCA,
      c.NOMBRE AS CATEGORIA,
      pr.NOMBRE AS PROVEEDOR
    FROM productos p
    LEFT JOIN marcas m ON p.MARCA_ID = m.MARCA_ID
    LEFT JOIN categorias c ON p.CATEGORIA_ID = c.CATEGORIA_ID
    LEFT JOIN proveedores pr ON p.PROVEEDOR_ID = pr.PROVEEDOR_ID
    WHERE 
      p.NOMBRE LIKE ? 
      OR p.CODIGO_BARRAS LIKE ?
  ");

  $like = "%$q%";
  $stmt->bind_param("ss", $like, $like);
  $stmt->execute();

  $result = $stmt->get_result();

  $data = [];

  while ($row = $result->fetch_assoc()) {
    $data[] = $row;
  }

  echo json_encode($data);
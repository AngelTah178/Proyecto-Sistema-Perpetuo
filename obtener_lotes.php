<?php
  include 'conexion.php';

  $categoria_id = $_POST['categoria_id'];

  switch($categoria_id){
    case 1: $filtro = "Resistencias"; break;
    case 2: $filtro = "Capacitores"; break;
    case 3: $filtro = "Inductores"; break;
    case 4: $filtro = "Diodos|Transistores"; break;
    case 5: $filtro = "Circuitos Integrados"; break;
    case 6: $filtro = "Microcontroladores"; break;
    case 7: $filtro = "Sensores"; break;
    case 8: $filtro = "Conectores"; break;
    case 9: $filtro = "Protoboards|PCB"; break;
    case 10: $filtro = "Cables"; break;
    default: $filtro = "";
  }

  $query = "
  SELECT l.lote_id, u.seccion
  FROM lotes l
  JOIN ubicaciones u ON l.ubicacion_id = u.ubicacion_id
  WHERE u.seccion REGEXP '$filtro'
  ";

  $result = $conexion->query($query);

  echo '<option value="">Selecciona</option>';

  while ($row = $result->fetch_assoc()) {
    echo "<option value='{$row['lote_id']}'>
      Lote {$row['lote_id']} ({$row['seccion']})
    </option>";
  }
?>
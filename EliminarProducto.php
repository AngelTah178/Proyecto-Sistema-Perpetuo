<?php
session_start();
include "conexion.php";

// ================== VALIDAR SESIÓN ==================
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
  header("Location: index.php");
  exit;
}

// ================== VALIDAR ID ==================
$id = $_POST['id'] ?? null;

if (!$id) {
  $_SESSION['mensajeProducto'] = "ID inválido";
  $_SESSION['tipoProducto'] = "danger";
  header("Location: index.php");
  exit;
}

// ================== VERIFICAR ESTADO ==================
$stmt = $conn->prepare("SELECT ESTADO, PROVEEDOR_ID FROM productos WHERE PRODUCTO_ID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

if (!$res) {
  $_SESSION['mensajeProducto'] = "Producto no encontrado";
  $_SESSION['tipoProducto'] = "danger";
  header("Location: index.php");
  exit;
}

if ($res['ESTADO'] == 0) {
  $_SESSION['mensajeProducto'] = "Este producto ya está eliminado";
  $_SESSION['tipoProducto'] = "warning";
  header("Location: index.php");
  exit;
}

// ================== DATOS PARA MOVIMIENTO ==================
$id_usuario = $_SESSION['ID_USUARIO'];
date_default_timezone_set("America/Mexico_City");

$fecha = date("Y-m-d H:i:s");
$cantidad = 0;
$tipo = 4; // BAJA
$almacen_id = null;
$proveedor_id = $res['PROVEEDOR_ID'];

// ================== INSERTAR MOVIMIENTO (BAJA) ==================
$mov = $conn->prepare("
  INSERT INTO movimientos 
  (FECHA_REGISTRO, CANTIDAD, TIPO_ID, ID_USUARIO, PROVEEDOR_ID, PRODUCTO_ID, ALMACEN_ID) 
  VALUES (?, ?, ?, ?, ?, ?, ?)
");

$mov->bind_param(
  "siiiiii",
  $fecha,
  $cantidad,
  $tipo,
  $id_usuario,
  $proveedor_id,
  $id,
  $almacen_id
);

if (!$mov->execute()) {
  $_SESSION['mensajeProducto'] = "Error al registrar movimiento: " . $mov->error;
  $_SESSION['tipoProducto'] = "danger";
  header("Location: index.php");
  exit;
}

// ================== BAJA LÓGICA ==================
$stmt = $conn->prepare("
  UPDATE productos 
  SET ESTADO = 0 
  WHERE PRODUCTO_ID = ?
");

$stmt->bind_param("i", $id);

if ($stmt->execute()) {

  $_SESSION['mensajeProducto'] = "Producto eliminado correctamente";
  $_SESSION['tipoProducto'] = "success";

} else {

  $_SESSION['mensajeProducto'] = "Error al eliminar producto";
  $_SESSION['tipoProducto'] = "danger";
}

header("Location: index.php");
exit;
?>
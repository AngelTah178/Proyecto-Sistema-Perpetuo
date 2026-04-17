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

  $id = intval($id);

  // ================== VERIFICAR PRODUCTO ==================
  $stmt = $conn->prepare("
    SELECT ESTADO, PROVEEDOR_ID 
    FROM productos 
    WHERE PRODUCTO_ID = ?
  ");
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

  // ================== VALIDAR PROVEEDOR ==================
  $proveedor_id = intval($res['PROVEEDOR_ID']);

  if ($proveedor_id <= 0) {
    $_SESSION['mensajeProducto'] = "Error: proveedor inválido en el producto";
    $_SESSION['tipoProducto'] = "danger";
    header("Location: index.php");
    exit;
  }

  // ================== DATOS GENERALES ==================
  $id_usuario = $_SESSION['ID_USUARIO'];
  date_default_timezone_set("America/Mexico_City");

  $fecha = date("Y-m-d H:i:s");
  $cantidad = 0;
  $tipo = 4; // BAJA
  $almacen_id = null;

  // ================== TRANSACCIÓN ==================
  $conn->begin_transaction();

  try {

    // ================== BAJA LÓGICA ==================
    $update = $conn->prepare("
      UPDATE productos 
      SET ESTADO = 0 
      WHERE PRODUCTO_ID = ?
    ");
    $update->bind_param("i", $id);

    if (!$update->execute()) {
      throw new Exception("Error al actualizar producto");
    }

    // ================== INSERTAR MOVIMIENTO ==================
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
      throw new Exception("Error al registrar movimiento");
    }

    // ================== TODO OK ==================
    $conn->commit();

    $_SESSION['mensajeProducto'] = "Producto eliminado correctamente";
    $_SESSION['tipoProducto'] = "success";

  } catch (Exception $e) {

    // ================== ERROR ==================
    $conn->rollback();

    $_SESSION['mensajeProducto'] = $e->getMessage();
    $_SESSION['tipoProducto'] = "danger";
  }

  header("Location: index.php");
  exit;
?>
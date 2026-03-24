<?php
session_start();
include "conexion.php";
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: index.php");
    exit;
}

#obtenemos el rol desde la sesión
$rol = $_SESSION['ROL'] ?? '';
#validamos si es un admin
if ($rol != "admin" && $rol != "empleado") {
    echo "Acceso denegado. Solo admninistradores";
    exit();
}

$producto_id = ($_GET['PRODUCTO_ID']);
//tomar el id del paciente, seleccionas de la tabla historias donde mi id = el putisimo id
$sql_get_paciente = "SELECT PRODUCTO_ID FROM productos WHERE PRODUCTO_ID = $producto_id";
$result = $conn->query($sql_get_paciente);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $producto_id = $row['PRODUCTO_ID'];
    //variable id es igual a la columna id paciente

    //delete de
    $sql_delete_producto = "DELETE FROM productos WHERE PRODUCTO_ID = $producto_id";
    $conn->query($sql_delete_producto);


    header("Location: inventario.php");
    exit();
} else {
    echo "No se encontró la historia clínica con ID: $id";
}

$conn->close();
?>
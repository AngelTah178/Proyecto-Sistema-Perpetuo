<?php
session_start();
include "conexion.php";

// ================== VALIDAR SESIÓN ==================
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

$rol = $_SESSION['ROL'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <?php include 'include/navbar.php'; ?>
    <br><br><br><br>

</body>

</html>
<?php

session_start();
include "conexion.php";

// ================== VALIDAR SESIÓN ==================
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}


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
    <button class="btn btn-sm btn-warning" onclick="window.location.href='RegistroEntrada.php'">
        stock de producto
    </button>

</body>

</html>
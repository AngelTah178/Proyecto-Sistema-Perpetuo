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

$query = $conn->prepare("SELECT * FROM productos");
$query->execute();
$result = $query->get_result();
$productos = $result->fetch_all(MYSQLI_ASSOC);
?>
<html>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>

<body>
    <?php include 'include/navbar.php'; ?>
    <!--Primera parte del CRUD, mostrar productos del inventario-->
    <br><br><br><br><br>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>PRODUCTO_ID</th>
                <th>CODIGO_BARRAS</th>
                <th>SKU</th>
                <th>NOMBRE</th>
                <th>DESCRIPCION</th>
                <th>PRECIO</th>
                <th>FECHA_REGISTRO</th>
                <th>LOTE_ID</th>
                <th>MARCA_ID</th>
                <th>CATEGORIA_ID</th>
                <th>PROVEEDOR_ID</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($productos as $p): ?>
                <tr>
                    <td>
                        <?php echo $p['PRODUCTO_ID']; ?>
                    </td>
                    <td>
                        <?php echo $p['CODIGO_BARRAS']; ?>
                    </td>
                    <td>
                        <?php echo $p['SKU']; ?>
                    </td>
                    <td>
                        <?php echo $p['NOMBRE']; ?>
                    </td>
                    <td>
                        <?php echo $p['DESCRIPCION']; ?>
                    </td>
                    <td>
                        <?php echo $p['PRECIO']; ?>
                    </td>
                    <td>
                        <?php echo $p['FECHA_REGISTRO']; ?>
                    </td>
                    <td>
                        <?php echo $p['LOTE_ID']; ?>
                    </td>
                    <td>
                        <?php echo $p['MARCA_ID']; ?>
                    </td>
                    <td>
                        <?php echo $p['CATEGORIA_ID']; ?>
                    </td>
                    <td>
                        <?php echo $p['PROVEEDOR_ID']; ?>
                    </td>
                    <td>
                        <button class="btn btn-danger" onclick="window.location.href='empleado.php'">Consultar</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'include/navbar.php'; ?>
    <!--Primera parte del CRUD, mostrar productos del inventario-->
    <br><br><br><br><br>

    <div class="contenedorNuevo">
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalProducto">
            Agregar producto
        </button>
    </div>

    <!--Este es un modal que nos ayudará a pasarle datos al php-->
    <div class="modal fade" id="modalProducto" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <form action="AgregarProducto.php" method="POST">

                    <div class="modal-header">
                        <h5 class="modal-title">Agregar producto</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">

                        <div class="mb-3">
                            <label style="color:black;">Código de barras</label>
                            <input type="text" name="CODIGO_BARRAS" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label style="color:black;">SKU</label>
                            <input type="text" name="SKU" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label style="color:black;">Nombre</label>
                            <input type="text" name="NOMBRE" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label style="color:black;">Descripción</label>
                            <input type="text" name="DESCRIPCION" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label style="color:black;">Precio</label>
                            <input type="number" name="PRECIO" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label style="color:black;">Fecha de registro</label>
                            <input type="date" name="FECHA_REGISTRO" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label style="color:black;">Lote</label>
                            <input type="text" name="LOTE" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label style="color:black;">Marca</label>
                            <input type="text" name="MARCA" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label style="color:black;">Categoría</label>
                            <input type="text" name="CATEGORIA" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label style="color:black;">Proveedor</label>
                            <input type="text" name="PROVEEDOR" class="form-control">
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    <!--Fin del modal-->
    <div class="contenedor_Buscador">
        <form method="GET" class="form-buscador">
            <input type="text" name="buscar" placeholder="Buscar">
            <button type="submit">Buscar</button>
        </form>
    </div>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>CODIGO_BARRAS</th>
                <th>SKU</th>
                <th>NOMBRE</th>
                <th>DESCRIPCION</th>
                <th>PRECIO</th>
                <th>FECHA_REGISTRO</th>
                <th>LOTE</th>
                <th>MARCA</th>
                <th>CATEGORIA</th>
                <th>PROVEEDOR</th>
                <th>Acciones</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($productos as $p): ?>
                <tr>
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
                        <?php echo $p['LOTE']; ?>
                    </td>
                    <td>
                        <?php echo $p['MARCA']; ?>
                    </td>
                    <td>
                        <?php echo $p['CATEGORIA']; ?>
                    </td>
                    <td>
                        <?php echo $p['PROVEEDOR']; ?>
                    </td>

                    <td>
                        <button class="btn btn-secondary">Editar</button>
                        <button class="btn btn-danger">Eliminar</button>
                    </td>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>
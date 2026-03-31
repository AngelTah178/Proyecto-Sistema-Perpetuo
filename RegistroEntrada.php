<?php
session_start();
include "conexion.php";

// ================== VALIDAR SESIÓN ==================
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

#INICIO DE INSERT DE DATOS
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $producto_id = $_POST['PRODUCTO_ID'];
    $almacen_id = $_POST['ALMACEN_ID'];
    $unidades = $_POST['UNIDADES'];
    $fecha = $_POST['FECHA_REGISTRO'];

    $stmt = $conn->prepare("
        INSERT INTO stock (PRODUCTO_ID, ALMACEN_ID, UNIDADES, FECHA_REGISTRO)
        VALUES (?, ?, ?, ?)
    ");

    $stmt->bind_param("iiis", $producto_id, $almacen_id, $unidades, $fecha);

    if ($stmt->execute()) {
        echo "Stock registrado correctamente";
        header("Location: Stock.php");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Panel Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="CSS/index.css">
</head>

<body>

    <?php include 'include/navbar.php'; ?>
    <form method="POST">

        <!-- HEADER -->
        <div class="modal-header">
            <h5 class="modal-title">
                Agregar producto
            </h5>
        </div>

        <!-- BODY -->
        <div class="modal-body">

            <div class="row">

                <?php $productos = $conn->query("SELECT PRODUCTO_ID, NOMBRE FROM productos"); ?>

                <select name="PRODUCTO_ID" class="form-control" required>
                    <option value="">Selecciona producto</option>
                    <?php while ($p = $productos->fetch_assoc()): ?>
                        <option value="<?= $p['PRODUCTO_ID'] ?>">
                            <?= $p['NOMBRE'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Unidades</label>
                    <input type="text" name="UNIDADES" class="form-control input-pro">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Almacen</label>
                    <?php $almacenes = $conn->query("SELECT ALMACEN_ID, ALMACEN FROM almacenes"); ?>

                    <select name="ALMACEN_ID" class="form-control" required>
                        <option value="">Selecciona almacén</option>
                        <?php while ($a = $almacenes->fetch_assoc()): ?>
                            <option value="<?= $a['ALMACEN_ID'] ?>">
                                <?= $a['ALMACEN'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fecha de registro</label>
                    <input type="date" name="FECHA_REGISTRO" class="form-control input-pro" required>
                </div>


            </div>

            <div class="modal-footer">
                <button type="submit" class="btn ms-2 btn-success"
                    style="border-radius:10px; padding:8px 20px; font-weight:600;">
                    Guardar
                </button>
                <button class="btn btn-warning" onclick="window.location.href='Stock.php'">
                    Cancelar </button>
            </div>

    </form>

</body>

</html>
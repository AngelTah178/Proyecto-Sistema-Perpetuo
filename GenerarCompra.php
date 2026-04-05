<?php
session_start();
include "conexion.php";

if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

if (!isset($_SESSION['orden'])) {
    $_SESSION['orden'] = [];
}

if (isset($_POST['agregar'])) {
    $producto_id = $_POST['PRODUCTO_ID'];
    $proveedor_id = $_POST['PROVEEDOR_ID'];
    $almacen_id = $_POST['ALMACEN_ID'];
    $unidades = $_POST['UNIDADES'];

    $_SESSION['proveedor_id'] = $proveedor_id;
    $_SESSION['almacen_id'] = $almacen_id;

    $stmt = $conn->prepare("SELECT PRODUCTO_ID, NOMBRE, PRECIO FROM productos WHERE PRODUCTO_ID = ?");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $producto = $result->fetch_assoc();

    $total = $producto['PRECIO'] * $unidades;

    $_SESSION['orden'][] = [
        'producto_id' => $producto['PRODUCTO_ID'],
        'producto' => $producto['NOMBRE'],
        'precio' => $producto['PRECIO'],
        'unidades' => $unidades,
        'total' => $total
    ];

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_GET['limpiar'])) {
    unset($_SESSION['orden']);
    unset($_SESSION['proveedor_id']);
    unset($_SESSION['almacen_id']);
    header("Location: GenerarCompra.php");
    exit;
}

if (isset($_GET['eliminar'])) {
    $index = $_GET['eliminar'];

    if (isset($_SESSION['orden'][$index])) {
        unset($_SESSION['orden'][$index]);
        $_SESSION['orden'] = array_values($_SESSION['orden']);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

if (isset($_POST['confirmar'])) {
    if (empty($_SESSION['orden'])) {
        die("No hay productos en la orden");
    }

    if (!isset($_SESSION['proveedor_id']) || !isset($_SESSION['almacen_id'])) {
        die("Faltan datos de proveedor o almacén");
    }

    $proveedor_id = $_SESSION['proveedor_id'];
    $almacen_id = $_SESSION['almacen_id'];

    $conn->begin_transaction();
    try {
        foreach ($_SESSION['orden'] as $item) {
            $producto_id = $item['producto_id'];
            $unidades = $item['unidades'];
            $fecha = date('Y-m-d H:i:s');
            $usuario = $_SESSION['ID_USUARIO'];

            $stockStmt = $conn->prepare(
                "SELECT STOCK_ID, UNIDADES FROM stock WHERE PRODUCTO_ID = ?"
            );
            $stockStmt->bind_param("i", $producto_id);
            $stockStmt->execute();
            $stockResult = $stockStmt->get_result();

            if ($stockResult->num_rows == 0) {
                throw new Exception("No hay stock para el producto: " . $item['producto']);
            }

            $stock = $stockResult->fetch_assoc();

            if ($stock['UNIDADES'] < $unidades) {
                throw new Exception("No hay suficiente stock para el producto: " . $item['producto']);
            }

            $nuevo_stock = $stock['UNIDADES'] - $unidades;

            $updateStock = $conn->prepare(
                "UPDATE stock SET UNIDADES = ?, FECHA_REGISTRO = ? WHERE STOCK_ID = ?"
            );
            $updateStock->bind_param("isi", $nuevo_stock, $fecha, $stock['STOCK_ID']);
            $updateStock->execute();

            $movimientos = $conn->prepare(
                "INSERT INTO movimientos
                (FECHA_REGISTRO, CANTIDAD, TIPO_ID, ID_USUARIO, PROVEEDOR_ID, PRODUCTO_ID, ALMACEN_ID)
                VALUES (?,?,?,?,?,?,?)"
            );
            $tipo = 2;
            $movimientos->bind_param("siiiiii", $fecha, $unidades, $tipo, $usuario, $proveedor_id, $producto_id, $almacen_id);
            $movimientos->execute();
        }

        $conn->commit();
        unset($_SESSION['orden']);
        unset($_SESSION['proveedor_id']);
        unset($_SESSION['almacen_id']);
        header("location: GenerarCompra.php?ok=1");
        exit;
    } catch (Exception $e) {
        $conn->rollback();
        die("Error al procesar la orden: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charse t="UTF-8">
    <met a name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
</head>

<body>
    <?php include 'include/navbar.php'; ?>
    <br><br><br><br>
    <div class="mb-4">
        <p class="text-muted">Crear Nueva Orden de Compra</p>
        <form method="POST">
            <?php $proveedores = $conn->query("SELECT PROVEEDOR_ID, NOMBRE FROM proveedores"); ?>
            <div class="mb-3">
                <label>Proveedor</label>
                <select name="PROVEEDOR_ID" id="proveedor" class="form-control" required>
                    <option value="">Selecciona proveedor</option>
                    <?php while ($pr = $proveedores->fetch_assoc()): ?>
                        <option value="<?= $pr['PROVEEDOR_ID'] ?>">
                            <?= $pr['NOMBRE'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <?php $almacenes = $conn->query("SELECT ALMACEN_ID, ALMACEN FROM almacenes"); ?>
                <div class="mb-3">
                    <label>Almacén</label>
                    <select name="ALMACEN_ID" id="almacen" class="form-control" required>
                        <option value="">Selecciona almacén</option>
                        <?php while ($al = $almacenes->fetch_assoc()): ?>
                            <option value="<?= $al['ALMACEN_ID'] ?>">
                                <?= $al['ALMACEN'] ?>
                            </option>
                        <?php endwhile; ?>
                    </select>


                </div>
                <br>


                <p class="text-muted">Productos</p>
                <select name="PRODUCTO_ID" id="producto" class="form-control" required disabled>
                    <option value="">Selecciona primero un proveedor</option>
                </select>

                <div class="mb-3">
                    <label>Unidades</label>
                    <input type="number" name="UNIDADES" class="form-control" required>
                </div>

                <button type="submit" name="agregar" class="btn btn-success">
                    Agregar
                </button>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th> </th>
                            <th>Cantidad</th>
                            <th>Producto</th>
                            <th>Precio Unitario</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php if (!empty($_SESSION['orden'])): ?>

                            <?php foreach ($_SESSION['orden'] as $index => $item): ?>
                                <tr>
                                    <td>
                                        <a href="?eliminar=<?= $index ?>" class="btn btn-danger btn-sm">
                                            X
                                        </a>

                                    </td>
                                    <td>
                                        <?= $item['unidades'] ?>
                                    </td>
                                    <td>
                                        <?= $item['producto'] ?>
                                    </td>
                                    <td>$
                                        <?= $item['precio'] ?>
                                    </td>
                                    <td>$
                                        <?= $item['total'] ?>
                                    </td>
                                </tr>

                            <?php endforeach; ?>
                            <?php foreach ($_SESSION['orden'] as $item)
                                $totalFinal += $item['total']; ?>
                            <tr>
                                <td colspan="4" class="text-end fw-bold">
                                    Precio Final:
                                </td>
                                <td class="fw-bold">$
                                    <?= $totalFinal ?>
                            </tr>
                        <?php endif; ?>

                    </tbody>
                </table>

                <a href="?limpiar=1" class="btn btn-danger">Limpiar orden</a>
                <button type="submit" name="confirmar" class="btn btn-primary" formnovalidate>
                    Confirmar compra
                </button>

                <button class="btn btn-success" onclick="window.location.href='index.php'">Salir</button>
        </form>



    </div>

    <!--SCRIPT PARA VALIDAR SI SE SELECCIONIÓ UN PROVEEDOR--->
    <script>
        document.addEventListener("DOMContentLoaded", function () {

            const proveedor = document.getElementById("proveedor");
            const almacen = document.getElementById("almacen");
            const producto = document.getElementById("producto");

            function cargarProductos() {

                let proveedorId = proveedor.value;
                let almacenId = almacen.value;

                producto.innerHTML = '<option>Cargando...</option>';
                producto.disabled = true;

                if (proveedorId === "" || almacenId === "") {
                    producto.innerHTML = '<option>Selecciona proveedor y almacén</option>';
                    return;
                }

                fetch(`obtener_productos.php?proveedor_id=${proveedorId}&almacen_id=${almacenId}`)
                    .then(res => res.text())
                    .then(data => {
                        producto.innerHTML = data;
                        producto.disabled = false;
                    });
            }

            proveedor.addEventListener("change", cargarProductos);
            almacen.addEventListener("change", cargarProductos);

        });
    </script>
    <!---FIN DE VALIDAR SI SE SELECCIONÓ UN PROVEEDOR-->
</body>

</html>
<?php
session_start();
include "conexion.php";

// ================== VALIDAR SESIÓN ==================
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}
//ESTO ES LO QUE VA A JALAR, PRODUTO_ID Y CON ESO LAS CANTIDADES, A TRVÉS DEL JOIN
//EL PROVEEDOR.
//ESTO LO INSERTA EN MOVIMIENTOS EN ESTE CASO ES SALIDA DE PRODUCTO
//DEBE CALCULAR EL TOTAL DE LA COMPRA, PARA ESO SE HACE UN JOIN CON PRODUCTOS Y SE MULTIPLICA EL PRECIO POR LAS UNIDADES
$rol = $_SESSION['ROL'];

#==== AGREGADO ====

if (!isset($_SESSION['orden'])) {
    $_SESSION['orden'] = []; //cada orden es un array y se guarda en la sesión
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Procesar la adición de productos a la orden
    $producto_id = $_POST['PRODUCTO_ID'];
    $proveedor_id = $_POST['PROVEEDOR_ID'];
    $unidades = $_POST['UNIDADES'];

    //obtenemos datos del producto
    $stmt = $conn->prepare("SELECT NOMBRE, PRECIO FROM productos WHERE PRODUCTO_ID = ?");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $producto = $result->fetch_assoc();

    $total = $producto['PRECIO'] * $unidades;
    $totalFinal = 0; //variable para almacenar el total final de la orden, se va sumando cada vez que se agrega un producto
    //se guarda en session
    $_SESSION['orden'][] = [
        'producto' => $producto['NOMBRE'],
        'precio' => $producto['PRECIO'],
        'unidades' => $unidades,
        'total' => $total
    ];
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;

}
//====0fin agregado===
#======0limpiar orden ======
if (isset($_GET['limpiar'])) {
    unset($_SESSION['orden']);
    header("Location: GenerarCompra.php");
    exit;
    #=====fin limpiar orden =====0

}

#======eliminar orden ====
if (isset($_GET['eliminar'])) {
    $index = $_GET['eliminar'];

    if (isset($_SESSION['orden'][$index])) {
        unset($_SESSION['orden'][$index]);

        // Reordenar array (IMPORTANTE)
        $_SESSION['orden'] = array_values($_SESSION['orden']);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
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
    <div class="mb-4">
        <p class="text-muted">Crear Nueva Orden de Compra</p>
        <form method="POST">
            <?php $proveedores = $conn->query("SELECT PROVEEDOR_ID, NOMBRE FROM proveedores"); ?>
            <div class="mb-3">
                <label>Proveedor</label>
                <select name="PROVEEDOR_ID" class="form-control" required>
                    <option value="">Selecciona proveedor</option>
                    <?php while ($pr = $proveedores->fetch_assoc()): ?>
                        <option value="<?= $pr['PROVEEDOR_ID'] ?>">
                            <?= $pr['NOMBRE'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <br>

            <p class="text-muted">Productos</p>
            <?php $productos = $conn->query("SELECT PRODUCTO_ID, NOMBRE FROM productos"); ?>
            <div class="mb-3">
                <label>Producto</label>
                <select name="PRODUCTO_ID" class="form-control" required>
                    <option value="">Selecciona producto</option>
                    <?php while ($pr = $productos->fetch_assoc()): ?>
                        <option value="<?= $pr['PRODUCTO_ID'] ?>">
                            <?= $pr['NOMBRE'] ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label>Unidades</label>
                <input type="number" name="UNIDADES" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-success">Agregar</button>

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

        </form>



    </div>
</body>

</html>
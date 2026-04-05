<?php

session_start();
include "conexion.php";

// ================== VALIDAR SESIÓN ==================
if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
}

#creamos la variable stock para almacenar la consulta
//============== JOIN PARA OBTENER EL STOCK  ====================
$stock = $conn->query(
    "SELECT
    s.STOCK_ID,
    s.UNIDADES,
    s.FECHA_REGISTRO,
    p.NOMBRE AS PRODUCTO,
    a.ALMACEN,
    pr.NOMBRE AS PROVEEDOR
FROM stock s

LEFT JOIN productos p 
    ON s.PRODUCTO_ID = p.PRODUCTO_ID

LEFT JOIN almacenes a 
    ON s.ALMACEN_ID = a.ALMACEN_ID

LEFT JOIN movimientos m 
    ON m.ID_MOVIMIENTO = (
        SELECT MAX(m2.ID_MOVIMIENTO)
        FROM movimientos m2
        WHERE m2.PRODUCTO_ID = s.PRODUCTO_ID
        AND m2.ALMACEN_ID = s.ALMACEN_ID
        AND m2.TIPO_ID = 1
    )

LEFT JOIN proveedores pr 
    ON m.PROVEEDOR_ID = pr.PROVEEDOR_ID"
)->fetch_all(MYSQLI_ASSOC);
#se hizo un left join para acceder a la tabla productos y asignar p como un marcador para productos
#lo mismo para almacenes, esto nos permite acceder a los campos del almacen

//============= FIN JOIN PARA OBTENER STOCK ================

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // VALIDACIONES
    if (
        !isset($_POST['PRODUCTO_ID'], $_POST['ALMACEN_ID'], $_POST['PROVEEDOR_ID'], $_POST['UNIDADES']) ||
        $_POST['PRODUCTO_ID'] == "" ||
        $_POST['ALMACEN_ID'] == "" ||
        $_POST['PROVEEDOR_ID'] == "" ||
        $_POST['UNIDADES'] == ""
    ) {
        die("Todos los campos son obligatorios");
    }

    $producto_id = $_POST['PRODUCTO_ID'];
    $almacen_id = $_POST['ALMACEN_ID'];
    $proveedor_id = $_POST['PROVEEDOR_ID'];
    $unidades = $_POST['UNIDADES'];
    $ubicacion_id = $conn->insert_id;
    $fecha = date('Y-m-d H:i:s');
    $id_usuario = $_SESSION['ID_USUARIO'];

    $ubicacion_id = null;


    if (
        !empty($_POST['PASILLO']) &&
        !empty($_POST['SECCION']) &&
        !empty($_POST['NIVEL']) &&
        !empty($_POST['ESTANTE'])
    ) {
        $stmt = $conn->prepare(
            "INSERT INTO ubicaciones (PASILLO, SECCION, NIVEL, ESTANTE, ALMACEN_ID)
             VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssssi",
            $_POST['PASILLO'],
            $_POST['SECCION'],
            $_POST['NIVEL'],
            $_POST['ESTANTE'],
            $almacen_id // ✅ YA TIENE VALOR
        );

        $stmt->execute();

        $ubicacion_id = $conn->insert_id;
    }

    $conn->begin_transaction();
    try {

        // ================== VERIFICAR STOCK ==================
        $stmt = $conn->prepare(
            "SELECT STOCK_ID, UNIDADES 
             FROM stock 
             WHERE PRODUCTO_ID = ? AND ALMACEN_ID = ?"
        );
        $stmt->bind_param("ii", $producto_id, $almacen_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $nuevo_stock = $row['UNIDADES'] + $unidades;

            $update = $conn->prepare(
                "UPDATE stock 
                 SET UNIDADES = ?, FECHA_REGISTRO = ? 
                 WHERE STOCK_ID = ?"
            );
            $update->bind_param("isi", $nuevo_stock, $fecha, $row['STOCK_ID']);
            $update->execute();

        } else {

            $insert = $conn->prepare(
                "INSERT INTO stock 
    (PRODUCTO_ID, ALMACEN_ID, UNIDADES, FECHA_REGISTRO, UBICACION_ID)
    VALUES (?, ?, ?, ?, ?)"
            );

            $insert->bind_param(
                "iiisi",
                $producto_id,
                $almacen_id,
                $unidades,
                $fecha,
                $ubicacion_id
            );

            $insert->execute();
        }

        // ================== INSERTAR MOVIMIENTO ==================
        $movimientos = $conn->prepare(
            "INSERT INTO movimientos 
            (FECHA_REGISTRO, CANTIDAD, TIPO_ID, ID_USUARIO, PROVEEDOR_ID, PRODUCTO_ID, ALMACEN_ID)
            VALUES (?, ?, 1, ?, ?, ?, ?)"
        );

        $movimientos->bind_param(
            "siiiii",
            $fecha,
            $unidades,
            $id_usuario,
            $proveedor_id,
            $producto_id,
            $almacen_id
        );

        $movimientos->execute();

        $conn->commit();

        header("Location: Stock.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
    //==================== FIN INSERT ==================
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
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalExistencias">
        Registrar existencias
    </button>

    <button class="btn btn-danger" onclick="window.location.href='index.php'">
        Salir
    </button>
    <!--Modal de registro de usuarios-->
    <div class="modal fade" id="modalExistencias" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-existencias">

                <form method="POST">
                    <input type="hidden" name="form_existencas" value="1">

                    <h4>Registrar Entrada</h4>

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
                    </div>

                    <div class="mb-3">
                        <label>Producto</label>
                        <select name="PRODUCTO_ID" id="producto" class="form-control" required disabled>
                            <option value="">Selecciona primero un proveedor</option>
                        </select>
                    </div>


                    <div class="mb-3">
                        <label>Unidades</label>
                        <input type="number" name="UNIDADES" class="form-control" required>
                    </div>

                    <?php $almacenes = $conn->query("SELECT ALMACEN_ID, ALMACEN FROM almacenes"); ?>
                    <div class="mb-3">
                        <label>Almacén</label>
                        <select name="ALMACEN_ID" class="form-control" required>
                            <option value="">Selecciona almacén</option>
                            <?php while ($a = $almacenes->fetch_assoc()): ?>
                                <option value="<?= $a['ALMACEN_ID'] ?>">
                                    <?= $a['ALMACEN'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <!----INICIO UBICACIONES---->
                    <hr>
                    <h5>Registrar nueva ubicación</h5>

                    <div class="mb-2">
                        <input type="text" name="PASILLO" class="form-control" placeholder="Pasillo" required>
                    </div>

                    <div class="mb-2">
                        <input type="text" name="SECCION" class="form-control" placeholder="Sección" required>
                    </div>

                    <div class="mb-2">
                        <input type="text" name="NIVEL" class="form-control" placeholder="Nivel" required>
                    </div>

                    <div class="mb-2">
                        <input type="text" name="ESTANTE" class="form-control" placeholder="Estante" required>
                    </div>

                    <input type="hidden" name="crear_ubicacion" value="1">
                    <!----FIN UBICACIONES---->

                    <div class="mb-3">
                        <label>Fecha</label>
                        <input type="date" name="FECHA_REGISTRO" class="form-control" required>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn ms-2 btn-success"
                            style="border-radius:10px; padding:8px 20px; font-weight:600;">
                            Guardar
                        </button>
                        <button type="button" class="btn ms-2 btn-danger"
                            style="border-radius:10px; padding:8px 20px; font-weight:600;" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>


    <!--ESTO ES EN CASO DE QUE NO HAYAN PRODCUTOS-->
    <?php if (count($stock) > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Producto</th>
                    <th>Almacén</th>
                    <th>Proveedor</th>
                    <th>Unidades</th>
                    <th>Fecha de Registro</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stock as $item): ?>
                    <tr>
                        <td><?= $item['STOCK_ID'] ?></td>
                        <td><?= $item['PRODUCTO'] ?></td>
                        <td><?= $item['ALMACEN'] ?></td>
                        <td>
                            <?= $item['PROVEEDOR'] ?? 'N/A' ?>
                        </td>
                        <td><?= $item['UNIDADES'] ?></td>

                        <td><?= date('d/m/Y H:i:s', strtotime($item['FECHA_REGISTRO'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>NO HAY REGISTROS</p>
    <?php endif; ?>

    <!--SCRIPT PARA VALIDAR SI SE SELECCIONIÓ UN PROVEEDOR--->
    <script>
        const proveedor = document.getElementById("proveedor");
        const producto = document.getElementById("producto");

        proveedor.addEventListener("change", function () {
            let proveedorId = this.value;

            // Reset
            producto.innerHTML = '<option value="">Cargando...</option>';
            producto.disabled = true;

            if (proveedorId === "") {
                producto.innerHTML = '<option value="">Selecciona primero un proveedor</option>';
                return;
            }

            fetch("obtenerProductosStock.php?proveedor_id=" + proveedorId)
                .then(response => response.text())
                .then(data => {
                    producto.innerHTML = data;
                    producto.disabled = false;
                });
        });
    </script>
    <!---FIN DE VALIDAR SI SE SELECCIONÓ UN PROVEEDOR-->
</body>

</html>
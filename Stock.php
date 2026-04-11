<?php
function guardarStock($conn)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['form_existencas'])) {
        return;
    }

    // ================== VALIDACIÓN ==================
    if (
        empty($_POST['PRODUCTO_ID']) ||
        empty($_POST['ALMACEN_ID']) ||
        empty($_POST['PROVEEDOR_ID']) ||
        empty($_POST['UNIDADES']) ||
        empty($_POST['UBICACION_ID'])
    ) {
        throw new Exception("Todos los campos son obligatorios");
    }

    $producto_id = $_POST['PRODUCTO_ID'];
    $almacen_id = $_POST['ALMACEN_ID'];
    $proveedor_id = $_POST['PROVEEDOR_ID'];
    $ubicacion_id = $_POST['UBICACION_ID'];
    $unidades = $_POST['UNIDADES'];

    date_default_timezone_set("America/Cancun");
    $FECHA_REGISTRO = date("Y-m-d H:i:s");

    $id_usuario = $_SESSION['ID_USUARIO'];

    $conn->begin_transaction();

    try {

        // ================== VERIFICAR STOCK EXISTENTE ==================
        $stmt = $conn->prepare(
            "SELECT STOCK_ID, UNIDADES 
                FROM stock 
                WHERE PRODUCTO_ID = ? 
                AND ALMACEN_ID = ? 
                AND UBICACION_ID = ?"
        );

        $stmt->bind_param("iii", $producto_id, $almacen_id, $ubicacion_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            // 
            $row = $result->fetch_assoc();
            $nuevo_stock = $row['UNIDADES'] + $unidades;

            $update = $conn->prepare(
                "UPDATE stock 
                    SET UNIDADES = ?, FECHA_REGISTRO = ?
                    WHERE STOCK_ID = ?"
            );

            $update->bind_param("isi", $nuevo_stock, $FECHA_REGISTRO, $row['STOCK_ID']);
            $update->execute();


        } else {

            // 🆕 NO EXISTE → INSERTAR
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
                $FECHA_REGISTRO,
                $ubicacion_id
            );

            $insert->execute();
        }

        // ================== REGISTRAR MOVIMIENTO ==================
        $mov = $conn->prepare(
            "INSERT INTO movimientos 
                (FECHA_REGISTRO, CANTIDAD, TIPO_ID, ID_USUARIO, PROVEEDOR_ID, PRODUCTO_ID, ALMACEN_ID)
                VALUES (?, ?, 1, ?, ?, ?, ?)"
        );

        $mov->bind_param(
            "siiiii",
            $FECHA_REGISTRO,
            $unidades,
            $id_usuario,
            $proveedor_id,
            $producto_id,
            $almacen_id
        );

        $mov->execute();
        $stmtNombre = $conn->prepare("SELECT NOMBRE FROM productos WHERE PRODUCTO_ID = ?");
        $stmtNombre->bind_param("i", $producto_id);
        $stmtNombre->execute();
        $resNombre = $stmtNombre->get_result();
        $producto = $resNombre->fetch_assoc();
        $conn->commit();

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}

?>
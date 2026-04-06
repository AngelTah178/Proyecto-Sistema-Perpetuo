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
        empty($_POST['UNIDADES'])
    ) {
        throw new Exception("Todos los campos son obligatorios");
    }

    $producto_id = $_POST['PRODUCTO_ID'];
    $almacen_id = $_POST['ALMACEN_ID'];
    $proveedor_id = $_POST['PROVEEDOR_ID'];
    $unidades = $_POST['UNIDADES'];
    $fecha = date('Y-m-d H:i:s');
    $id_usuario = $_SESSION['ID_USUARIO'];

    $conn->begin_transaction();

    try {

        // ================== UBICACIÓN ==================
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
                $almacen_id
            );

            $stmt->execute();
            $ubicacion_id = $conn->insert_id;
        }

        // ================== STOCK ==================
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
                 SET UNIDADES = ?, FECHA_REGISTRO = ?, UBICACION_ID = ?
                 WHERE STOCK_ID = ?"
            );

            $update->bind_param("isii", $nuevo_stock, $fecha, $ubicacion_id, $row['STOCK_ID']);
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

        // ================== MOVIMIENTOS ==================
        $mov = $conn->prepare(
            "INSERT INTO movimientos 
            (FECHA_REGISTRO, CANTIDAD, TIPO_ID, ID_USUARIO, PROVEEDOR_ID, PRODUCTO_ID, ALMACEN_ID)
            VALUES (?, ?, 1, ?, ?, ?, ?)"
        );

        $mov->bind_param(
            "siiiii",
            $fecha,
            $unidades,
            $id_usuario,
            $proveedor_id,
            $producto_id,
            $almacen_id
        );

        $mov->execute();

        $conn->commit();

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }
}
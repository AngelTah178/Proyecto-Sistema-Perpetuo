<?php
  session_start();
  include "conexion.php";

  date_default_timezone_set('America/Cancun');

  if (!isset($_SESSION['logueado']) || $_SESSION['logueado'] !== true) {
    header("Location: login.php");
    exit;
  }

  if (!isset($_SESSION['orden'])) {
    $_SESSION['orden'] = [];
  }

  /* AGREGAR A ORDEN */
  if (isset($_POST['agregar'])) {

    $producto_id = $_POST['PRODUCTO_ID'];
    $proveedor_id = $_POST['PROVEEDOR_ID'];
    $almacen_id = $_POST['ALMACEN_ID'];
    $unidades = $_POST['UNIDADES'];

    $_SESSION['proveedor_id'] = $proveedor_id;
    $_SESSION['almacen_id'] = $almacen_id;

    // 🔥 VALIDAR PRODUCTO ACTIVO (IMPORTANTE)
    $check = $conn->prepare("
      SELECT PRODUCTO_ID, NOMBRE, PRECIO
      FROM productos
      WHERE PRODUCTO_ID = ?
      AND ESTADO = 1
    ");

    $check->bind_param("i", $producto_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows === 0) {
      $_SESSION['mensaje'] = "Producto eliminado o inactivo";
      $_SESSION['tipo'] = "danger";
      header("Location: GenerarCompra.php");
      exit;
    }

    $producto = $res->fetch_assoc();

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

  /* LIMPIAR ORDEN */
  if (isset($_GET['limpiar'])) {
    unset($_SESSION['orden']);
    unset($_SESSION['proveedor_id']);
    unset($_SESSION['almacen_id']);
    header("Location: GenerarCompra.php");
    exit;
  }

  /* ELIMINAR ITEM */
  if (isset($_GET['eliminar'])) {
    $index = $_GET['eliminar'];

    if (isset($_SESSION['orden'][$index])) {
      unset($_SESSION['orden'][$index]);
      $_SESSION['orden'] = array_values($_SESSION['orden']);
    }

    header("Location: GenerarCompra.php");
    exit;
  }

  /* CONFIRMAR VENTA */
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

        // VALIDAR PRODUCTO ACTIVO (CRÍTICO)
        $checkProducto = $conn->prepare("
          SELECT PRODUCTO_ID
          FROM productos
          WHERE PRODUCTO_ID = ?
          AND ESTADO = 1
        ");

        $checkProducto->bind_param("i", $producto_id);
        $checkProducto->execute();
        $r = $checkProducto->get_result();

        if ($r->num_rows === 0) {
          throw new Exception("Producto eliminado dentro de la orden: " . $item['producto']);
        }

        // STOCK
        $stockStmt = $conn->prepare("
          SELECT STOCK_ID, UNIDADES
          FROM stock
          WHERE PRODUCTO_ID = ?
        ");

        $stockStmt->bind_param("i", $producto_id);
        $stockStmt->execute();
        $stockResult = $stockStmt->get_result();

        if ($stockResult->num_rows == 0) {
          throw new Exception("No hay stock para: " . $item['producto']);
        }

        $stock = $stockResult->fetch_assoc();

        if ($stock['UNIDADES'] < $unidades) {
          throw new Exception("Stock insuficiente para: " . $item['producto']);
        }

        $nuevo_stock = $stock['UNIDADES'] - $unidades;

        $updateStock = $conn->prepare("
          UPDATE stock
          SET UNIDADES = ?, FECHA_REGISTRO = ?
          WHERE STOCK_ID = ?
        ");

        $updateStock->bind_param("isi", $nuevo_stock, $fecha, $stock['STOCK_ID']);
        $updateStock->execute();

        $tipo = 2;

        $mov = $conn->prepare("
          INSERT INTO movimientos
          (FECHA_REGISTRO, CANTIDAD, TIPO_ID, ID_USUARIO, PROVEEDOR_ID, PRODUCTO_ID, ALMACEN_ID)
          VALUES (?,?,?,?,?,?,?)
        ");

        $mov->bind_param(
          "siiiiii",
          $fecha,
          $unidades,
          $tipo,
          $usuario,
          $proveedor_id,
          $producto_id,
          $almacen_id
        );

        $mov->execute();
      }

      $conn->commit();

      unset($_SESSION['orden']);
      unset($_SESSION['proveedor_id']);
      unset($_SESSION['almacen_id']);

      $_SESSION['mensaje'] = "Orden generada correctamente";
      $_SESSION['tipo'] = "success";

      header("Location: GenerarCompra.php");
      exit;

    } catch (Exception $e) {

      $conn->rollback();

      $_SESSION['mensaje'] = "Error: " . $e->getMessage();
      $_SESSION['tipo'] = "danger";

      header("Location: GenerarCompra.php");
      exit;
    }
  }
?>

<html lang="en">

<head>
  <meta charse t="UTF-8">
  <met a name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta</title>
    <link rel="stylesheet" href="CSS/generarVenta.css">
</head>

<body>
  <?php include 'include/navbar.php'; ?>

  <div class="container mt-4">

    <!-- TITULO -->
    <div class="mb-4">
      <h2 class="fw-bold">Nueva Orden de Venta</h2>
      <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="alert alert-<?= $_SESSION['tipo']; ?>">
          <?= $_SESSION['mensaje']; ?>
        </div>
        <?php unset($_SESSION['mensaje'], $_SESSION['tipo']); ?>
      <?php endif; ?>
      <p class="text-muted">Agrega productos y genera la venta</p>
    </div>

    <div class="row g-4">

      <!-- FORMULARIO -->
      <div class="col-md-5">
        <div class="card shadow-sm p-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Datos de la orden</h5>
            <button class="btn btn-outline-danger btn-sm" onclick="window.location.href='index.php'">
              Salir
            </button>
          </div>

          <form method="POST">

            <div class="mb-3">
              <label class="form-label">Código de barras</label>
              <input type="text" id="codigo_barras" class="form-control input-pro" placeholder="Escanea o escribe..."
                required>
            </div>

            <div class="mb-3">
              <label class="form-label">Producto</label>
              <input type="text" id="nombre_producto" class="form-control input-pro" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Proveedor</label>
              <input type="text" id="proveedor_nombre" class="form-control input-pro" readonly>
            </div>

            <div class="mb-3">
              <label class="form-label">Almacén</label>
              <select name="ALMACEN_ID" id="almacen" class="form-control input-pro" required>
                <option value="">Escanea un producto primero</option>
              </select>
            </div>

            <!-- OCULTOS -->
            <input type="hidden" name="PRODUCTO_ID" id="producto_id">
            <input type="hidden" name="PROVEEDOR_ID" id="proveedor_id">

            <!-- UNIDADES -->
            <div class="mb-3">
              <label class="form-label">Unidades</label>
              <input type="number" name="UNIDADES" class="form-control input-pro" required>
            </div>

            <button type="submit" name="agregar" class="btn btn-success w-100">
              Agregar a la orden
            </button>

          </form>

        </div>
      </div>

      <!-- TABLA ORDEN -->
      <div class="col-md-7">
        <div class="card shadow-sm p-4">

          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Detalle de la orden</h5>

            <a href="?limpiar=1" class="btn btn-danger btn-sm">
              Limpiar
            </a>
          </div>

          <div class="table-responsive">
            <table class="table table-hover align-middle">

              <thead class="table-dark">
                <tr>
                  <th></th>
                  <th>Cantidad</th>
                  <th>Producto</th>
                  <th>Precio</th>
                  <th>Total</th>
                </tr>
              </thead>

              <tbody>
                <?php $totalFinal = 0; ?>

                <?php if (!empty($_SESSION['orden'])): ?>
                  <?php foreach ($_SESSION['orden'] as $index => $item): ?>
                    <tr>
                      <td>
                        <a href="?eliminar=<?= $index ?>" class="btn btn-sm btn-danger">
                          <i class="bi bi-x"></i>
                        </a>
                      </td>

                      <td><?= $item['unidades'] ?></td>
                      <td><?= $item['producto'] ?></td>
                      <td>$<?= $item['precio'] ?></td>
                      <td>$<?= $item['total'] ?></td>
                    </tr>

                    <?php $totalFinal += $item['total']; ?>
                  <?php endforeach; ?>

                  <tr>
                    <td colspan="4" class="text-end fw-bold">
                      Total:
                    </td>
                    <td class="fw-bold text-success">
                      $<?= $totalFinal ?>
                    </td>
                  </tr>

                <?php else: ?>
                  <tr>
                    <td colspan="5" class="text-center text-muted">
                      No hay productos en la orden
                    </td>
                  </tr>
                <?php endif; ?>

              </tbody>

            </table>
          </div>

          <!---OTRO PENDEJO FORM--->
          <form method="POST">
            <div class="d-flex justify-content-between mt-3">

              <button type="submit" name="confirmar" class="btn btn-primary">
                Confirmar venta
              </button>

              <button type="button" class="btn btn-secondary" onclick="window.location.href='index.php'">
                Regresar
              </button>

            </div>
          </form>

        </div>
      </div>

    </div>

  </div>

  <script>
    //Datos con el codigo de barras
    document.addEventListener("DOMContentLoaded", function () {
      const codigo = document.getElementById("codigo_barras");
      codigo.addEventListener("keyup", function () {

        let valor = this.value.trim();

        if (valor.length < 3) return;

        fetch("buscarProductoCodigo.php?codigo=" + encodeURIComponent(valor))
        .then(res => res.json())
        .then(data => {

          if (!data || data.length === 0) {
            document.getElementById("nombre_producto").value = "";
            document.getElementById("proveedor_nombre").value = "";
            document.getElementById("almacen").innerHTML = '<option>No encontrado</option>';
            return;
          }

          // PRODUCTO Y PROVEEDOR
          document.getElementById("nombre_producto").value = data[0].NOMBRE;
          document.getElementById("proveedor_nombre").value = data[0].PROVEEDOR;

          document.getElementById("producto_id").value = data[0].PRODUCTO_ID;
          document.getElementById("proveedor_id").value = data[0].PROVEEDOR_ID;

          // ALMACENES
          let select = document.getElementById("almacen");
          select.innerHTML = '<option value="">Selecciona almacén</option>';

          data.forEach(item => {
            if (item.ALMACEN_ID) {
              select.innerHTML += `
                <option value="${item.ALMACEN_ID}">
                  ${item.ALMACEN} (Stock: ${item.UNIDADES})
                </option>
              `;
            }
          });

          });

      });
    });
  </script>
</body>

</html>
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
      <p class="text-muted">Agrega productos y genera la venta</p>
    </div>

    <div class="row g-4">

      <!-- FORMULARIO -->
      <div class="col-md-5">
        <div class="card shadow-sm p-4">
          <div class="mb-2">

            <button class="btn btn-danger" onclick="window.location.href='index.php'">Salir</button>

          </div>
          <h5 class="mb-3">Datos de la orden</h5>

          <form method="POST">

            <!-- PROVEEDOR -->
            <?php $proveedores = $conn->query("SELECT PROVEEDOR_ID, NOMBRE FROM proveedores"); ?>
            <div class="mb-3">
              <label class="form-label">Proveedor</label>
              <select name="PROVEEDOR_ID" id="proveedor" class="form-control input-pro" required>
                <option value="">Selecciona proveedor</option>
                <?php while ($pr = $proveedores->fetch_assoc()): ?>
                  <option value="<?= $pr['PROVEEDOR_ID'] ?>">
                    <?= $pr['NOMBRE'] ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <!-- ALMACEN -->
            <?php $almacenes = $conn->query("SELECT ALMACEN_ID, ALMACEN FROM almacenes"); ?>
            <div class="mb-3">
              <label class="form-label">Almacén</label>
              <select name="ALMACEN_ID" id="almacen" class="form-control input-pro" required>
                <option value="">Selecciona almacén</option>
                <?php while ($al = $almacenes->fetch_assoc()): ?>
                  <option value="<?= $al['ALMACEN_ID'] ?>">
                    <?= $al['ALMACEN'] ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>

            <!-- PRODUCTO -->
            <div class="mb-3">
              <label class="form-label">Producto</label>
              <select name="PRODUCTO_ID" id="producto" class="form-control input-pro" required disabled>
                <option value="">Selecciona primero proveedor y almacén</option>
              </select>
            </div>

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
    //VALIDAR SI SE SELECCIONIÓ UN PROVEEDOR
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
</body>

</html>
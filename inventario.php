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

  $query = $conn->query("
  SELECT 
      p.*,
      m.NOMBRE AS MARCA,
      c.NOMBRE AS CATEGORIA,
      pr.NOMBRE AS PROVEEDOR,
      l.LOTE_ID
  FROM productos p
  LEFT JOIN marcas m ON p.MARCA_ID = m.MARCA_ID
  LEFT JOIN categorias c ON p.CATEGORIA_ID = c.CATEGORIA_ID
  LEFT JOIN proveedores pr ON p.PROVEEDOR_ID = pr.PROVEEDOR_ID
  LEFT JOIN lotes l ON p.LOTE_ID = l.LOTE_ID
  ");

  $productos = $query->fetch_all(MYSQLI_ASSOC);

  # LOTES
  $lotes = $conn->query("SELECT LOTE_ID FROM lotes");

  # MARCAS
  $marcas = $conn->query("SELECT MARCA_ID, NOMBRE FROM marcas");

  # CATEGORIAS
  $categorias = $conn->query("SELECT CATEGORIA_ID, NOMBRE FROM categorias");

  # PROVEEDORES
  $proveedores = $conn->query("SELECT PROVEEDOR_ID, NOMBRE FROM proveedores");

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $CODIGO_BARRAS = $_POST["CODIGO_BARRAS"];
    $SKU = $_POST["SKU"];
    $NOMBRE = $_POST["NOMBRE"];
    $DESCRIPCION = $_POST["DESCRIPCION"];
    $PRECIO = $_POST["PRECIO"];
    $FECHA_REGISTRO = $_POST["FECHA_REGISTRO"];
    $LOTE_ID = $_POST["LOTE_ID"];
    $MARCA_ID = $_POST["MARCA_ID"];
    $CATEGORIA_ID = $_POST["CATEGORIA_ID"];
    $PROVEEDOR_ID = $_POST["PROVEEDOR_ID"];

    $stmt = $conn->prepare("INSERT INTO productos (CODIGO_BARRAS, SKU, NOMBRE, DESCRIPCION, PRECIO, FECHA_REGISTRO, LOTE_ID, MARCA_ID, CATEGORIA_ID, PROVEEDOR_ID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssdsiiii", $CODIGO_BARRAS, $SKU, $NOMBRE, $DESCRIPCION, $PRECIO, $FECHA_REGISTRO, $LOTE_ID, $MARCA_ID, $CATEGORIA_ID, $PROVEEDOR_ID);
    if ($stmt->execute()) {
<<<<<<< Updated upstream
      $mensaje = "Producto agregado correctamente";
      exit();
=======
        $mensaje = "Producto agregado correctamente";
        header("Location: inventario.php");
        exit();
>>>>>>> Stashed changes
    } else {
      $mensaje = "Error al agregar producto: " . $query;
    }
  }

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

          <form method="POST">

            <div class="modal-header">
              <h5 class="modal-title">Agregar producto</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

              <div class="mb-3">
                <label style="color:black;">Código de barras</label>
                <input type="text" name="CODIGO_BARRAS" class="form-control" autofocus required>
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
                <select name="LOTE_ID" class="form-control" required>
                  <option value="">Selecciona un lote</option>
                  <?php while ($l = $lotes->fetch_assoc()): ?>
                  <option value="<?= $l['LOTE_ID'] ?>">
                    Lote
                    <?= $l['LOTE_ID'] ?>
                  </option>
                  <?php endwhile; ?>
                </select>
              </div>

              <div class="mb-3">
                <label style="color:black;">Marca</label>
                <select name="MARCA_ID" class="form-control" required>
                  <option value="">Selecciona una marca</option>
                  <?php while ($m = $marcas->fetch_assoc()): ?>
                  <option value="<?= $m['MARCA_ID'] ?>">
                    <?= $m['NOMBRE'] ?>
                  </option>
                  <?php endwhile; ?>
                </select>
              </div>

              <div class="mb-3">
                <label style="color:black;">Categoría</label>
                <select name="CATEGORIA_ID" class="form-control" required>
                  <option value="">Selecciona una categoría</option>
                  <?php while ($c = $categorias->fetch_assoc()): ?>
                  <option value="<?= $c['CATEGORIA_ID'] ?>">
                    <?= $c['NOMBRE'] ?>
                  </option>
                  <?php endwhile; ?>
                </select>
              </div>

              <div class="mb-3">
                <label style="color:black;">Proveedor</label>
                <select name="PROVEEDOR_ID" class="form-control" required>
                  <option value="">Selecciona un proveedor</option>
                  <?php while ($p = $proveedores->fetch_assoc()): ?>
                  <option value="<?= $p['PROVEEDOR_ID'] ?>">
                    <?= $p['NOMBRE'] ?>
                  </option>
                    <?php endwhile; ?>
                  </select>
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

<<<<<<< Updated upstream
          <td>
            <?php echo $p['NOMBRE']; ?>
          </td>
=======
                    <td>
                        <a href="editarProducto.php?id=<?= $p['PRODUCTO_ID'] ?>" class="btn btn-secondary">
                            Editar
                        </a>

                        <a href="EliminarProducto.php?PRODUCTO_ID=<?= $p['PRODUCTO_ID'] ?>" class="btn btn-danger">
                            Eliminar
                        </a>
                    </td>
>>>>>>> Stashed changes

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
            <a href="EliminarProducto.php?PRODUCTO_ID=<?= $p['PRODUCTO_ID'] ?>" class="btn btn-danger">
              Eliminar
            </a>
          </td>

        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    
  </body>
</html>
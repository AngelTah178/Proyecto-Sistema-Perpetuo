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

/* =========================
   ACTUALIZAR PRODUCTO (POST)
========================= */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id = $_POST['PRODUCTO_ID'];
    $nombre = $_POST['NOMBRE'];
    $precio = $_POST['PRECIO'];
    $marca = $_POST['MARCA_ID'];
    $categoria = $_POST['CATEGORIA_ID'];
    $proveedor = $_POST['PROVEEDOR_ID'];
    $lote = $_POST['LOTE_ID'];

    $sql = "UPDATE productos SET 
                NOMBRE = ?, 
                PRECIO = ?, 
                MARCA_ID = ?, 
                CATEGORIA_ID = ?, 
                PROVEEDOR_ID = ?, 
                LOTE_ID = ?
            WHERE PRODUCTO_ID = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sdiiiii",
        $nombre,
        $precio,
        $marca,
        $categoria,
        $proveedor,
        $lote,
        $id
    );

    if ($stmt->execute()) {
        header("Location: inventario.php");
        exit;
    } else {
        echo "Error al actualizar: " . $conn->error;
    }
}


/* =========================

========================= */
if (isset($_GET['id'])) {

    $id = $_GET['id'];

    // 
    $stmt = $conn->prepare("SELECT * FROM productos WHERE PRODUCTO_ID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $producto = $stmt->get_result()->fetch_assoc();

    if (!$producto) {
        echo "Producto no encontrado";
        exit;
    }

    // Catálogo q
    $marcas = $conn->query("SELECT * FROM marcas")->fetch_all(MYSQLI_ASSOC);
    $categorias = $conn->query("SELECT * FROM categorias")->fetch_all(MYSQLI_ASSOC);
    $proveedores = $conn->query("SELECT * FROM proveedores")->fetch_all(MYSQLI_ASSOC);
    $lotes = $conn->query("SELECT * FROM lotes")->fetch_all(MYSQLI_ASSOC);

} else {
    echo "ID no especificado";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
</head>

<body>

    <h1>Editar Producto</h1>

    <form method="POST">

        <input type="hidden" name="PRODUCTO_ID" value="<?php echo $producto['PRODUCTO_ID']; ?>">

        <label>Nombre:</label>
        <input type="text" name="NOMBRE" value="<?php echo $producto['NOMBRE']; ?>">

        <label>Precio:</label>
        <input type="number" step="0.01" name="PRECIO" value="<?php echo $producto['PRECIO']; ?>">

        <label>Marca:</label>
        <select name="MARCA_ID">
            <?php foreach ($marcas as $m): ?>
                <option value="<?php echo $m['MARCA_ID']; ?>" <?php echo ($m['MARCA_ID'] == $producto['MARCA_ID']) ? 'selected' : ''; ?>>
                    <?php echo $m['NOMBRE']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Categoría:</label>
        <select name="CATEGORIA_ID">
            <?php foreach ($categorias as $c): ?>
                <option value="<?php echo $c['CATEGORIA_ID']; ?>" <?php echo ($c['CATEGORIA_ID'] == $producto['CATEGORIA_ID']) ? 'selected' : ''; ?>>
                    <?php echo $c['NOMBRE']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Proveedor:</label>
        <select name="PROVEEDOR_ID">
            <?php foreach ($proveedores as $p): ?>
                <option value="<?php echo $p['PROVEEDOR_ID']; ?>" <?php echo ($p['PROVEEDOR_ID'] == $producto['PROVEEDOR_ID']) ? 'selected' : ''; ?>>
                    <?php echo $p['NOMBRE']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label>Lote:</label>
        <select name="LOTE_ID">
            <?php foreach ($lotes as $l): ?>
                <option value="<?php echo $l['LOTE_ID']; ?>" <?php echo ($l['LOTE_ID'] == $producto['LOTE_ID']) ? 'selected' : ''; ?>>
                    <?php echo $l['LOTE_ID']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <br><br>
        <input type="submit" value="Actualizar">

    </form>

</body>

</html>
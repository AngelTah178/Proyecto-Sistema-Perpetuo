<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "inventario";

#creamos la conexion
$conn = new mysqli($servername, $username, $password, $dbname);
#check the connection
if ($conn->connect_error) {
    die("connection failed: " . $conn->connect_error);
}
echo "connected successfully";
?>
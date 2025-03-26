<?php
session_start();
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
  header("Location: login.php");
  exit;
}

if (!isset($_GET['id'])) {
  header("Location: agregar.php");
  exit;
}

$conn = new mysqli("localhost", "root", "", "cartelerabd");
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$id_pelicula = intval($_GET['id']);

// Eliminar relaciones de reparto primero (si existen)
$conn->query("DELETE FROM pelicula_reparto WHERE id_pelicula = $id_pelicula");

// Luego eliminar la película
$conn->query("DELETE FROM peliculas WHERE id_pelicula = $id_pelicula");

header("Location: agregar.php");
exit;
?>

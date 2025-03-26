<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
  http_response_code(403);
  echo "Debes iniciar sesión para guardar favoritos.";
  exit;
}

$conn = new mysqli("localhost", "root", "", "cartelerabd");
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$id_usuario = $_SESSION['id_usuario'];
$id_pelicula = isset($_POST['id_pelicula']) ? intval($_POST['id_pelicula']) : 0;

if ($id_pelicula > 0) {
  $fecha = date('Y-m-d H:i:s');

  // Verifica si ya existe el favorito
  $verifica = $conn->query("SELECT * FROM favoritos WHERE id_usuario = $id_usuario AND id_pelicula = $id_pelicula");

  if ($verifica->num_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO favoritos (id_usuario, id_pelicula, fecha_guardado) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $id_usuario, $id_pelicula, $fecha);
    $stmt->execute();
    $stmt->close();
    echo "Película guardada en favoritos.";
  } else {
    echo "Esta película ya está en tus favoritos.";
  }
} else {
  echo "Película no válida.";
}
?>

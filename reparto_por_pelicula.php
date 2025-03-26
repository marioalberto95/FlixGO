<?php
$conn = new mysqli("localhost", "root", "", "cartelerabd");
if ($conn->connect_error) {
  die("Error: " . $conn->connect_error);
}

$id_pelicula = intval($_GET['id_pelicula']);
$query = $conn->prepare("
  SELECT r.nombre, r.tipo, r.foto_url, pr.rol 
  FROM reparto r
  JOIN pelicula_reparto pr ON r.id_reparto = pr.id_reparto
  WHERE pr.id_pelicula = ?
");
$query->bind_param("i", $id_pelicula);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
  echo "<ul class='space-y-2'>";
  while ($row = $result->fetch_assoc()) {
    echo "<li class='flex items-center gap-3'>
            <img src='{$row['foto_url']}' class='w-10 h-10 rounded-full object-cover border border-purple-500'>
            <div>
              <p class='text-white font-semibold'>{$row['nombre']} <span class='text-sm text-purple-400'>({$row['tipo']})</span></p>
              <p class='text-sm text-gray-400 italic'>Rol: {$row['rol']}</p>
            </div>
          </li>";
  }
  echo "</ul>";
} else {
  echo "<p class='text-gray-400 italic'>No hay reparto registrado.</p>";
}
?>

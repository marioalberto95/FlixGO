<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['tipo_usuario'] === 'admin') {
  header("Location: login.php");
  exit;
}

$conn = new mysqli("localhost", "root", "", "cartelerabd");
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$id_usuario = $_SESSION['id_usuario'] ?? null;

// Eliminar de favoritos
if (isset($_GET['quitar'])) {
  $id_pelicula = intval($_GET['quitar']);
  $conn->query("DELETE FROM favoritos WHERE id_usuario = $id_usuario AND id_pelicula = $id_pelicula");
  header("Location: favoritos.php");
  exit;
}

$favoritos = [];
if ($id_usuario) {
  $sql = "
    SELECT p.*, g.nombre_genero, c.tipo_clasificacion
    FROM favoritos f
    INNER JOIN peliculas p ON f.id_pelicula = p.id_pelicula
    LEFT JOIN generos g ON p.genero_id = g.genero_id
    LEFT JOIN clasificaciones c ON p.id_clasificacion = c.id_clasificacion
    WHERE f.id_usuario = $id_usuario
    ORDER BY f.fecha_guardado DESC
  ";
  $result = $conn->query($sql);
  while ($row = $result->fetch_assoc()) {
    $favoritos[] = $row;
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Favoritos - FlixGO</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="estilos/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-slate-900 to-slate-800 text-white font-['Montserrat'] min-h-screen flex flex-col">

<header class="py-6 px-4 bg-black/50 backdrop-blur-md border-b border-purple-900/30">
  <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
    <h1 class="text-3xl md:text-4xl font-bold mb-4 md:mb-0 bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-500">
      FlixGO
    </h1>
    <nav class="flex items-center space-x-6 text-sm uppercase tracking-wide">
      <a href="index.php" class="hover:text-purple-400 transition-colors">Inicio</a>
      <a href="favoritos.php" class="hover:text-purple-400 transition-colors">Favoritos</a>
      <span class="ml-2 text-sm">Hola <?= $_SESSION['usuario'] ?></span>
      <a href="logout.php" class="ml-2 text-red-400 text-sm hover:underline">Salir</a>
    </nav>
  </div>
</header>

<main class="flex-grow container mx-auto px-4 py-10">
  <h2 class="text-2xl font-bold text-center mb-8">Mis Películas Favoritas</h2>

  <?php if (empty($favoritos)): ?>
    <p class="text-center text-gray-400">Aún no has guardado ninguna película como favorita.</p>
  <?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php foreach ($favoritos as $peli): ?>
        <div class="movie-card bg-slate-800/50 rounded-lg overflow-hidden border border-purple-900/20 hover:scale-[1.02] hover:shadow-lg hover:shadow-purple-500/20 transition-all duration-300">
          <img src="<?= $peli['poster_url'] ?>" alt="<?= $peli['titulo'] ?>" class="w-full h-48 object-cover">
          <div class="p-4">
            <h3 class="text-lg font-semibold"><?= $peli['titulo'] ?></h3>
            <p class="text-sm text-gray-400 mt-1">Género: <?= $peli['nombre_genero'] ?></p>
            <p class="text-sm text-gray-400">Clasificación: <?= $peli['tipo_clasificacion'] ?></p>
            <p class="text-sm text-gray-400 mt-2 line-clamp-3"><?= $peli['sinopsis'] ?></p>
            <div class="mt-3 flex justify-between items-center">
              <a href="#" class="text-purple-400 text-sm hover:underline" onclick="mostrarDetalles(<?= $peli['id_pelicula'] ?>, '<?= addslashes($peli['titulo']) ?>', '<?= addslashes($peli['sinopsis']) ?>', '<?= addslashes($peli['trailer_url']) ?>')">Ver más</a>
              <a href="favoritos.php?quitar=<?= $peli['id_pelicula'] ?>" class="text-red-400 text-sm hover:underline">Quitar</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<?php include 'footer.php'; ?>

<script>
function mostrarDetalles(idPelicula, titulo, sinopsis, trailerUrl) {
  const modal = document.createElement('div');
  modal.className = 'fixed inset-0 bg-black/70 flex items-center justify-center z-50';
  modal.innerHTML = `
    <div class='bg-slate-900 p-6 rounded shadow-lg max-w-lg w-full border border-purple-800 relative'>
      <button onclick='this.parentElement.parentElement.remove()' class='absolute top-2 right-2 text-gray-400 hover:text-white'>&times;</button>
      <h2 class='text-xl font-bold mb-2'>${titulo}</h2>
      <p class='text-sm text-gray-300 mb-4'>${sinopsis}</p>
      ${trailerUrl ? `<iframe class='w-full h-60 mb-4' src='${trailerUrl}' frameborder='0' allowfullscreen></iframe>` : '<p class="text-sm italic mb-4">Tráiler no disponible</p>'}
      <div id="reparto-${idPelicula}">
        <p class="text-purple-400 font-semibold mb-2">Reparto:</p>
        <div class="text-sm text-gray-300">Cargando reparto...</div>
      </div>
    </div>
  `;
  document.body.appendChild(modal);

  fetch('reparto_por_pelicula.php?id_pelicula=' + idPelicula)
    .then(res => res.text())
    .then(html => {
      document.getElementById('reparto-' + idPelicula).innerHTML = html;
    })
    .catch(err => {
      document.getElementById('reparto-' + idPelicula).innerHTML = '<p class="text-red-400">Error al cargar reparto</p>';
    });
}
</script>

</body>
</html>

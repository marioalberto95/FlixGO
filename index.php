<?php
session_start();
$conn = new mysqli("localhost", "root", "", "cartelerabd");

if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$peliculas = $conn->query("
  SELECT p.*, g.nombre_genero, c.tipo_clasificacion 
  FROM peliculas p 
  LEFT JOIN generos g ON p.genero_id = g.genero_id 
  LEFT JOIN clasificaciones c ON p.id_clasificacion = c.id_clasificacion 
  ORDER BY p.id_pelicula DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>FlixGO - Catálogo de Películas</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="estilos/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-slate-900 to-slate-800 text-white font-['Montserrat'] min-h-screen flex flex-col">

<!-- Header -->
<header class="py-6 px-4 bg-black/50 backdrop-blur-md border-b border-purple-900/30">
  <div class="container mx-auto flex flex-col md:flex-row justify-between items-center">
    <h1 class="text-3xl md:text-4xl font-bold mb-4 md:mb-0 bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-500">
      FlixGO
    </h1>
    <nav class="flex items-center space-x-6 text-sm uppercase tracking-wide">
      <a href="index.php" class="hover:text-purple-400 transition-colors">Inicio</a>

      <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'admin'): ?>
        <a href="agregar.php" class="hover:text-purple-400 transition-colors">Películas</a>
        <a href="generos.php" class="hover:text-purple-400 transition-colors">Géneros</a>
        <a href="clasificaciones.php" class="hover:text-purple-400 transition-colors">Clasificación</a>
        <a href="reparto.php" class="hover:text-purple-400 transition-colors">Reparto</a>
      <?php elseif (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'usuario'): ?>
        <a href="favoritos.php" class="hover:text-purple-400 transition-colors">Favoritos</a>
        <a href="#peliculas" class="hover:text-purple-400 transition-colors">Películas</a>
      <?php else: ?>
        <a href="#peliculas" class="hover:text-purple-400 transition-colors">Películas</a>
      <?php endif; ?>

      <?php if (isset($_SESSION['usuario'])): ?>
        <span class="ml-2 text-sm">Hola <?= $_SESSION['usuario'] ?></span>
        <a href="logout.php" class="ml-2 text-red-400 text-sm hover:underline">Salir</a>
      <?php else: ?>
        <a href="login.php" class="ml-4 bg-purple-500 px-4 py-2 rounded hover:bg-purple-600 text-sm">Iniciar Sesión</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="flex-grow">
  <section id="inicio" class="container mx-auto px-4 py-12">
    <div class="search-bar mb-12 max-w-2xl mx-auto">
      <div class="relative">
        <input type="text" placeholder="Buscar películas..." class="w-full bg-slate-800/50 border border-purple-900/30 rounded-full py-3 px-6 focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all">
        <button class="absolute right-3 top-1/2 -translate-y-1/2 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full p-2 hover:opacity-90 transition-opacity">
          <i class="fas fa-search"></i>
        </button>
      </div>
    </div>
  </section>

  <section id="peliculas" class="container mx-auto px-4 py-12">
    <h2 class="text-2xl font-bold mb-8 text-center">Películas Destacadas</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php while ($peli = $peliculas->fetch_assoc()): ?>
        <div class="movie-card bg-slate-800/50 rounded-lg overflow-hidden border border-purple-900/20 hover:scale-[1.02] hover:shadow-lg hover:shadow-purple-500/20 transition-all duration-300">
          <img src="<?= $peli['poster_url'] ?>" alt="<?= $peli['titulo'] ?>" class="w-full h-48 object-cover">
          <div class="p-4">
            <h3 class="text-lg font-semibold"><?= $peli['titulo'] ?></h3>
            <p class="text-sm text-gray-400 mt-1">Género: <?= $peli['nombre_genero'] ?></p>
            <p class="text-sm text-gray-400">Clasificación: <?= $peli['tipo_clasificacion'] ?></p>
            <p class="text-sm text-gray-400">Estreno: <?= $peli['fecha_estreno'] ?></p>
            <p class="text-sm text-gray-400 mt-2 line-clamp-3"><?= $peli['sinopsis'] ?></p>
            <div class="mt-3 flex justify-between items-center">
              <a href="#" class="text-sm text-purple-400 hover:underline"
                 onclick="mostrarDetalles(<?= $peli['id_pelicula'] ?>, '<?= addslashes($peli['titulo']) ?>', '<?= addslashes($peli['sinopsis']) ?>', '<?= addslashes($peli['trailer_url']) ?>')">
                Ver más
              </a>
              <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'usuario'): ?>
                <button onclick="guardarFavorito(<?= $peli['id_pelicula'] ?>)" class="text-sm text-white bg-purple-600 px-3 py-1 rounded hover:bg-purple-700">Guardar</button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>
</main>

<?php include 'footer.php'; ?>

<!-- Modal con reparto -->
<script>
function mostrarDetalles(idPelicula, titulo, sinopsis, trailerUrl) {
  const modal = document.createElement('div');
  modal.className = 'fixed inset-0 bg-black/70 flex items-center justify-center z-50';
  modal.innerHTML = `
    <div class='bg-slate-900 p-6 rounded shadow-lg max-w-lg w-full border border-purple-800 relative'>
      <button onclick='this.parentElement.parentElement.remove()' class='absolute top-2 right-2 text-gray-400 hover:text-white'>&times;</button>
      <h2 class='text-xl font-bold mb-2'>${titulo}</h2>
      <p class='text-sm text-gray-300 mb-4'>${sinopsis}</p>
      ${trailerUrl ? `<iframe class='w-full h-60 mb-4' src='${trailerUrl}' frameborder='0' allowfullscreen></iframe>` : '<p class="text-sm italic">Tráiler no disponible</p>'}
      <div id="reparto-${idPelicula}" class="text-sm text-gray-300">Cargando reparto...</div>
    </div>
  `;
  document.body.appendChild(modal);

  fetch('reparto_por_pelicula.php?id_pelicula=' + idPelicula)
    .then(res => res.text())
    .then(html => {
      document.getElementById('reparto-' + idPelicula).innerHTML = html;
    })
    .catch(() => {
      document.getElementById('reparto-' + idPelicula).innerHTML = "<p class='text-red-400'>Error al cargar reparto</p>";
    });
}

function guardarFavorito(idPelicula) {
  fetch('guardar_favorito.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'id_pelicula=' + idPelicula
  })
  .then(response => response.text())
  .then(data => alert(data))
  .catch(() => alert('Error al guardar en favoritos'));
}
</script>

</body>
</html>

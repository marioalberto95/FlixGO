<?php
session_start();
if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$conn = new mysqli("localhost", "root", "", "cartelerabd");
if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

$clasificaciones = $conn->query("SELECT * FROM clasificaciones");
$generos = $conn->query("SELECT * FROM generos");

if (isset($_POST['guardar'])) {
  $titulo = $_POST['titulo'];
  $sinopsis = $_POST['sinopsis'];
  $fecha = $_POST['fecha_estreno'];
  $duracion = $_POST['duracion'];
  $clasificacion = empty($_POST['id_clasificacion']) ? 'NULL' : $_POST['id_clasificacion'];
  $genero = empty($_POST['genero_id']) ? 'NULL' : $_POST['genero_id'];
  $poster = $_POST['poster_url'];
  $trailer = $_POST['trailer_url'];

  $sql = "INSERT INTO peliculas (titulo, sinopsis, fecha_estreno, duracion, id_clasificacion, genero_id, poster_url, trailer_url)
          VALUES ('$titulo', '$sinopsis', '$fecha', '$duracion', $clasificacion, $genero, '$poster', '$trailer')";

  if ($conn->query($sql) === TRUE) {
    header("Location: index.php");
    exit;
  } else {
    echo "Error: " . $conn->error;
  }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Película</title>
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
        <a href="agregar.php" class="hover:text-purple-400 transition-colors">Películas</a>
        <a href="generos.php" class="hover:text-purple-400 transition-colors">Géneros</a>
        <a href="clasificaciones.php" class="hover:text-purple-400 transition-colors">Clasificación</a>
        <a href="reparto.php" class="hover:text-purple-400 transition-colors">Reparto</a>
        <span class="ml-2 text-sm">Hola <?= $_SESSION['usuario'] ?></span>
        <a href="logout.php" class="ml-2 text-red-400 text-sm hover:underline">Salir</a>
      </nav>
    </div>
  </header>

  <main class="flex-grow container mx-auto px-4 py-10">
    <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-800 p-6 rounded border border-purple-900/30">
      <input type="text" name="titulo" placeholder="Título" required class="p-2 rounded bg-slate-700">
      <input type="text" name="duracion" placeholder="Duración (min)" required class="p-2 rounded bg-slate-700">
      <input type="date" name="fecha_estreno" required class="p-2 rounded bg-slate-700">

      <select name="id_clasificacion" required class="p-2 rounded bg-slate-700">
        <option value="">Clasificación</option>
        <?php while ($c = $clasificaciones->fetch_assoc()): ?>
          <option value="<?= $c['id_clasificacion'] ?>"><?= $c['tipo_clasificacion'] ?></option>
        <?php endwhile; ?>
      </select>

      <select name="genero_id" required class="p-2 rounded bg-slate-700">
        <option value="">Género</option>
        <?php while ($g = $generos->fetch_assoc()): ?>
          <option value="<?= $g['genero_id'] ?>"><?= $g['nombre_genero'] ?></option>
        <?php endwhile; ?>
      </select>

      <div class="flex flex-col md:col-span-2">
        <label class="mb-1 text-sm">URL del póster</label>
        <input type="text" name="poster_url" placeholder="URL del póster" required class="p-2 rounded bg-slate-700 mb-2" oninput="document.getElementById('previewPoster').src = this.value">
        <img id="previewPoster" src="" alt="Vista previa del póster" class="w-40 h-56 object-cover border border-slate-600 rounded">
      </div>

      <input type="text" name="trailer_url" placeholder="URL del tráiler (opcional)" class="p-2 rounded bg-slate-700">

      <textarea name="sinopsis" placeholder="Sinopsis" class="md:col-span-2 p-2 rounded bg-slate-700"></textarea>

      <button type="submit" name="guardar" class="md:col-span-2 bg-purple-600 py-2 rounded hover:bg-purple-700">
        Guardar Película
      </button>
    </form>

    <!-- Lista de películas agregadas -->
    <section class="mt-10">
      <h2 class="text-xl font-bold mb-4">Películas Registradas</h2>
      <div class="overflow-x-auto">
        <table class="min-w-full bg-slate-800 border border-purple-900/30 rounded">
          <thead>
            <tr class="text-left text-sm uppercase text-purple-300 border-b border-purple-900/30">
              <th class="p-3">Título</th>
              <th class="p-3">Duración</th>
              <th class="p-3">Estreno</th>
              <th class="p-3">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $pelis = $conn->query("SELECT id_pelicula, titulo, duracion, fecha_estreno FROM peliculas ORDER BY id_pelicula DESC");
            while ($fila = $pelis->fetch_assoc()): ?>
              <tr class="border-b border-purple-900/20 hover:bg-slate-700/50">
                <td class="p-3"><?= $fila['titulo'] ?></td>
                <td class="p-3"><?= $fila['duracion'] ?> min</td>
                <td class="p-3"><?= $fila['fecha_estreno'] ?></td>
                <td class="p-3 space-x-2">
                  <a href="editar_pelicula.php?id=<?= $fila['id_pelicula'] ?>" class="text-yellow-400 hover:underline">Editar</a>
                  <a href="eliminar_pelicula.php?id=<?= $fila['id_pelicula'] ?>" class="text-red-400 hover:underline" onclick="return confirm('¿Eliminar esta película?')">Eliminar</a>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </section>

  </main>

  <?php include 'footer.php'; ?>

</body>
</html>
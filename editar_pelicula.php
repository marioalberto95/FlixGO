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

$id = $_GET['id'] ?? null;
if (!$id) {
  die("ID de película no válido");
}

$clasificaciones = $conn->query("SELECT * FROM clasificaciones");
$generos = $conn->query("SELECT * FROM generos");

$pelicula = $conn->query("SELECT * FROM peliculas WHERE id_pelicula = $id")->fetch_assoc();
if (!$pelicula) {
  die("Película no encontrada");
}

if (isset($_POST['actualizar'])) {
  $titulo = $_POST['titulo'];
  $sinopsis = $_POST['sinopsis'];
  $fecha = $_POST['fecha_estreno'];
  $duracion = $_POST['duracion'];
  $clasificacion = $_POST['id_clasificacion'];
  $genero = $_POST['genero_id'];
  $poster = $_POST['poster_url'];
  $trailer = $_POST['trailer_url'];

  $sql = "UPDATE peliculas SET 
            titulo='$titulo', 
            sinopsis='$sinopsis', 
            fecha_estreno='$fecha', 
            duracion='$duracion', 
            id_clasificacion=$clasificacion, 
            genero_id=$genero,
            poster_url='$poster',
            trailer_url='$trailer'
          WHERE id_pelicula=$id";

  if ($conn->query($sql)) {
    header("Location: agregar.php");
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
  <title>Editar Película</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="estilos/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gradient-to-br from-slate-900 to-slate-800 text-white font-['Montserrat'] min-h-screen flex flex-col">

  <header class="py-6 px-4 bg-black/50 backdrop-blur-md border-b border-purple-900/30">
    <div class="container mx-auto flex justify-between items-center">
      <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-500">
        FlixGO
      </h1>
      <nav class="space-x-4 text-sm">
        <a href="index.php" class="hover:text-purple-400">Inicio</a>
        <a href="agregar.php" class="hover:text-purple-400">Películas</a>
        <a href="logout.php" class="text-red-400 hover:underline">Salir</a>
      </nav>
    </div>
  </header>

  <main class="flex-grow container mx-auto px-4 py-10">
    <h2 class="text-xl font-bold mb-6">Editar Película</h2>
    <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-800 p-6 rounded border border-purple-900/30">
      <input type="text" name="titulo" placeholder="Título" value="<?= $pelicula['titulo'] ?>" required class="p-2 rounded bg-slate-700">
      <input type="text" name="duracion" placeholder="Duración" value="<?= $pelicula['duracion'] ?>" required class="p-2 rounded bg-slate-700">
      <input type="date" name="fecha_estreno" value="<?= $pelicula['fecha_estreno'] ?>" required class="p-2 rounded bg-slate-700">

      <select name="id_clasificacion" required class="p-2 rounded bg-slate-700">
        <?php while ($c = $clasificaciones->fetch_assoc()): ?>
          <option value="<?= $c['id_clasificacion'] ?>" <?= $c['id_clasificacion'] == $pelicula['id_clasificacion'] ? 'selected' : '' ?>>
            <?= $c['tipo_clasificacion'] ?>
          </option>
        <?php endwhile; ?>
      </select>

      <select name="genero_id" required class="p-2 rounded bg-slate-700">
        <?php while ($g = $generos->fetch_assoc()): ?>
          <option value="<?= $g['genero_id'] ?>" <?= $g['genero_id'] == $pelicula['genero_id'] ? 'selected' : '' ?>>
            <?= $g['nombre_genero'] ?>
          </option>
        <?php endwhile; ?>
      </select>

      <div class="flex flex-col md:col-span-2">
  <label class="mb-1 text-sm">URL del póster</label>
  <input type="text" name="poster_url" placeholder="URL del póster" value="<?= $pelicula['poster_url'] ?>" required class="p-2 rounded bg-slate-700 mb-2" oninput="document.getElementById('previewPoster').src = this.value">
  <img id="previewPoster" src="<?= $pelicula['poster_url'] ?>" alt="Vista previa del póster" class="w-40 h-56 object-cover border border-slate-600 rounded">
</div>
      <input type="text" name="trailer_url" placeholder="URL del tráiler" value="<?= $pelicula['trailer_url'] ?>" class="p-2 rounded bg-slate-700">

      <textarea name="sinopsis" placeholder="Sinopsis" class="md:col-span-2 p-2 rounded bg-slate-700"><?= $pelicula['sinopsis'] ?></textarea>

      <button type="submit" name="actualizar" class="md:col-span-2 bg-yellow-500 py-2 rounded hover:bg-yellow-600">Actualizar Película</button>
    </form>
  </main>

  <?php include 'footer.php'; ?>

</body>
</html>

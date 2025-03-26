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

$peliculas = $conn->query("SELECT id_pelicula, titulo FROM peliculas ORDER BY titulo ASC");

// Editar reparto
if (isset($_POST['editar'])) {
  $id_reparto = $_POST['id_reparto'];
  $nombre = $_POST['nombre'];
  $tipo = $_POST['tipo'];
  $foto_url = $_POST['foto_url'];
  $id_pelicula = $_POST['id_pelicula'];
  $rol = $_POST['rol'];

  $stmt = $conn->prepare("UPDATE reparto SET nombre=?, tipo=?, foto_url=? WHERE id_reparto=?");
  $stmt->bind_param("sssi", $nombre, $tipo, $foto_url, $id_reparto);
  $stmt->execute();
  $stmt->close();

  $conn->query("DELETE FROM pelicula_reparto WHERE id_reparto = $id_reparto");
  $stmt2 = $conn->prepare("INSERT INTO pelicula_reparto (id_pelicula, id_reparto, rol) VALUES (?, ?, ?)");
  $stmt2->bind_param("iis", $id_pelicula, $id_reparto, $rol);
  $stmt2->execute();
  $stmt2->close();

  header("Location: reparto.php");
  exit;
}

// Agregar reparto
if (isset($_POST['guardar'])) {
  $nombre = $_POST['nombre'];
  $tipo = $_POST['tipo'];
  $foto_url = $_POST['foto_url'];
  $id_pelicula = $_POST['id_pelicula'];
  $rol = $_POST['rol'];

  $stmt = $conn->prepare("INSERT INTO reparto (nombre, tipo, foto_url) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $nombre, $tipo, $foto_url);
  $stmt->execute();
  $id_reparto = $conn->insert_id;
  $stmt->close();

  $stmt2 = $conn->prepare("INSERT INTO pelicula_reparto (id_pelicula, id_reparto, rol) VALUES (?, ?, ?)");
  $stmt2->bind_param("iis", $id_pelicula, $id_reparto, $rol);
  $stmt2->execute();
  $stmt2->close();

  header("Location: reparto.php");
  exit;
}

// Eliminar reparto
if (isset($_GET['eliminar'])) {
  $id = intval($_GET['eliminar']);
  $conn->query("DELETE FROM pelicula_reparto WHERE id_reparto = $id");
  $conn->query("DELETE FROM reparto WHERE id_reparto = $id");
  header("Location: reparto.php");
  exit;
}

$reparto = $conn->query("
  SELECT r.*, p.titulo AS pelicula, pr.rol, pr.id_pelicula
  FROM reparto r
  LEFT JOIN pelicula_reparto pr ON r.id_reparto = pr.id_reparto
  LEFT JOIN peliculas p ON pr.id_pelicula = p.id_pelicula
  ORDER BY r.id_reparto DESC
");
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Reparto</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="estilos/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-slate-900 to-slate-800 text-white font-['Montserrat'] min-h-screen flex flex-col">

<!-- Header igual al resto del sistema -->
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
      <a href="reparto.php" class="text-purple-400 font-bold">Reparto</a>
      <span class="ml-2 text-sm">Hola <?= $_SESSION['usuario'] ?></span>
      <a href="logout.php" class="ml-2 text-red-400 text-sm hover:underline">Salir</a>
    </nav>
  </div>
</header>

<main class="flex-grow container mx-auto px-4 py-10">
  <!-- Formulario de agregar reparto -->
  <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-slate-800 p-6 rounded border border-purple-900/30">
    <input type="text" name="nombre" placeholder="Nombre del actor/director" required class="p-2 rounded bg-slate-700">
    <select name="tipo" required class="p-2 rounded bg-slate-700">
      <option value="">Selecciona tipo</option>
      <option value="Actor">Actor</option>
      <option value="Director">Director</option>
    </select>
    <input type="text" name="foto_url" id="foto_url_input" placeholder="URL de la foto" class="p-2 rounded bg-slate-700 md:col-span-2" oninput="previsualizarFoto()">
    <div class="md:col-span-2 flex justify-center">
      <img id="foto_preview" src="" alt="Previsualización" class="w-24 h-24 object-cover rounded-full border border-purple-600 hidden">
    </div>
    <select name="id_pelicula" required class="p-2 rounded bg-slate-700">
      <option value="">Selecciona película</option>
      <?php $peliculas->data_seek(0); while ($p = $peliculas->fetch_assoc()): ?>
        <option value="<?= $p['id_pelicula'] ?>"><?= htmlspecialchars($p['titulo']) ?></option>
      <?php endwhile; ?>
    </select>
    <input type="text" name="rol" placeholder="Rol en la película" required class="p-2 rounded bg-slate-700">
    <button type="submit" name="guardar" class="md:col-span-2 bg-purple-600 py-2 rounded hover:bg-purple-700">Agregar al Reparto</button>
  </form>

  <!-- Lista de reparto -->
  <section class="mt-10">
    <h2 class="text-xl font-bold mb-4">Reparto Registrado</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
      <?php $reparto->data_seek(0); while ($actor = $reparto->fetch_assoc()): ?>
        <div class="bg-slate-800/50 rounded-lg border border-purple-900/20 p-4 text-center">
          <img src="<?= $actor['foto_url'] ?>" alt="<?= $actor['nombre'] ?>" class="w-24 h-24 object-cover rounded-full mx-auto mb-3">
          <h3 class="text-lg font-semibold"><?= $actor['nombre'] ?></h3>
          <p class="text-purple-300 text-sm"><?= $actor['tipo'] ?></p>
          <p class="text-sm text-gray-400 italic mt-1">Rol: <?= $actor['rol'] ?></p>
          <p class="text-sm text-gray-400 italic">Película: <?= $actor['pelicula'] ?></p>
          <div class="flex justify-center space-x-2 text-sm mt-3">
            <a href="?editar=<?= $actor['id_reparto'] ?>" class="bg-yellow-500 hover:bg-yellow-600 px-3 py-1 rounded">Editar</a>
            <a href="?eliminar=<?= $actor['id_reparto'] ?>" onclick="return confirm('¿Eliminar este registro?')" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded">Eliminar</a>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </section>
</main>

<!-- Modal editar -->
<?php if (isset($_GET['editar'])):
  $idEditar = intval($_GET['editar']);
  $editarData = $conn->query("SELECT r.*, pr.rol, pr.id_pelicula FROM reparto r LEFT JOIN pelicula_reparto pr ON r.id_reparto = pr.id_reparto WHERE r.id_reparto = $idEditar")->fetch_assoc();
?>
<div class="fixed inset-0 bg-black/60 flex justify-center items-center z-50">
  <form method="POST" class="bg-slate-900 p-6 rounded-lg border border-purple-800 w-full max-w-lg relative">
    <button onclick="this.parentElement.parentElement.remove()" type="button" class="absolute top-2 right-3 text-gray-400 hover:text-white text-2xl">&times;</button>
    <input type="hidden" name="id_reparto" value="<?= $editarData['id_reparto'] ?>">
    <h2 class="text-xl font-bold mb-4 text-purple-400">Editar Reparto</h2>
    <input type="text" name="nombre" value="<?= $editarData['nombre'] ?>" required class="w-full mb-3 p-2 rounded bg-slate-700" placeholder="Nombre">
    <select name="tipo" required class="w-full mb-3 p-2 rounded bg-slate-700">
      <option value="Actor" <?= $editarData['tipo'] === 'Actor' ? 'selected' : '' ?>>Actor</option>
      <option value="Director" <?= $editarData['tipo'] === 'Director' ? 'selected' : '' ?>>Director</option>
    </select>
    <input type="text" name="foto_url" id="editar_foto_url" value="<?= $editarData['foto_url'] ?>" class="w-full mb-3 p-2 rounded bg-slate-700" placeholder="URL de la foto" oninput="previewEditarFoto()">
    <div class="flex justify-center mb-4">
      <img id="editar_foto_preview" src="<?= $editarData['foto_url'] ?>" alt="Previsualización" class="w-24 h-24 rounded-full object-cover border border-purple-600">
    </div>
    <select name="id_pelicula" required class="w-full mb-3 p-2 rounded bg-slate-700">
      <?php $pelis = $conn->query("SELECT id_pelicula, titulo FROM peliculas ORDER BY titulo ASC");
      while ($p = $pelis->fetch_assoc()): ?>
        <option value="<?= $p['id_pelicula'] ?>" <?= $p['id_pelicula'] == $editarData['id_pelicula'] ? 'selected' : '' ?>><?= $p['titulo'] ?></option>
      <?php endwhile; ?>
    </select>
    <input type="text" name="rol" value="<?= $editarData['rol'] ?>" class="w-full mb-4 p-2 rounded bg-slate-700" placeholder="Rol en la película">
    <button type="submit" name="editar" class="w-full bg-purple-600 py-2 rounded hover:bg-purple-700">Guardar Cambios</button>
  </form>
</div>
<?php endif; ?>

<!-- Scripts -->
<script>
function previsualizarFoto() {
  const url = document.getElementById('foto_url_input').value;
  const preview = document.getElementById('foto_preview');
  if (url) {
    preview.src = url;
    preview.classList.remove('hidden');
  } else {
    preview.classList.add('hidden');
  }
}

function previewEditarFoto() {
  const url = document.getElementById('editar_foto_url').value;
  const img = document.getElementById('editar_foto_preview');
  img.src = url;
}
</script>
<?php include 'footer.php'; ?>
</body>
</html>

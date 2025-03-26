<?php
session_start();
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $conn = new mysqli("localhost", "root", "", "cartelerabd");

  if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
  }

  $email = $conn->real_escape_string($_POST["email"]);
  $pass = hash('sha256', $_POST["password"]); // Encriptar con SHA-256

  $query = "SELECT * FROM usuarios WHERE email='$email' AND contraseña='$pass'";
  $result = $conn->query($query);

  if ($result && $result->num_rows == 1) {
    $user = $result->fetch_assoc();
    $_SESSION["usuario"] = $user["nombre"];
    $_SESSION["tipo_usuario"] = $user["tipo_usuario"];
    $_SESSION["id_usuario"] = $user["id_usuario"]; // Por si lo usas para favoritos
    header("Location: index.php");
    exit();
  } else {
    $error = "Correo o contraseña incorrectos";
  }

  $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión - FlixGO</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="estilos/style.css">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-slate-900 to-slate-800 text-white font-['Montserrat'] min-h-screen flex flex-col">

  <!-- Header -->
  <header class="py-6 px-4 bg-black/50 backdrop-blur-md border-b border-purple-900/30">
    <div class="container mx-auto flex justify-between items-center">
      <h1 class="text-3xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-400 to-pink-500">
        FlixGO
      </h1>
      <nav class="space-x-4 text-sm">
        <a href="index.php" class="bg-purple-600 px-3 py-1 rounded hover:bg-purple-700">Inicio</a>
      </nav>
    </div>
  </header>

  <!-- Formulario de Login -->
  <main class="flex-grow flex items-center justify-center">
    <div class="bg-slate-800 p-8 rounded-lg shadow-md w-full max-w-md border border-purple-900/40">
      <h2 class="text-2xl font-bold mb-6 text-center text-purple-400">Iniciar Sesión</h2>
      
      <?php if (!empty($error)): ?>
        <p class="text-red-400 text-sm text-center mb-3"><?= $error ?></p>
      <?php endif; ?>

      <form method="POST" action="login.php" class="space-y-4">
        <input type="email" name="email" placeholder="Correo electrónico" required class="w-full px-4 py-2 rounded bg-slate-700 border border-purple-900/30">
        <input type="password" name="password" placeholder="Contraseña" required class="w-full px-4 py-2 rounded bg-slate-700 border border-purple-900/30">
        <button type="submit" class="w-full bg-purple-600 py-2 rounded hover:bg-purple-700 transition">Ingresar</button>
      </form>
    </div>
  </main>

  <?php include 'footer.php'; ?>
</body>
</html>

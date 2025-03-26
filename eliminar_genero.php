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
    die("ID de género no válido");
}

$sql = "DELETE FROM generos WHERE genero_id = $id";

if ($conn->query($sql) === TRUE) {
    header("Location: generos.php");
    exit;
} else {
    echo "Error: " . $conn->error;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Eliminar Género</title>
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
                <a href="generos.php" class="hover:text-purple-400">Géneros</a>
                <a href="logout.php" class="text-red-400 hover:underline">Salir</a>
            </nav>
        </div>
    </header>

    <main class="flex-grow container mx-auto px-4 py-10">
        <div class="bg-slate-800 p-6 rounded border border-purple-900/30">
            <h2 class="text-xl font-bold mb-6">Eliminar Género</h2>
            <p>¿Estás seguro de que quieres eliminar este género?</p>
            <form method="POST">
                <button type="submit" class="bg-red-500 py-2 px-4 rounded hover:bg-red-600">Eliminar Género</button>
                <a href="generos.php" class="bg-gray-500 py-2 px-4 rounded hover:bg-gray-600 ml-2">Cancelar</a>
            </form>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>
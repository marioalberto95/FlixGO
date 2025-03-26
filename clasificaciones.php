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

$error_message = "";

if (isset($_POST['guardar_clasificacion'])) {
    $tipo_clasificacion = trim($_POST['tipo_clasificacion']); // Limpiar espacios en blanco
    $descripcion = trim($_POST['descripcion']); // Limpiar espacios en blanco

    // Validar que el tipo de clasificación no esté vacío
    if (empty($tipo_clasificacion)) {
        $error_message = "El tipo de clasificación no puede estar vacío.";
    } else {
        // Verificar si la clasificación ya existe (case-insensitive)
        $stmt_check = $conn->prepare("SELECT id_clasificacion FROM clasificaciones WHERE LOWER(tipo_clasificacion) = ?");
        $tipo_clasificacion_lower = strtolower($tipo_clasificacion);
        $stmt_check->bind_param("s", $tipo_clasificacion_lower);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $error_message = "La clasificación '$tipo_clasificacion' ya existe.";
        } else {
            // Insertar la nueva clasificación
            $sql = "INSERT INTO clasificaciones (tipo_clasificacion, descripcion) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $tipo_clasificacion, $descripcion);

            if ($stmt->execute()) {
                header("Location: clasificaciones.php");
                exit;
            } else {
                $error_message = "Error al guardar la clasificación: " . $conn->error;
            }
            $stmt->close();
        }
        $stmt_check->close();
    }
}

if (isset($_GET['eliminar'])) {
    $id_clasificacion = $_GET['eliminar'];
    $sql = "DELETE FROM clasificaciones WHERE id_clasificacion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_clasificacion);
    if ($stmt->execute()) {
        header("Location: clasificaciones.php");
        exit;
    } else {
        $error_message = "Error al eliminar la clasificación: " . $conn->error;
    }
    $stmt->close();
}

if (isset($_POST['editar_clasificacion'])) {
    $id_clasificacion = $_POST['id_clasificacion'];
    $tipo_clasificacion = trim($_POST['tipo_clasificacion']); // Limpiar espacios en blanco
    $descripcion = trim($_POST['descripcion']); // Limpiar espacios en blanco
    $tipo_clasificacion_lower = strtolower($tipo_clasificacion);

    // Validar que el tipo de clasificación no esté vacío
    if (empty($tipo_clasificacion)) {
        $error_message = "El tipo de clasificación no puede estar vacío.";
    } else {
        // Verificar si la clasificación ya existe (case-insensitive) excluyendo la actual
        $stmt_check_edit = $conn->prepare("SELECT id_clasificacion FROM clasificaciones WHERE LOWER(tipo_clasificacion) = ? AND id_clasificacion != ?");
        $stmt_check_edit->bind_param("si", $tipo_clasificacion_lower, $id_clasificacion);
        $stmt_check_edit->execute();
        $result_check_edit = $stmt_check_edit->get_result();

        if ($result_check_edit->num_rows > 0) {
            $error_message = "La clasificación '$tipo_clasificacion' ya existe.";
        } else {
            $sql = "UPDATE clasificaciones SET tipo_clasificacion = ?, descripcion = ? WHERE id_clasificacion = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $tipo_clasificacion, $descripcion, $id_clasificacion);

            if ($stmt->execute()) {
                header("Location: clasificaciones.php");
                exit;
            } else {
                $error_message = "Error al actualizar la clasificación: " . $conn->error;
            }
            $stmt->close();
        }
        $stmt_check_edit->close();
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Administrar Clasificaciones</title>
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
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error:</strong>
                <span class="block sm:inline"><?= $error_message ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="bg-slate-800 p-6 rounded border border-purple-900/30 mb-8">
            <div class="mb-4">
                <label for="tipo_clasificacion" class="block text-sm font-medium text-white mb-2">Tipo de Clasificación</label>
                <input type="text" name="tipo_clasificacion" id="tipo_clasificacion" placeholder="Tipo de Clasificación" required class="p-2 w-full rounded bg-slate-700 text-white">
            </div>
            <div class="mb-4">
                <label for="descripcion" class="block text-sm font-medium text-white mb-2">Descripción</label>
                <textarea name="descripcion" id="descripcion" placeholder="Descripción" required class="p-2 w-full rounded bg-slate-700 text-white"></textarea>
            </div>
            <button type="submit" name="guardar_clasificacion" class="bg-purple-600 py-2 px-4 rounded hover:bg-purple-700 mt-4">
                Agregar Clasificación
            </button>
        </form>

        <section>
            <h2 class="text-xl font-bold mb-4">Lista de Clasificaciones</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-slate-800 border border-purple-900/30 rounded">
                    <thead>
                        <tr class="text-left text-sm uppercase text-purple-300 border-b border-purple-900/30">
                            <th class="p-3">ID</th>
                            <th class="p-3">Tipo de Clasificación</th>
                            <th class="p-3">Descripción</th>
                            <th class="p-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $clasificaciones = $conn->query("SELECT * FROM clasificaciones ORDER BY id_clasificacion DESC");
                        while ($fila = $clasificaciones->fetch_assoc()): ?>
                            <tr class="border-b border-purple-900/20 hover:bg-slate-700/50">
                                <td class="p-3"><?= $fila['id_clasificacion'] ?></td>
                                <td class="p-3"><?= $fila['tipo_clasificacion'] ?></td>
                                <td class="p-3"><?= $fila['descripcion'] ?></td>
                                <td class="p-3 space-x-2">
                                    <a href="editar_clasificacion.php?id=<?= $fila['id_clasificacion'] ?>" class="text-yellow-400 hover:underline">Editar</a>
                                    <a href="eliminar_clasificacion.php?id=<?= $fila['id_clasificacion'] ?>" class="text-red-400 hover:underline" onclick="return confirm('¿Eliminar esta clasificación?')">Eliminar</a>
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
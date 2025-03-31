<?php
session_start();
// Configurar una cookie de visita
if (!isset($_COOKIE['visitas'])) {
    setcookie('visitas', 1, time() + 86400 * 30); // Caduca en 30 días
} else {
    setcookie('visitas', $_COOKIE['visitas'] + 1, time() + 86400 * 30);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Interactivo</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="seccion">
        <h2>Formulario de Registro</h2>
        <form action="procesar.php" method="post" enctype="multipart/form-data">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <label for="foto">Foto de perfil:</label>
            <input type="file" id="foto" name="foto" accept="image/*">

            <button type="submit">Registrarse</button>
        </form>
    </div>

    <div class="seccion">
        <h2>Información de Sesión</h2>
        <?php if (isset($_SESSION['usuario'])): ?>
            <p>Bienvenido, <?= htmlspecialchars($_SESSION['usuario']) ?>!</p>
            <a href="perfil.php">Ver perfil</a> | 
            <a href="logout.php">Cerrar sesión</a>
        <?php else: ?>
            <p>No has iniciado sesión.</p>
        <?php endif; ?>

        <p>Visitas a esta página: <?= $_COOKIE['visitas'] ?? 1 ?></p>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
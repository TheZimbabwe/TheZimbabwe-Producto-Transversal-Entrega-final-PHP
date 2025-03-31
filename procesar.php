<?php
session_start();

// Validación con expresiones regulares
function validarDatos($nombre, $email, $password) {
    if (!preg_match('/^[a-zA-Z\s]{3,50}$/', $nombre)) {
        return "El nombre solo puede contener letras y espacios (3-50 caracteres)";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return "El email no es válido";
    }

    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/', $password)) {
        return "La contraseña debe tener al menos 8 caracteres, una letra y un número";
    }

    return true;
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $validacion = validarDatos($nombre, $email, $password);

    if ($validacion === true) {
        // Guardar en sesión
        $_SESSION['usuario'] = $nombre;
        $_SESSION['email'] = $email;

        // Manejo de archivos
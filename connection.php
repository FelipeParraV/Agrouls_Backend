<?php
    // Crear conexión
    $conn = new mysqli("localhost", "root", "", "agrouls_official");

    // Verificar conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }
?>

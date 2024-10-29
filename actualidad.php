<?php
    // Habilitar CORS
    header("Access-Control-Allow-Origin: *"); // Cambia esta URL para desarrollo
    header("Access-Control-Allow-Methods: GET");
    header("Access-Control-Allow-Headers: Content-Type");

    // Incluir archivo de configuración de la base de datos
    include 'connection.php';

    // Recuperar datos de la tabla de imágenes
    $sql = "SELECT id, titulo, fecha, enunciado, descripcion, imagen FROM actualidad order by orden";
    $result = $conn->query($sql);

    $filas = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $filas[] = $row;
        }
    }

    echo json_encode($filas);

    $conn->close();
?>

<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type");

    include 'connection.php'; // Archivo de conexión a la base de datos

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Ejecutar la consulta SQL y almacenar el resultado en la variable $result
            $sql = "SELECT * FROM cec_1 ORDER BY orden";
            $result = $conn->query($sql);
            $rows = array(); // Inicializar un array vacío para almacenar los registros recuperados
            // Iterar sobre cada fila en el conjunto de resultados
            while($row = $result->fetch_assoc()) {
                // Agregar cada fila recuperada al array $rows
                $rows[] = $row;
            }
            echo json_encode($rows);
            break;

        case 'POST':

            $action = $_POST['action'] ?? 'new';

            //echo "Action: $action";

            if ($action === 'new') {
                $sql = "SELECT orden FROM cec_1 ORDER BY orden DESC LIMIT 1"; // Consulta SQL para obtener el último orden

                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $orden = $row['orden'] + 1;
                    echo "Orden: $orden";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }

                // Variables adicionales
                $presentacion = $_POST['presentacion']; // Obtiene el título de la noticia
                $directorio = $_POST['directorio']; // Obtiene el título de la noticia
                $programa = $_POST['programa']; // Obtiene el título de la noticia

                // Insertar los datos en la base de datos
                $sql = "INSERT INTO cec_1 (presentacion, directorio, programa, orden) VALUES ('$presentacion', '$directorio', '$programa', '$orden')";

                if ($conn->query($sql) === TRUE) { // Si la consulta se ejecuta correctamente
                    echo "Image and data saved to database. Well Done!"; // Muestra un mensaje de éxito
                } else { // Si hay un error en la consulta
                    echo "Error: " . $sql . "<br>" . $conn->error;  // Muestra un mensaje de error
                }

            } elseif ($action === 'update_order') {
                $items = $_POST['items'];
                foreach ($items as $item) {
                    $id = $item['id'];
                    $order = $item['order'];
                    $sql = "UPDATE cec_1 SET orden = ? WHERE id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ii", $order, $id);
                    $stmt->execute();
                }
                echo json_encode(["message" => "Orden actualizado exitosamente"]);
                break;
            } else {
                echo "Accion no permitada";
            }
            
            break;

        case 'PUT':
            $_PUT = json_decode(file_get_contents("php://input"), true); // Obtener los datos enviados en la solicitud

            $id = $_PUT['editId'];
            $presentacion = $_PUT['editPresentacion']; // Obtiene el título de la noticia
            $directorio = $_PUT['editDirectorio']; // Obtiene el título de la noticia
            $programa = $_PUT['editPrograma']; // Obtiene el título de la noticia

            $sql = "UPDATE cec_1 SET presentacion='$presentacion', directorio='$directorio' programa='$programa' WHERE id=$id";

            if ($conn->query($sql) === TRUE) { // Si la consulta se ejecuta correctamente
                echo "Datos actualizados"; // Muestra un mensaje de éxito
            } else { // Si hay un error en la consulta
                echo "Error: " . $sql . "<br>" . $conn->error;  // Muestra un mensaje de error
            }

            break;
        case 'DELETE':
            // Eliminar un registro existente
            $data = json_decode(file_get_contents("php://input"), true); // Obtener los datos enviados en la solicitud
            $id = $data['id']; // Obtener el ID del registro a eliminar

            $sql = "DELETE FROM cec_1 WHERE id=$id"; // Consulta SQL para eliminar el registro
            if ($conn->query($sql) === TRUE) { // Si la consulta se ejecuta correctamente
                echo "Registro eliminado correctamente"; // Muestra un mensaje de éxito
            } else { // Si hay un error en la consulta
                echo json_encode(["error" => "Error: " . $sql . " " . $conn->error]); // Muestra un mensaje de error
            }
            break;

            

        default:
            echo json_encode(["error" => "Método no permitido"]);
            break;
    }

    $conn->close();
?>

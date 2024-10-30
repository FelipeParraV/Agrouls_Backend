<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type");

    include 'connection.php'; // Archivo de conexión a la base de datos

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Ejecutar la consulta SQL y almacenar el resultado en la variable $result
            $sql = "SELECT * FROM perfil_2 ORDER BY orden";
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
                $image_name = null;
                if(isset($_FILES['image'])) { // Verificar si se ha subido un archivo
                    $target_dir = "uploads/"; // Cambia esta ruta a la carpeta donde deseas almacenar las imágenes
                    $target_file = $target_dir . basename($_FILES["image"]["name"]); // Ruta completa del archivo + nombre del archivo
                    $uploadOk = 1;  // Variable de control para verificar si se puede subir el archivo
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION)); // Obtener la extensión del archivo
                    $image_name = basename($_FILES["image"]["name"]); // Obtener solo el nombre del archivo

                    // // Verificar si es una imagen real
                    // $check = getimagesize($_FILES["image"]["tmp_name"]);
                    // if($check !== false) {  // Si es una imagen
                    //     //echo "File is an image - " . $check["mime"] . ".";  // Muestra el tipo de imagen
                    //     $uploadOk = 1;  // Se sube el archivo
                    // } else {    // Si no es una imagen
                    //     echo "File is not an image."; 
                    //     http_response_code(415); // Muestra un mensaje de error
                    //     $uploadOk = 0;  // No se sube el archivo
                    // }

                    // Verificar si el archivo ya existe
                    $file_base_name = pathinfo($target_file, PATHINFO_FILENAME);
                    $file_extension = pathinfo($target_file, PATHINFO_EXTENSION);
                    $counter = 1;
                    while (file_exists($target_file)) {
                        $target_file = $target_dir . $file_base_name . '_' . $counter . '.' . $file_extension;
                        $counter++;
                    }
                    $image_name = basename($target_file);

                    // Verificar el tamaño del archivo
                    if ($_FILES["image"]["size"] > 16000000) { // Si el archivo es mayor a 16MB
                        echo "Sorry, your file is too large.";  // Muestra un mensaje de error
                        http_response_code(417); // Muestra un mensaje de error
                        $uploadOk = 0; // No se sube el archivo
                    }

                    // Verificar si $uploadOk es 0 debido a un error
                    if ($uploadOk == 0) {
                        echo "Sorry, your file was not uploaded.";
                    // Si todo está bien, intenta subir el archivo
                    } else {
                        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) { // Si el archivo se sube correctamente
                            //echo "The file ". htmlspecialchars($image_name). " has been uploaded."; // Muestra un mensaje de éxito
                        } else { // Si hay un error al subir el archivo
                            echo "Sorry, there was an error uploading your file.";
                            $image_name = null;
                        }
                    }
                }

                $sql = "SELECT orden FROM perfil_2 ORDER BY orden DESC LIMIT 1"; // Consulta SQL para obtener el último orden
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $orden = $row['orden'] + 1;
                    echo "Orden: $orden";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }

                // Variables adicionales
                $image_path = $image_name; // Obtiene el nombre del archivo

                // Insertar los datos en la base de datos
                $sql = "INSERT INTO perfil_2 (archivo, orden) VALUES ('$image_path', '$orden')";

                if ($conn->query($sql) === TRUE) { // Si la consulta se ejecuta correctamente
                    echo "Image and data saved to database. Well Done!"; // Muestra un mensaje de éxito
                } else { // Si hay un error en la consulta
                    echo "Error: " . $sql . "<br>" . $conn->error;  // Muestra un mensaje de error
                }
                break;

            } elseif ($action === 'update_image') {
                if(isset($_FILES['image'])) {
                    $target_dir = "uploads/"; // Cambia esta ruta a la carpeta donde deseas almacenar las imágenes
                    $target_file = $target_dir . basename($_FILES["image"]["name"]); // Ruta completa del archivo + nombre del archivo
                    $uploadOk = 1;  // Variable de control para verificar si se puede subir el archivo
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION)); // Obtener la extensión del archivo
                    $image_name = basename($_FILES["image"]["name"]); // Obtener solo el nombre del archivo

                    // // Verificar si es una imagen real
                    // $check = getimagesize($_FILES["image"]["tmp_name"]);
                    // if($check !== false) {  // Si es una imagen
                    //     echo "File is an image - " . $check["mime"] . ".";  // Muestra el tipo de imagen
                    //     $uploadOk = 1;  // Se sube el archivo
                    // } else {    // Si no es una imagen
                    //     echo "File is not an image.";   // Muestra un mensaje de error
                    //     http_response_code(415); // Muestra un mensaje de error
                    //     $uploadOk = 0;  // No se sube el archivo
                    // }

                    // Verificar si el archivo ya existe
                    $file_base_name = pathinfo($target_file, PATHINFO_FILENAME);
                    $file_extension = pathinfo($target_file, PATHINFO_EXTENSION);
                    $counter = 1;
                    while (file_exists($target_file)) {
                        $target_file = $target_dir . $file_base_name . '_' . $counter . '.' . $file_extension;
                        $counter++;
                    }
                    $image_name = basename($target_file);

                    // Verificar el tamaño del archivo
                    if ($_FILES["image"]["size"] > 16000000) { // Si el archivo es mayor a 16MB
                        echo "Sorry, your file is too large.";  // Muestra un mensaje de error
                        http_response_code(417);
                        $uploadOk = 0; // No se sube el archivo
                    }

                    // Verificar si $uploadOk es 0 debido a un error
                    if ($uploadOk == 0) {
                        echo "Sorry, your file was not uploaded.";
                    // Si todo está bien, intenta subir el archivo
                    } else {
                        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) { // Si el archivo se sube correctamente
                            echo "The file ". htmlspecialchars($image_name). " has been uploaded."; // Muestra un mensaje de éxito

                            $id = $_POST['editId'];
                            $image_path = $image_name;

                            // Obtener el nombre de la imagen anterior desde la base de datos
                            $sql = "SELECT archivo FROM perfil_2 WHERE id=$id";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                $old_image = $row['archivo'];

                                echo "Old image: $old_image";

                                // Eliminar la imagen anterior del directorio de almacenamiento
                                if (file_exists($target_dir . $old_image)) {
                                    unlink($target_dir . $old_image);
                                }
                            }
                            
                            echo "Variables: id=$id, imagen=$image_path";
                            // Insertar los datos en la base de datos
                            $sql = "UPDATE perfil_2 SET archivo='$image_path' WHERE id=$id";

                            if ($conn->query($sql) === TRUE) { // Si la consulta se ejecuta correctamente
                                echo "La imagen fue actualizada"; // Muestra un mensaje de éxito
                            } else { // Si hay un error en la consulta
                                echo "Error: " . $sql . "<br>" . $conn->error;  // Muestra un mensaje de error
                            }
                        } else { // Si hay un error al subir el archivo
                            echo "Sorry, there was an error uploading your file.";
                        }
                    }
                } else { // Si no se ha subido un archivo
                    echo "No file uploaded."; // Muestra un mensaje de error
                }
            } elseif ($action === 'update_order') {
                $items = $_POST['items'];
                foreach ($items as $item) {
                    $id = $item['id'];
                    $order = $item['order'];
                    $sql = "UPDATE perfil_2 SET orden = ? WHERE id = ?";
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
            

            break;
        case 'DELETE':
            // Eliminar un registro existente
            $data = json_decode(file_get_contents("php://input"), true); // Obtener los datos enviados en la solicitud
            $id = $data['id']; // Obtener el ID del registro a eliminar
            $imagen = $data['imagen']; // Obtener el nombre de la imagen a eliminar

            $imagePath = $_SERVER['DOCUMENT_ROOT'] . "/agrouls_backend/uploads/" . $imagen; // Ruta completa de la imagen
            if (file_exists($imagePath)) { // Verificar si la imagen existe
                unlink($imagePath); // Eliminar la imagen
                
            }

            $sql = "DELETE FROM perfil_2 WHERE id=$id"; // Consulta SQL para eliminar el registro
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

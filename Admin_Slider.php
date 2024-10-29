<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type");

    include 'connection.php'; // Archivo de conexión a la base de datos

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            // Ejecutar la consulta SQL y almacenar el resultado en la variable $result
            $sql = "SELECT * FROM slider ORDER BY orden";
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
                if(isset($_FILES['image1']) && isset($_FILES['image2'])) { // Verificar si se han subido ambos archivos
                $target_dir = "uploads/"; // Cambia esta ruta a la carpeta donde deseas almacenar las imágenes
                $uploadOk = 1;  // Variable de control para verificar si se pueden subir los archivos
                $image_paths = []; // Array para almacenar las rutas de los archivos subidos

                // Función para procesar cada archivo
                function processFile($file, $target_dir) {
                    $target_file = $target_dir . basename($file["name"]); // Ruta completa del archivo + nombre del archivo
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION)); // Obtener la extensión del archivo
                    $image_name = basename($file["name"]); // Obtener solo el nombre del archivo

                    // Verificar si es una imagen real
                    $check = getimagesize($file["tmp_name"]);
                    if($check !== false) {  // Si es una imagen
                        $uploadOk = 1;  // Se sube el archivo
                    } else {    // Si no es una imagen
                        echo "File is not an image."; 
                        http_response_code(415); // Muestra un mensaje de error
                        $uploadOk = 0;  // No se sube el archivo
                    }

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
                    if ($file["size"] > 16000000) { // Si el archivo es mayor a 16MB
                        echo "Sorry, your file is too large.";  // Muestra un mensaje de error
                        http_response_code(417); // Muestra un mensaje de error
                        $uploadOk = 0; // No se sube el archivo
                    }

                    // Verificar si $uploadOk es 0 debido a un error
                    if ($uploadOk == 0) {
                        echo "Sorry, your file was not uploaded.";
                        return null;
                    // Si todo está bien, intenta subir el archivo
                    } else {
                        if (move_uploaded_file($file["tmp_name"], $target_file)) { // Si el archivo se sube correctamente
                            return $image_name; // Retorna el nombre del archivo subido
                        } else { // Si hay un error al subir el archivo
                            echo "Sorry, there was an error uploading your file.";
                            return null;
                        }
                    }
                }

                // Procesar ambos archivos
                $image1_name = processFile($_FILES['image1'], $target_dir);
                $image2_name = processFile($_FILES['image2'], $target_dir);

                if ($image1_name && $image2_name) {
                    $sql = "SELECT orden FROM slider ORDER BY orden DESC LIMIT 1"; // Consulta SQL para obtener el último orden

                    $result = $conn->query($sql);
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        $orden = $row['orden'] + 1;
                    } else {
                        echo "Error: " . $sql . "<br>" . $conn->error;
                    }

                    // Variables adicionales
                    $titulo = $_POST['titulo']; // Obtiene el título de la noticia

                    // Insertar los datos en la base de datos
                    $sql = "INSERT INTO slider (titulo, slider, slider2, orden) VALUES ('$titulo', '$image1_name', '$image2_name', '$orden')";

                    if ($conn->query($sql) === TRUE) { // Si la consulta se ejecuta correctamente
                        echo "Image and data saved to database. Well Done!"; // Muestra un mensaje de éxito
                    } else { // Si hay un error en la consulta
                        echo "Error: " . $sql . "<br>" . $conn->error;  // Muestra un mensaje de error
                    }
                }
            } else { // Si no se han subido ambos archivos
                echo "No files uploaded."; // Muestra un mensaje de error
            }
                break;

            } elseif ($action === 'update_image1') {
                if(isset($_FILES['image1'])) {
                    $target_dir = "uploads/"; // Cambia esta ruta a la carpeta donde deseas almacenar las imágenes
                    $target_file = $target_dir . basename($_FILES["image1"]["name"]); // Ruta completa del archivo + nombre del archivo
                    $uploadOk = 1;  // Variable de control para verificar si se puede subir el archivo
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION)); // Obtener la extensión del archivo
                    $image_name = basename($_FILES["image1"]["name"]); // Obtener solo el nombre del archivo

                    // Verificar si es una imagen real
                    $check = getimagesize($_FILES["image1"]["tmp_name"]);
                    if($check !== false) {  // Si es una imagen
                        echo "File is an image - " . $check["mime"] . ".";  // Muestra el tipo de imagen
                        $uploadOk = 1;  // Se sube el archivo
                    } else {    // Si no es una imagen
                        echo "File is not an image.";   // Muestra un mensaje de error
                        http_response_code(415); // Muestra un mensaje de error
                        $uploadOk = 0;  // No se sube el archivo
                    }

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
                    if ($_FILES["image1"]["size"] > 16000000) { // Si el archivo es mayor a 16MB
                        echo "Sorry, your file is too large.";  // Muestra un mensaje de error
                        http_response_code(417);
                        $uploadOk = 0; // No se sube el archivo
                    }

                    // Verificar si $uploadOk es 0 debido a un error
                    if ($uploadOk == 0) {
                        echo "Sorry, your file was not uploaded.";
                    // Si todo está bien, intenta subir el archivo
                    } else {
                        if (move_uploaded_file($_FILES["image1"]["tmp_name"], $target_file)) { // Si el archivo se sube correctamente
                            echo "The file ". htmlspecialchars($image_name). " has been uploaded."; // Muestra un mensaje de éxito

                            $id = $_POST['editId'];
                            $image_path = $image_name;

                            // Obtener el nombre de la imagen anterior desde la base de datos
                            $sql = "SELECT slider FROM slider WHERE id=$id";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                $old_image = $row['imagen'];

                                // Eliminar la imagen anterior del directorio de almacenamiento
                                if (file_exists($target_dir . $old_image)) {
                                    unlink($target_dir . $old_image);
                                }
                            }
                            
                            // Insertar los datos en la base de datos
                            $sql = "UPDATE slider SET slider='$image_path' WHERE id=$id";

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
            } elseif ($action === 'update_image2') {
                if(isset($_FILES['image2'])) {
                    $target_dir = "uploads/"; // Cambia esta ruta a la carpeta donde deseas almacenar las imágenes
                    $target_file = $target_dir . basename($_FILES["image2"]["name"]); // Ruta completa del archivo + nombre del archivo
                    $uploadOk = 1;  // Variable de control para verificar si se puede subir el archivo
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION)); // Obtener la extensión del archivo
                    $image_name = basename($_FILES["image2"]["name"]); // Obtener solo el nombre del archivo

                    // Verificar si es una imagen real
                    $check = getimagesize($_FILES["image2"]["tmp_name"]);
                    if($check !== false) {  // Si es una imagen
                        echo "File is an image - " . $check["mime"] . ".";  // Muestra el tipo de imagen
                        $uploadOk = 1;  // Se sube el archivo
                    } else {    // Si no es una imagen
                        echo "File is not an image.";   // Muestra un mensaje de error
                        http_response_code(415); // Muestra un mensaje de error
                        $uploadOk = 0;  // No se sube el archivo
                    }

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
                    if ($_FILES["image2"]["size"] > 16000000) { // Si el archivo es mayor a 16MB
                        echo "Sorry, your file is too large.";  // Muestra un mensaje de error
                        http_response_code(417);
                        $uploadOk = 0; // No se sube el archivo
                    }

                    // Verificar si $uploadOk es 0 debido a un error
                    if ($uploadOk == 0) {
                        echo "Sorry, your file was not uploaded.";
                    // Si todo está bien, intenta subir el archivo
                    } else {
                        if (move_uploaded_file($_FILES["image2"]["tmp_name"], $target_file)) { // Si el archivo se sube correctamente
                            echo "The file ". htmlspecialchars($image_name). " has been uploaded."; // Muestra un mensaje de éxito

                            $id = $_POST['editId'];
                            $image_path = $image_name;

                            // Obtener el nombre de la imagen anterior desde la base de datos
                            $sql = "SELECT slider2 FROM slider WHERE id=$id";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                $old_image = $row['imagenCelular'];

                                // Eliminar la imagen anterior del directorio de almacenamiento
                                if (file_exists($target_dir . $old_image)) {
                                    unlink($target_dir . $old_image);
                                }
                            }
                            
                            // Insertar los datos en la base de datos
                            $sql = "UPDATE slider SET slider2='$image_path' WHERE id=$id";

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
            } elseif ($action === 'update_image3') {
                if(isset($_FILES['image1']) && isset($_FILES['image1'])) {
                    $target_dir = "uploads/"; // Cambia esta ruta a la carpeta donde deseas almacenar las imágenes
                    $target_file1 = $target_dir . basename($_FILES["image1"]["name"]); // Ruta completa del archivo + nombre del archivo
                    $uploadOk1 = 1;  // Variable de control para verificar si se puede subir el archivo
                    $imageFileType = strtolower(pathinfo($target_file1, PATHINFO_EXTENSION)); // Obtener la extensión del archivo
                    $image_name1 = basename($_FILES["image1"]["name"]); // Obtener solo el nombre del archivo

                    // Verificar si es una imagen real
                    $check = getimagesize($_FILES["image1"]["tmp_name"]);
                    if($check !== false) {  // Si es una imagen
                        echo "File is an image - " . $check["mime"] . ".";  // Muestra el tipo de imagen
                        $uploadOk1 = 1;  // Se sube el archivo
                    } else {    // Si no es una imagen
                        echo "File is not an image.";   // Muestra un mensaje de error
                        http_response_code(415); // Muestra un mensaje de error
                        $uploadOk1 = 0;  // No se sube el archivo
                    }

                    // Verificar si el archivo ya existe
                    $file_base_name = pathinfo($target_file1, PATHINFO_FILENAME);
                    $file_extension = pathinfo($target_file1, PATHINFO_EXTENSION);
                    $counter = 1;
                    while (file_exists($target_file1)) {
                        $target_file1 = $target_dir . $file_base_name . '_' . $counter . '.' . $file_extension;
                        $counter++;
                    }
                    $image_name1 = basename($target_file1);

                    // Verificar el tamaño del archivo
                    if ($_FILES["image1"]["size"] > 16000000) { // Si el archivo es mayor a 16MB
                        echo "Sorry, your file is too large.";  // Muestra un mensaje de error
                        http_response_code(417);
                        $uploadOk1 = 0; // No se sube el archivo
                    }
                    
//----------------------------------------------------------------------------------------------------------

                    $target_dir = "uploads/"; // Cambia esta ruta a la carpeta donde deseas almacenar las imágenes
                    $target_file2 = $target_dir . basename($_FILES["image2"]["name"]); // Ruta completa del archivo + nombre del archivo
                    $uploadOk2 = 1;  // Variable de control para verificar si se puede subir el archivo
                    $imageFileType = strtolower(pathinfo($target_file2, PATHINFO_EXTENSION)); // Obtener la extensión del archivo
                    $image_name2 = basename($_FILES["image2"]["name"]); // Obtener solo el nombre del archivo

                    // Verificar si es una imagen real
                    $check = getimagesize($_FILES["image2"]["tmp_name"]);
                    if($check !== false) {  // Si es una imagen
                        echo "File is an image - " . $check["mime"] . ".";  // Muestra el tipo de imagen
                        $uploadOk2 = 1;  // Se sube el archivo
                    } else {    // Si no es una imagen
                        echo "File is not an image.";   // Muestra un mensaje de error
                        http_response_code(415); // Muestra un mensaje de error
                        $uploadOk2 = 0;  // No se sube el archivo
                    }

                    // Verificar si el archivo ya existe
                    $file_base_name = pathinfo($target_file2, PATHINFO_FILENAME);
                    $file_extension = pathinfo($target_file2, PATHINFO_EXTENSION);
                    $counter = 1;
                    while (file_exists($target_file2)) {
                        $target_file2 = $target_dir . $file_base_name . '_' . $counter . '.' . $file_extension;
                        $counter++;
                    }
                    $image_name2 = basename($target_file2);

                    // Verificar el tamaño del archivo
                    if ($_FILES["image2"]["size"] > 16000000) { // Si el archivo es mayor a 16MB
                        echo "Sorry, your file is too large.";  // Muestra un mensaje de error
                        http_response_code(417);
                        $uploadOk2 = 0; // No se sube el archivo
                    }

                    // Verificar si $uploadOk es 0 debido a un error
                    if ($uploadOk2 == 1 && $uploadOk1 == 1) {
                        // Si todo está bien, intenta subir el archivo
                        if (move_uploaded_file($_FILES["image1"]["tmp_name"], $target_file1) && move_uploaded_file($_FILES["image2"]["tmp_name"], $target_file2)) { // Si el archivo se sube correctamente
                            echo "The file ". htmlspecialchars($image_name1). " has been uploaded."; // Muestra un mensaje de éxito
                            echo "The file ". htmlspecialchars($image_name2). " has been uploaded."; // Muestra un mensaje de éxito

                            $id = $_POST['editId'];
                            $image_path1 = $image_name1;
                            $image_path2 = $image_name2;

                            echo $image_path1;
                            echo $image_path2;

                            // Obtener el nombre de la imagen anterior desde la base de datos
                            $sql = "SELECT slider FROM slider WHERE id=$id";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                $old_image1 = $row['imagen'];

                                // Eliminar la imagen anterior del directorio de almacenamiento
                                if (file_exists($target_dir . $old_image1)) {
                                    unlink($target_dir . $old_image1);
                                }
                            }
                            
                            // Insertar los datos en la base de datos
                            $sql = "UPDATE slider SET slider='$image_path1' WHERE id=$id";

                            if ($conn->query($sql) === TRUE) { // Si la consulta se ejecuta correctamente
                                echo "La imagen fue actualizada"; // Muestra un mensaje de éxito
                            } else { // Si hay un error en la consulta
                                echo "Error: " . $sql . "<br>" . $conn->error;  // Muestra un mensaje de error
                            }

                            // Obtener el nombre de la imagen anterior desde la base de datos
                            $sql = "SELECT slider2 FROM slider WHERE id=$id";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0) {
                                $row = $result->fetch_assoc();
                                $old_image2 = $row['imagenCelular'];

                                // Eliminar la imagen anterior del directorio de almacenamiento
                                if (file_exists($target_dir . $old_image2)) {
                                    unlink($target_dir . $old_image2);
                                }
                            }
                            
                            // Insertar los datos en la base de datos
                            $sql = "UPDATE slider SET slider2='$image_path2' WHERE id=$id";

                            if ($conn->query($sql) === TRUE) { // Si la consulta se ejecuta correctamente
                                echo "La imagen fue actualizada"; // Muestra un mensaje de éxito
                            } else { // Si hay un error en la consulta
                                echo "Error: " . $sql . "<br>" . $conn->error;  // Muestra un mensaje de error
                            }
                        } else { // Si hay un error al subir el archivo
                            echo "Sorry, there was an error uploading your file.";
                        }
                    } else {
                        echo "Sorry, your file was not uploaded.";
                    }
                } else { // Si no se ha subido un archivo
                    echo "No file uploaded."; // Muestra un mensaje de error
                }
            } elseif ($action === 'update_order') {
                $items = $_POST['items'];
                foreach ($items as $item) {
                    $id = $item['id'];
                    $order = $item['order'];
                    $sql = "UPDATE slider SET orden = ? WHERE id = ?";
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
            $titulo = $_PUT['editTitulo']; // Obtiene el título de la noticia


            $sql = "UPDATE slider SET titulo='$titulo' WHERE id=$id";

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
            $imagen1 = $data['image']; // Obtener el nombre de la imagen a eliminar
            $imagen2 = $data['imageCelular']; // Obtener el nombre de la imagen a eliminar

            $imagePath1 = $_SERVER['DOCUMENT_ROOT'] . "/agrouls_backend/uploads/" . $imagen1; // Ruta completa de la imagen
            if (file_exists($imagePath1)) { // Verificar si la imagen existe
                unlink($imagePath1); // Eliminar la imagen
                
            }

            $imagePath2 = $_SERVER['DOCUMENT_ROOT'] . "/agrouls_backend/uploads/" . $imagen2; // Ruta completa de la imagen
            if (file_exists($imagePath2)) { // Verificar si la imagen existe
                unlink($imagePath2); // Eliminar la imagen
                
            }

            $sql = "DELETE FROM slider WHERE id=$id"; // Consulta SQL para eliminar el registro
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

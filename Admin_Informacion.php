<?php
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
    header("Access-Control-Allow-Headers: Content-Type");

    include 'connection.php';

    $method = $_SERVER['REQUEST_METHOD'];

    switch ($method) {
        case 'GET':
            $sql = "SELECT * FROM informacion ORDER BY orden";
            $result = $conn->query($sql);
            $rows = array();
            while($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            echo json_encode($rows);
            break;

        case 'POST':

            $action = $_POST['action'] ?? 'new';

            if ($action === 'new') {
                $sql = "SELECT orden FROM informacion ORDER BY orden DESC LIMIT 1";

                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $orden = $row['orden'] + 1;
                    echo "Orden: $orden";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }

                $titulo = $_POST['titulo'];
                $informacion = $_POST['informacion'];

                $sql = "INSERT INTO informacion (titulo, informacion, orden) VALUES ('$titulo','$informacion', '$orden')";

                if ($conn->query($sql) === TRUE) {
                    echo "Image and data saved to database. Well Done!";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }

            } elseif ($action === 'update_order') {
                $items = $_POST['items'];
                foreach ($items as $item) {
                    $id = $item['id'];
                    $order = $item['order'];
                    $sql = "UPDATE informacion SET orden = ? WHERE id = ?";
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
            $_PUT = json_decode(file_get_contents("php://input"), true);

            $id = $_PUT['editId'];
            $titulo = $_PUT['editTitulo'];
            $informacion = $_PUT['editInformacion'];


            $sql = "UPDATE informacion SET titulo='$titulo', informacion='$informacion' WHERE id=$id";

            if ($conn->query($sql) === TRUE) {
                echo "Datos actualizados";
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }

            break;
        case 'DELETE':
            $data = json_decode(file_get_contents("php://input"), true);
            $id = $data['id'];

            $sql = "DELETE FROM informacion WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                echo "Registro eliminado correctamente";
            } else {
                echo json_encode(["error" => "Error: " . $sql . " " . $conn->error]);
            }
            break;

        default:
            echo json_encode(["error" => "Método no permitido"]);
            break;
    }

    $conn->close();
?>

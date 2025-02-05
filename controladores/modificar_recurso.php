<?php
session_start();
require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->begin_transaction();

        $modulo_id = $_POST['modulo_id'];

        // Get the curso_id for redirection
        $stmt = $db->prepare("SELECT curso_id FROM modulos WHERE id = ?");
        $stmt->bind_param("i", $modulo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $curso_id = $result->fetch_assoc()['curso_id'];

        // Procesar recursos existentes
        if (isset($_POST['recurso_ids'])) {
            foreach ($_POST['recurso_ids'] as $i => $recurso_id) {
                $tipo = $_POST['recurso_tipos'][$i];
                $descripcion = $_POST['recurso_descripciones'][$i];
                $url = null;
                $archivo_path = null;

                if ($tipo === 'url') {
                    $url = $_POST['recurso_urls'][$i];
                } else {
                    // Procesar archivo
                    if (
                        isset($_FILES['recurso_archivos']['name'][$i]) &&
                        $_FILES['recurso_archivos']['error'][$i] === 0
                    ) {
                        $archivo = [
                            'name' => $_FILES['recurso_archivos']['name'][$i],
                            'type' => $_FILES['recurso_archivos']['type'][$i],
                            'tmp_name' => $_FILES['recurso_archivos']['tmp_name'][$i],
                            'error' => $_FILES['recurso_archivos']['error'][$i],
                            'size' => $_FILES['recurso_archivos']['size'][$i]
                        ];

                        $nombre_archivo = uniqid() . '_' . basename($archivo['name']);
                        $ruta_archivo = '../uploads/recursos/' . $nombre_archivo;

                        if (move_uploaded_file($archivo['tmp_name'], $ruta_archivo)) {
                            $archivo_path = 'uploads/recursos/' . $nombre_archivo;

                            // Eliminar archivo anterior si existe
                            $stmt = $db->prepare("SELECT archivo_path FROM recursos_modulo WHERE id = ?");
                            $stmt->bind_param("i", $recurso_id);
                            $stmt->execute();
                            $resultado = $stmt->get_result();
                            if ($archivo_anterior = $resultado->fetch_assoc()) {
                                if ($archivo_anterior['archivo_path'] && file_exists('../' . $archivo_anterior['archivo_path'])) {
                                    unlink('../' . $archivo_anterior['archivo_path']);
                                }
                            }
                        }
                    }
                }

                if ($recurso_id === 'nuevo') {
                    // Insertar nuevo recurso
                    $stmt = $db->prepare("INSERT INTO recursos_modulo (modulo_id, tipo, descripcion, url, archivo_path) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("issss", $modulo_id, $tipo, $descripcion, $url, $archivo_path);
                } else {
                    // Actualizar recurso existente
                    $stmt = $db->prepare("UPDATE recursos_modulo SET tipo = ?, descripcion = ?, url = ?, archivo_path = ? WHERE id = ?");
                    $stmt->bind_param("ssssi", $tipo, $descripcion, $url, $archivo_path, $recurso_id);
                }
                $stmt->execute();
            }
        }

        // Eliminar recursos que ya no existen
        if (isset($_POST['recursos_eliminados'])) {
            $recursos_eliminados = explode(',', $_POST['recursos_eliminados']);
            foreach ($recursos_eliminados as $recurso_id) {
                // Eliminar archivo físico si existe
                $stmt = $db->prepare("SELECT archivo_path FROM recursos_modulo WHERE id = ?");
                $stmt->bind_param("i", $recurso_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    if ($row['archivo_path'] && file_exists('../' . $row['archivo_path'])) {
                        unlink('../' . $row['archivo_path']);
                    }
                }

                // Eliminar registro de la base de datos
                $stmt = $db->prepare("DELETE FROM recursos_modulo WHERE id = ?");
                $stmt->bind_param("i", $recurso_id);
                $stmt->execute();
            }
        }

        $db->commit();
        $_SESSION['swal_success'] = "Recursos actualizados correctamente";
    } catch (Exception $e) {
        $db->rollback();
        $_SESSION['swal_error'] = "Error al actualizar los recursos: " . $e->getMessage();
    }

    // Redirect to editar-curso.php with the corresponding curso_id
    header("Location: ../editar-curso.php?id=" . $curso_id);
    exit;
}

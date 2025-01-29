<?php
session_start();
require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $db->begin_transaction();

        // Obtener datos básicos del curso
        $curso_id = $_POST['curso_id'];
        $titulo = trim($_POST['titulo']);
        $descripcion = trim($_POST['descripcion']);
        $cantidad_horas = $_POST['cantidad_horas'];
        $anio = (int)$_POST['anio'];
        $form_insc = trim($_POST['form_insc']);

        // Crear directorios si no existen
        $directorios = ['../uploads/cursos', '../uploads/programas', '../uploads/recursos'];
        foreach ($directorios as $dir) {
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        // Procesar nueva imagen si se proporcionó
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $imagen = $_FILES['imagen'];
            $nombre_imagen = uniqid() . '_' . basename($imagen['name']);
            $ruta_imagen = '../uploads/cursos/' . $nombre_imagen;

            if (move_uploaded_file($imagen['tmp_name'], $ruta_imagen)) {
                // Eliminar imagen anterior
                $stmt = $db->prepare("SELECT imagen_path FROM cursos WHERE id = ?");
                $stmt->bind_param("i", $curso_id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                if ($imagen_anterior = $resultado->fetch_assoc()) {
                    if ($imagen_anterior['imagen_path'] && file_exists('../' . $imagen_anterior['imagen_path'])) {
                        unlink('../' . $imagen_anterior['imagen_path']);
                    }
                }
                $imagen_path = 'uploads/cursos/' . $nombre_imagen;
            }
        }

        // Procesar nuevo programa PDF si se proporcionó
        if (isset($_FILES['programa']) && $_FILES['programa']['error'] == 0) {
            $programa = $_FILES['programa'];
            $nombre_programa = uniqid() . '_' . basename($programa['name']);
            $ruta_programa = '../uploads/programas/' . $nombre_programa;

            if (move_uploaded_file($programa['tmp_name'], $ruta_programa)) {
                // Eliminar programa anterior
                $stmt = $db->prepare("SELECT programa_pdf_path FROM cursos WHERE id = ?");
                $stmt->bind_param("i", $curso_id);
                $stmt->execute();
                $resultado = $stmt->get_result();
                if ($programa_anterior = $resultado->fetch_assoc()) {
                    if ($programa_anterior['programa_pdf_path'] && file_exists('../' . $programa_anterior['programa_pdf_path'])) {
                        unlink('../' . $programa_anterior['programa_pdf_path']);
                    }
                }
                $programa_path = 'uploads/programas/' . $nombre_programa;
            }
        }

        // Actualizar datos básicos del curso
        $sql = "UPDATE cursos SET 
                titulo = ?, 
                descripcion = ?, 
                cantidad_horas = ?, 
                anio = ?, 
                form_insc = ?";
        $params = [$titulo, $descripcion, $cantidad_horas, $anio, $form_insc];
        $types = "sssis";

        if (isset($imagen_path)) {
            $sql .= ", imagen_path = ?";
            $params[] = $imagen_path;
            $types .= "s";
        }

        if (isset($programa_path)) {
            $sql .= ", programa_pdf_path = ?";
            $params[] = $programa_path;
            $types .= "s";
        }

        $sql .= " WHERE id = ?";
        $params[] = $curso_id;
        $types .= "i";

        $stmt = $db->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        // Procesar módulos
        $modulos_actuales = [];
        $stmt = $db->prepare("SELECT id FROM modulos WHERE curso_id = ?");
        $stmt->bind_param("i", $curso_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $modulos_actuales[] = $row['id'];
        }

        $modulos_a_mantener = [];

        if (isset($_POST['modulo_ids'])) {
            foreach ($_POST['modulo_ids'] as $index => $modulo_id) {
                $titulo_modulo = $_POST['modulo_titulos'][$index];
                $descripcion_modulo = $_POST['modulo_descripciones'][$index];

                if ($modulo_id === 'nuevo') {
                    // Insertar nuevo módulo
                    $stmt = $db->prepare("INSERT INTO modulos (curso_id, titulo, descripcion) VALUES (?, ?, ?)");
                    $stmt->bind_param("iss", $curso_id, $titulo_modulo, $descripcion_modulo);
                    $stmt->execute();
                    $modulo_id = $db->insert_id;
                } else {
                    // Actualizar módulo existente
                    $stmt = $db->prepare("UPDATE modulos SET titulo = ?, descripcion = ? WHERE id = ?");
                    $stmt->bind_param("ssi", $titulo_modulo, $descripcion_modulo, $modulo_id);
                    $stmt->execute();
                }

                $modulos_a_mantener[] = $modulo_id;

                // Procesar recursos del módulo
                if (isset($_POST['recurso_ids'][$modulo_id])) {
                    $recursos_actuales = [];
                    $stmt = $db->prepare("SELECT id FROM recursos_modulo WHERE modulo_id = ?");
                    $stmt->bind_param("i", $modulo_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    while ($row = $result->fetch_assoc()) {
                        $recursos_actuales[] = $row['id'];
                    }

                    $recursos_a_mantener = [];

                    foreach ($_POST['recurso_ids'][$modulo_id] as $i => $recurso_id) {
                        $tipo = $_POST['recurso_tipos'][$modulo_id][$i];
                        $descripcion_recurso = $_POST['recurso_descripciones'][$modulo_id][$i];
                        $url = null;
                        $archivo_path = null;

                        if ($tipo === 'url') {
                            $url = $_POST['recurso_urls'][$modulo_id][$i];
                        } else {
                            // Procesar archivo
                            if (
                                isset($_FILES['recurso_archivos']['name'][$modulo_id][$i]) &&
                                $_FILES['recurso_archivos']['error'][$modulo_id][$i] === 0
                            ) {

                                $archivo = [
                                    'name' => $_FILES['recurso_archivos']['name'][$modulo_id][$i],
                                    'type' => $_FILES['recurso_archivos']['type'][$modulo_id][$i],
                                    'tmp_name' => $_FILES['recurso_archivos']['tmp_name'][$modulo_id][$i],
                                    'error' => $_FILES['recurso_archivos']['error'][$modulo_id][$i],
                                    'size' => $_FILES['recurso_archivos']['size'][$modulo_id][$i]
                                ];

                                $nombre_archivo = uniqid() . '_' . basename($archivo['name']);
                                $ruta_archivo = '../uploads/recursos/' . $nombre_archivo;

                                if (move_uploaded_file($archivo['tmp_name'], $ruta_archivo)) {
                                    $archivo_path = 'uploads/recursos/' . $nombre_archivo;
                                }
                            }
                        }

                        if ($recurso_id === 'nuevo') {
                            // Insertar nuevo recurso
                            $stmt = $db->prepare("INSERT INTO recursos_modulo (modulo_id, tipo, descripcion, url, archivo_path) VALUES (?, ?, ?, ?, ?)");
                            $stmt->bind_param("issss", $modulo_id, $tipo, $descripcion_recurso, $url, $archivo_path);
                            $stmt->execute();
                            $recurso_id = $db->insert_id;
                        } else {
                            // Actualizar recurso existente
                            $sql = "UPDATE recursos_modulo SET tipo = ?, descripcion = ?";
                            $params = [$tipo, $descripcion_recurso];
                            $types = "ss";

                            if ($tipo === 'url') {
                                $sql .= ", url = ?, archivo_path = NULL";
                                $params[] = $url;
                                $types .= "s";
                            } elseif ($archivo_path) {
                                $sql .= ", url = NULL, archivo_path = ?";
                                $params[] = $archivo_path;
                                $types .= "s";
                            }

                            $sql .= " WHERE id = ?";
                            $params[] = $recurso_id;
                            $types .= "i";

                            $stmt = $db->prepare($sql);
                            $stmt->bind_param($types, ...$params);
                            $stmt->execute();
                        }

                        $recursos_a_mantener[] = $recurso_id;
                    }

                    // Eliminar recursos que ya no existen
                    $recursos_a_eliminar = array_diff($recursos_actuales, $recursos_a_mantener);
                    foreach ($recursos_a_eliminar as $recurso_id) {
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
            }
        }

        // Eliminar módulos que ya no existen
        $modulos_a_eliminar = array_diff($modulos_actuales, $modulos_a_mantener);
        foreach ($modulos_a_eliminar as $modulo_id) {
            // Eliminar recursos del módulo
            $stmt = $db->prepare("SELECT archivo_path FROM recursos_modulo WHERE modulo_id = ?");
            $stmt->bind_param("i", $modulo_id);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                if ($row['archivo_path'] && file_exists('../' . $row['archivo_path'])) {
                    unlink('../' . $row['archivo_path']);
                }
            }

            // Eliminar registros de recursos
            $stmt = $db->prepare("DELETE FROM recursos_modulo WHERE modulo_id = ?");
            $stmt->bind_param("i", $modulo_id);
            $stmt->execute();

            // Eliminar módulo
            $stmt = $db->prepare("DELETE FROM modulos WHERE id = ?");
            $stmt->bind_param("i", $modulo_id);
            $stmt->execute();
        }

        $db->commit();
        $_SESSION['mensaje'] = "Curso actualizado correctamente";
        $_SESSION['tipo_mensaje'] = "success";
    } catch (Exception $e) {
        $db->rollback();
        $_SESSION['mensaje'] = "Error al actualizar el curso: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";

        // Limpiar archivos subidos en caso de error
        if (isset($ruta_imagen) && file_exists($ruta_imagen)) {
            unlink($ruta_imagen);
        }
        if (isset($ruta_programa) && file_exists($ruta_programa)) {
            unlink($ruta_programa);
        }
        if (isset($ruta_archivo) && file_exists($ruta_archivo)) {
            unlink($ruta_archivo);
        }
    }

    header('Location: ../admin-cursos.php');
    exit;
}

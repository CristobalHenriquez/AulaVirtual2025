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
        $directorios = ['../uploads/cursos', '../uploads/programas'];
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
            }
        }

        // Eliminar módulos que ya no existen
        $modulos_a_eliminar = array_diff($modulos_actuales, $modulos_a_mantener);
        foreach ($modulos_a_eliminar as $modulo_id) {
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
    }

    header('Location: ../admin-cursos.php');
    exit;
}
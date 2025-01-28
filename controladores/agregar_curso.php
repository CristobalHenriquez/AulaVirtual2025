<?php
session_start();
require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Obtener los datos del formulario
        $titulo = trim($_POST['titulo']);
        $descripcion = trim($_POST['descripcion']);
        $cantidad_horas = $_POST['cantidad_horas'];
        $anio = (int)$_POST['anio'];
        $form_insc = trim($_POST['form_insc']);
        
        $imagen_path = null;
        $programa_path = null;
        
        // Procesar la imagen
        if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
            $imagen = $_FILES['imagen'];
            $nombre_imagen = uniqid() . '_' . basename($imagen['name']);
            $directorio_destino = '../uploads/cursos/';
            
            if (!file_exists($directorio_destino)) {
                mkdir($directorio_destino, 0777, true);
            }
            
            $ruta_imagen = $directorio_destino . $nombre_imagen;
            
            if (move_uploaded_file($imagen['tmp_name'], $ruta_imagen)) {
                $imagen_path = 'uploads/cursos/' . $nombre_imagen;
            } else {
                throw new Exception("Error al subir la imagen");
            }
        }

        // Procesar el programa PDF
        if (isset($_FILES['programa']) && $_FILES['programa']['error'] == 0) {
            $programa = $_FILES['programa'];
            $nombre_programa = uniqid() . '_' . basename($programa['name']);
            $directorio_destino = '../uploads/programas/';
            
            if (!file_exists($directorio_destino)) {
                mkdir($directorio_destino, 0777, true);
            }
            
            $ruta_programa = $directorio_destino . $nombre_programa;
            
            if (move_uploaded_file($programa['tmp_name'], $ruta_programa)) {
                $programa_path = 'uploads/programas/' . $nombre_programa;
            } else {
                throw new Exception("Error al subir el programa");
            }
        }

        // Preparar la consulta SQL
        $sql = "INSERT INTO cursos (titulo, descripcion, imagen_path, programa_pdf_path, form_insc, cantidad_horas, anio) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $db->prepare($sql)) {
            $stmt->bind_param("ssssssi", 
                $titulo, 
                $descripcion, 
                $imagen_path, 
                $programa_path, 
                $form_insc, 
                $cantidad_horas, 
                $anio
            );
            
            if ($stmt->execute()) {
                $_SESSION['mensaje'] = "Curso agregado correctamente";
                $_SESSION['tipo_mensaje'] = "success";
            } else {
                throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
            }
            
            $stmt->close();
        } else {
            throw new Exception("Error en la preparación de la consulta: " . $db->error);
        }
        
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error: " . $e->getMessage();
        $_SESSION['tipo_mensaje'] = "danger";
        
        // Limpiar archivos subidos en caso de error
        if (isset($ruta_imagen) && file_exists($ruta_imagen)) {
            unlink($ruta_imagen);
        }
        if (isset($ruta_programa) && file_exists($ruta_programa)) {
            unlink($ruta_programa);
        }
    }
    
    // Redireccionar
    header('Location: ../admin-cursos.php');
    exit;
}
?>
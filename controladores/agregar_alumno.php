<?php
session_start();
require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $dni = $_POST['dni'] ?: null;
    $municipio = $_POST['municipio'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];
    $ramcc = isset($_POST['ramcc']) ? 1 : 0;
    $cursos = isset($_POST['cursos']) ? $_POST['cursos'] : [];

    try {
        // Iniciar transacción
        $db->begin_transaction();

        // Verificar si el email ya existe
        $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("El email ya está registrado.");
        }

        // Insertar el nuevo usuario
        $stmt = $db->prepare("
            INSERT INTO usuarios (nombre, apellidos, dni, municipio, email, password, rol, estado, ramcc)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'activo', ?)
        ");
        $stmt->bind_param("sssssssi", $nombre, $apellidos, $dni, $municipio, $email, $password, $rol, $ramcc);
        $stmt->execute();
        
        $usuario_id = $db->insert_id;

        // Insertar inscripciones a cursos si se seleccionaron
        if (!empty($cursos)) {
            $stmt = $db->prepare("
                INSERT INTO inscripciones (usuario_id, curso_id)
                VALUES (?, ?)
            ");
            foreach ($cursos as $curso_id) {
                $stmt->bind_param("ii", $usuario_id, $curso_id);
                $stmt->execute();
            }
        }

        // Confirmar transacción
        $db->commit();

        $_SESSION['swal_success'] = "Alumno agregado exitosamente.";
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        $db->rollback();
        $_SESSION['swal_error'] = "Error al agregar el alumno: " . $e->getMessage();
    }

    // Redireccionar de vuelta a la página de administración de alumnos
    header('Location: ../admin-alumnos.php');
    exit;
}
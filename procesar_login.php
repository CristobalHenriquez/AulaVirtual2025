<?php
session_start();
require_once 'includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $sql = "SELECT id, email, password, rol, nombre FROM usuarios WHERE email = ?";
    
    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            
            if (password_verify($password, $usuario['password'])) {
                // Iniciar sesión y guardar datos del usuario
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $usuario['id'];
                $_SESSION['email'] = $usuario['email'];
                $_SESSION['rol'] = $usuario['rol'];
                $_SESSION['nombre'] = $usuario['nombre'];

                // Preparar respuesta JSON
                $response = [
                    'success' => true,
                    'redirect' => $usuario['rol'] === 'Administrador' ? 'admin.php' : 'student.php'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'La contraseña ingresada es incorrecta'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'No existe una cuenta con ese correo electrónico'
            ];
        }
        $stmt->close();
    } else {
        $response = [
            'success' => false,
            'message' => 'Error en el servidor'
        ];
    }
    $db->close();

    // Enviar respuesta JSON
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // Si no es una solicitud POST, redirigir a la página de inicio de sesión
    header('Location: login.php');
    exit;
}
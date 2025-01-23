<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Incluir archivo de conexión
    include_once 'includes/head.php';

    // Obtener y limpiar datos del formulario
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Consulta preparada para prevenir SQL injection
    $sql = "SELECT id, email, password, rol FROM usuarios WHERE email = ?";
    
    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();
            
            // Verificar contraseña
            if (password_verify($password, $usuario['password'])) {
                // Iniciar sesión y guardar datos del usuario
                $_SESSION['loggedin'] = true;
                $_SESSION['id'] = $usuario['id'];
                $_SESSION['email'] = $usuario['email'];
                $_SESSION['rol'] = $usuario['rol'];

                // Redireccionar según el rol
                if ($usuario['rol'] === 'Administrador') {
                    header("location: admin.php");
                } else {
                    header("location: student.php");
                }
                exit;
            } else {
                $error = "La contraseña no es válida";
            }
        } else {
            $error = "No se encontró una cuenta con ese correo electrónico";
        }
        $stmt->close();
    }
    $db->close();
}
?>
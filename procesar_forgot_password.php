<?php
require_once 'includes/conexion.php';
require_once 'includes/password_reset_functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    // Verificar si el correo existe
    $sql = "SELECT id FROM usuarios WHERE email = ?";
    
    if ($stmt = $db->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // Generar token y fecha de expiración
            $token = generateResetToken();
            $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Actualizar la base de datos con el token
            $updateSql = "UPDATE usuarios SET password_reset_token = ?, password_reset_expiration = ? WHERE email = ?";
            if ($updateStmt = $db->prepare($updateSql)) {
                $updateStmt->bind_param("sss", $token, $expiration, $email);
                
                if ($updateStmt->execute() && sendResetEmail($email, $token)) {
                    $response = [
                        'success' => true,
                        'message' => 'Se han enviado las instrucciones a tu correo'
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Error al enviar el correo'
                    ];
                }
                $updateStmt->close();
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'No existe una cuenta con ese correo electrónico'
            ];
        }
        $stmt->close();
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
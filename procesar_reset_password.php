<?php
require_once 'includes/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($token) || empty($password)) {
        $response = [
            'success' => false,
            'message' => 'Datos incompletos'
        ];
    } else {
        // Verificar token y actualizar contraseña
        $sql = "SELECT id FROM usuarios WHERE password_reset_token = ? AND password_reset_expiration > NOW()";
        
        if ($stmt = $db->prepare($sql)) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $usuario = $result->fetch_assoc();
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                
                // Actualizar contraseña y limpiar token
                $updateSql = "UPDATE usuarios SET password = ?, password_reset_token = NULL, 
                             password_reset_expiration = NULL WHERE id = ?";
                
                if ($updateStmt = $db->prepare($updateSql)) {
                    $updateStmt->bind_param("si", $passwordHash, $usuario['id']);
                    
                    if ($updateStmt->execute()) {
                        $response = [
                            'success' => true,
                            'message' => 'Contraseña actualizada exitosamente'
                        ];
                    } else {
                        $response = [
                            'success' => false,
                            'message' => 'Error al actualizar la contraseña'
                        ];
                    }
                    $updateStmt->close();
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Token inválido o expirado'
                ];
            }
            $stmt->close();
        } else {
            $response = [
                'success' => false,
                'message' => 'Error en el servidor'
            ];
        }
    }
    
    $db->close();

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}


<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function generateResetToken()
{
    return bin2hex(random_bytes(32));
}

function getBaseUrl()
{
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];

    // Detectar si estamos en localhost
    if ($host === 'localhost') {
        // Obtener la ruta del proyecto desde la URL actual
        $currentPath = dirname($_SERVER['PHP_SELF']);
        // Eliminar '/includes' de la ruta si la función está en ese directorio
        $projectPath = str_replace('/includes', '', $currentPath);
        return $protocol . $host . $projectPath;
    } else {
        // En producción, usar solo el host
        return $protocol . $host;
    }
}

function sendResetEmail($email, $token)
{
    $mail = new PHPMailer(true);

    try {
        // Detectar si estamos en entorno local
        $isLocal = ($_SERVER['REMOTE_ADDR'] === '127.0.0.1' || $_SERVER['REMOTE_ADDR'] === '::1');

        if ($isLocal) {
            // Configuración para entorno local (MailHog)
            $mail->isSMTP();
            $mail->Host       = 'localhost';
            $mail->SMTPAuth   = false;
            $mail->Port       = 1025;
        } else {
            // Configuración para producción
            $mail->isSMTP();
            $mail->Host       = 'smtp.hostinger.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $_ENV['SMTP_USERNAME'] ?? '';
            $mail->Password   = $_ENV['SMTP_PASSWORD'] ?? '';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
        }

        // Configuración común
        $mail->SMTPDebug  = 0;
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom('noreply@aulavirtual.com', 'E-Learning RAMCC');
        $mail->addAddress($email);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Recuperación de Contraseña - E-Learning RAMCC';

        // Obtener la URL base correcta usando la nueva función
        $baseUrl = getBaseUrl();

        // Construir el enlace de restablecimiento
        $resetLink = $baseUrl . "/CambiarContraseña?token=" . $token;

        // Para debug (opcional)
        error_log("Reset Link generado: " . $resetLink);

        $mail->Body = "
            <div style='font-family: Arial, sans-serif;'>
                <h2>Recuperación de Contraseña</h2>
                <p>Has solicitado restablecer tu contraseña en E-Learning RAMCC.</p>
                <p>Haz clic en el siguiente enlace para crear una nueva contraseña:</p>
                <p><a href='{$resetLink}' style='background-color: #0d6efd; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Restablecer Contraseña</a></p>
                <p>Este enlace expirará en 1 hora por seguridad.</p>
                <p>Si no solicitaste este cambio, puedes ignorar este correo.</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>Este es un correo automático, por favor no respondas a este mensaje.</p>
            </div>
        ";

        // Intentar enviar el correo con reintentos
        $maxRetries = 3;
        while ($maxRetries > 0) {
            try {
                $mail->send();
                return true;
            } catch (Exception $e) {
                $maxRetries--;
                if ($maxRetries <= 0) {
                    error_log("Error al enviar correo a {$email}: " . $mail->ErrorInfo);
                    return false;
                }
                sleep(2);
            }
        }

        return false;
    } catch (Exception $e) {
        error_log("Error de configuración: " . $e->getMessage());
        return false;
    }
}

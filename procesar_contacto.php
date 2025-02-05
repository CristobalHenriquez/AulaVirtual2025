<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function sendContactEmail($name, $email, $subject, $message) {
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
            // Configuración para producción en Zoho Mail
            $mail->isSMTP();
            $mail->Host       = 'smtp.zoho.com'; // Servidor SMTP de Zoho
            $mail->SMTPAuth   = true;
            $mail->Username   = 'desarrollo@ramcc.net'; // Tu correo en Zoho
            $mail->Password   = 'Ramcc2023@';  // Tu contraseña real de Zoho
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465; // Puerto seguro SSL

            // Opcional: Si necesitas usar TLS en lugar de SSL
            // $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            // $mail->Port       = 587;
        }

        // Configuración común del correo
        $mail->SMTPDebug  = 0; // Cambia a 2 para depuración detallada
        $mail->CharSet    = 'UTF-8';
        $mail->setFrom('desarrollo@ramcc.net', 'E-Learning RAMCC');
        $mail->addAddress('capacitaciones@ramcc.net', 'E-Learning RAMCC');
        $mail->addReplyTo($email, $name);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = "Nuevo contacto: $subject";
        $mail->Body    = "
            <h2>Nuevo mensaje de contacto</h2>
            <p><strong>Nombre:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Asunto:</strong> $subject</p>
            <p><strong>Mensaje:</strong></p>
            <p>$message</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo de contacto: " . $mail->ErrorInfo);
        return false;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $response = [
            'success' => false,
            'message' => 'Por favor, complete todos los campos.'
        ];
    } else {
        if (sendContactEmail($name, $email, $subject, $message)) {
            $response = [
                'success' => true,
                'message' => 'Su mensaje ha sido enviado. ¡Gracias!'
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Hubo un error al enviar el mensaje. Por favor, inténtelo de nuevo más tarde.'
            ];
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

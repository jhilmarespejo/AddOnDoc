<?php
// use PHPMailer\PHPMailer\PHPMailer;
// use PHPMailer\PHPMailer\Exception;

// require 'vendor/autoload.php';

require '/var/www/html/AddOnInnova/PHPMailer/PHPMailerAutoload.php';

// =============================
// CONFIGURACIÓN
// =============================

// URL a verificar
$url = "https://app.innovasalud.bo";

// Configuración Coreo Web Link
$mailHost = "mail.innovasalud.bo";
$mailPort = 110;
$mailUsername = "monitor@innovasalud.bo";   // 👈 correo de envío
$mailPassword = "Prueba12345$";            // 👈 contraseña real o app password
$mailFrom    = "monitor@innovasalud.bo";
$mailTo      = "mlazarte@innovasalud.bo";  // 👈 correo de destino
$mailSubject = "Estado de la aplicación Innovasalud";

// =============================
// FUNCIÓN PARA VERIFICAR ESTADO
// =============================
function checkAppStatus($url) {
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);
    return $httpCode;
}

// =============================
// PROCESO DE MONITOREO
// =============================
$statusCode = checkAppStatus($url);

if ($statusCode == 200) {
    $message = "✅ La aplicación $url está operativa (HTTP $statusCode).";
} else {
    $message = "⚠️ La aplicación $url NO responde correctamente. Código devuelto: $statusCode.";
}

// =============================
// ENVÍO DE CORREO CON PHPMailer
// =============================
$mail = new PHPMailer(true);

try {
    // Configuración servidor
    $mail->isSMTP();
    $mail->Host       = $mailHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $mailUsername;
    $mail->Password   = $mailPassword;
    $mail->SMTPSecure = 'tls';
    $mail->Port       = $mailPort;

    // Remitente y destinatario
    $mail->setFrom($mailFrom, 'Monitor Innovasalud');
    $mail->addAddress($mailTo);

    // Contenido
    $mail->isHTML(true);
    $mail->Subject = $mailSubject;
    $mail->Body    = nl2br($message);
    $mail->AltBody = $message;

    $mail->send();
    echo "Notificación enviada: " . $message;

} catch (Exception $e) {
    echo "Error al enviar correo: {$mail->ErrorInfo}";
}

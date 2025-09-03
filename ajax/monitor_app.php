<?php

//require 'PHPMailer/PHPMailerAutoload.php';
require '/var/www/html/AddOnInnova/PHPMailer/PHPMailerAutoload.php';

$mail = new PHPMailer(true); // Instancia PHPMailer

try {
    // Configuración del servidor SMTP
    $mail->isSMTP();                            // Enviar usando SMTP
	$mail->Host = 'mail.innovasalud.bo';         // Servidor SMTP de Office 365
    $mail->SMTPAuth = true;                     // Habilitar autenticación SMTP
	$mail->SMTPDebug = 0;
	$mail->SMTPSecure = 'tls';

    $mail->Username = 'monitor@innovasalud.bo'; // Tu correo de Office 365
    $mail->Password = 'Prueba12345$';          // Tu contraseña (o contraseña de aplicación)
    $mail->Port = 587;                          // Puerto SMTP para STARTTLS

    // Remitente
	$mail->setFrom('monitor@innovasalud.bo', 'Monitor de Aplicaciones - Innovasalud');
    // Destinatario
    //$mail->addAddress('javo666@megalink.com', 'Javier');
    $mail->addAddress('mteran@innovasalud.bo'); 
    $mail->addAddress('mlazarte@innovasalud.bo', 'Marco Lazarte');


    // URL a verificar
    $url = "https://app.innovasalud.bo";

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

$currentStatus = ($statusCode == 200 || $statusCode == 302) ? "OK" : "ERROR";

if ($currentStatus ===  "OK") {
    $message = "✅ La aplicación $url está operativa (HTTP $statusCode).";
} else {
    $message = "⚠️ La aplicación $url NO responde correctamente. Código devuelto: $statusCode.";
}


    // Contenido del correo
    $mail->isHTML(true);                      // Establecer el formato de correo como HTML
    $mail->Subject = 'INNOVASALUD - Monitoreo de Aplicaciones';
    $mail->Body =  nl2br($message);
    // $mail->Body    = 'Hola Marco. Ya funciona. <b>en negrita</b>';

	$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
		)
	);

$mail->CharSet = 'UTF-8';
$mail->Encoding = 'base64';

    // Enviar el correo
    $mail->send();
    echo 'El mensaje ha sido enviado correctamente.';
} catch (Exception $e) {
    echo "No se pudo enviar el mensaje. Error: {$mail->ErrorInfo}";
}

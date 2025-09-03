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

    $mail->Username = 'mailapp@innovasalud.bo'; // Tu correo de Office 365
    $mail->Password = 'Prueba12345$';          // Tu contraseña (o contraseña de aplicación)
    $mail->Port = 587;                          // Puerto SMTP para STARTTLS

    // Remitente
	$mail->setFrom('mailapp@innovasalud.bo', 'Innovasalud');
    // Destinatario
    //$mail->addAddress('javo666@megalink.com', 'Javier');
	$mail->addAddress('mteran@innovasalud.bo', 'Marco');

    // Contenido del correo
    $mail->isHTML(true);                      // Establecer el formato de correo como HTML
    $mail->Subject = 'INNOVASALUD - Gestion de Pruebas';
    $mail->Body    = 'Hola Marco. Ya funciona. <b>en negrita</b>';

	$mail->SMTPOptions = array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
		)
	);

    // Enviar el correo
    $mail->send();
    echo 'El mensaje ha sido enviado correctamente.';
} catch (Exception $e) {
    echo "No se pudo enviar el mensaje. Error: {$mail->ErrorInfo}";
}




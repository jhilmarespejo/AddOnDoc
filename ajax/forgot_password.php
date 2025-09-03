<?php
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
require_once "../modelos/Forgot_password.php";
require '../PHPMailer/PHPMailerAutoload.php';

$psswd = new Forgot_password();


$id_operador = isset($_SESSION["idusuario"])? limpiarCadena($_SESSION["idusuario"]):""; 
$correo = isset($_POST["correo"])? limpiarCadena($_POST["correo"]):""; 

//$correo = 'miriam.fernandez@promujer.org';

switch ($_GET["op"]){
	case 'recibecorreo':

		//$data['status'] = 'ok';
		//$data['correo'] = $correo;

		//$correo = 'mteran@innovasalud.bo';
		$data_user = array();
		$data_user['status'] = 'ok';

		//echo "Correo: " . $correo . "<br>";
		$data_user = $psswd->get_nombre_del_usuario($correo);
		//dep($data_user);

		if($data_user['status'] == 'ok'){

			$login = $data_user['login'];
			$enviar_nombre = $data_user['nombre'];

			$codigo_canal = $data_user['codigo_canal'];

            if($codigo_canal != 'C999'){


    			$psswd_tmp = 'Pass'. $psswd->get_number();


    			$reset_pass = $psswd->reset_password($psswd_tmp, $login);
    			//dep($reset_pass);
    			if($reset_pass['status'] != 'ok'){

    				$data['status'] = 'Error';
    				$data['mensaje'] = ($reset_pass['mensaje_user'])?$reset_pass['mensaje_user']:$reset_pass['mensaje_used_psswd'];

    			}else{

                    		//// ----------------------------- ////
    				//// --- SOLO PARA LAS PRUEBAS --- ////
                    		//// ----------------------------- ////
    				//$correo = 'mteran@innovasalud.bo';
    				//$correo = 'corina.colque.flores@gmail.com';
    				//$copia1 = 'mteran@innovasalud.bo';
    				//echo "Se enviará a: " . $correo . "<br>";

    				$asunto='INNOVASALUD - Gestion de Seguridad';

    				$cuerpo  = '<!DOCTYPE html>';
    				$cuerpo .= '<html lang="es">';
    				$cuerpo .= '<head>';
    				$cuerpo .= '<meta http-equiv=”Content-Type” content=”text/html; charset=UTF-8″ />';
    				$cuerpo .= '</head>';
    				$cuerpo .= '<body>';
    				$cuerpo .= '<h4>Mensaje del Sistema INNOVASALUD</h4>';
    				$cuerpo .= '<h4 style="color:#ec7063;">Estimado(a) : &nbsp;'. $enviar_nombre . '</h4>';
    				$cuerpo .= '<h4 style="color:#ec7063;">Use la siguiente contrase&ntilde;a para ingresar al Sistema</h4>';
    				$cuerpo .= '<h4 style="color:#ec7063;margin-left:30px;">'. $psswd_tmp . '</h4>';
    				$cuerpo .= '<br>';
    				$cuerpo.='<P><i><strong>Este mensaje fue generado autom&aacute;ticamente y s&oacute;lo es informativo, no debe responder al mismo.</strong></i></P>';
    				$cuerpo .= '</body>';
    				$cuerpo .= '</html>';

    				$mail = new PHPMailer(true);

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
						$mail->addAddress($correo, $enviar_nombre);

						// Contenido del correo
						$mail->isHTML(true);                      // Establecer el formato de correo como HTML
						$mail->Subject = $asunto;
						$mail->Body    = $cuerpo;

						$mail->SMTPOptions = array(
						'ssl' => array(
							'verify_peer' => false,
							'verify_peer_name' => false,
							'allow_self_signed' => true
							)
						);

						// Enviar el correo
						$mail->send();
						//echo 'El mensaje ha sido enviado correctamente.';
					} catch (Exception $e) {
						echo "No se pudo enviar el mensaje. Error: {$mail->ErrorInfo}";
					}


    			}

    			$data['status'] = $reset_pass['status'];
    			$data['mensaje'] = 'Password temporal enviado a su correo';
    			$data['nombre'] = $enviar_nombre;

            }else{

			$data['status'] = 'Error';
			$data['mensaje'] = 'Funcion no habilitada para este Canal';
            		$data['correo'] = $data_user['correo'];


            }

		}else{

			$data['status'] = 'Error';
			$data['mensaje'] = 'No existe el correo. Favor contactar a Sistemas';
            		$data['correo'] = $data_user['correo'];

		}

		//dep($data);
		echo json_encode($data);

	break;

}
function dep($data){
	$format = print_r('<pre>');
	$format .= print_r($data);
	$format .= print_r('</pre>');

	return $format;
}

ob_end_flush();
?>

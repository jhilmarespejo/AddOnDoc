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
    				$mail->isSMTP();
    				$mail->SMTPDebug = 0;
    				//$mail->SMTPAutoTLS = false;
    				$mail -> SMTPOptions = [
    					'ssl' => [
    						'verify_peer' => false ,
    						'verify_peer_name' => false ,
    						'allow_self_signed' => true ,
    					]
    				];


    				//Server settings
    				$mail->SMTPAuth   = true;                               //Enable SMTP authentication
    				$mail->SMTPSecure = 'tls';                              //Enable implicit TLS encryption
    				$mail->Host       = 'smtp.office365.com';            //Set the SMTP server to send through
    				$mail->Port       = 587;                                //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    				$mail->Username   = 'sistemas@innovasalud.bo';  //SMTP username
    				$mail->Password   = 'ycpbnyqwcmnmhyxj';                   //SMTP password
    				$mail->setFrom('sistemas@innovasalud.bo', 'Sistema INNOVASALUD');//Modificar

    				$mail->SMTPDebug = 0;                                   //Enable verbose debug output

    				//Recipients
    				$mail->addAddress($correo, $enviar_nombre);             //Modificar
    				//$mail->addCC($copia1); //Modificar

    				//Content
    				$mail->isHTML(true);                                    //Set email format to HTML
    				$mail->Subject = $asunto;                               //Modificar
    				$mail->Body = $cuerpo;                                  //Modificar

    				//echo "A punto de llamar a MAIL!!!!<br>";

    				if($mail->send()){
    					$mensaje = 'Correo Enviado con información de contraseña';
    					//echo "Correo enviado!<br>";
    				} else {
    					$mensaje = 'Error'. $mail->ErrorInfo;
    					//echo "Falló el envio del correo<br>";

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

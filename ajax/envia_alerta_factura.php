<?php
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}

require_once "/var/www/html/AddOnInnova/config/Conexion.php";
//require_once "llamamosWS_Sartawi.php";
require '/var/www/html/AddOnInnova/PHPMailer/PHPMailerAutoload.php';



// FECHA INICIO VIGENCIA DEL PLAN A FACTURAR
date_default_timezone_set('America/La_Paz');
$fecha_inicio = date('Ymd');

// Buscamos que facturas están pendientes de Facturación
$row = buscaFacturasNoMigradas($conexion);
var_dump($row);
die();
$cnt = $row[0];
if($cnt == '0'){
	echo "<br>";
	echo "No hay Registros para facturar!!";
	echo "<br><br>";
	//dep($res);
}else{
	$k=1;
	$data = array();
	for($i = 0; $i < $cnt; $i++){

		$data['id_registro_a_facturar'] = $row[$k]['id'];
		$data['cod_cli'] = $row[$k]['cod_cli'];
		$data['cod_ope'] = $cod_ope;
		$data['cod_tra'] = $cod_tra;
		$data['tipo_documento'] = $row[$k]['tipo_documento'];
		$data['cedula'] = $row[$k]['cedula'];
		$data['extension'] = isset($row[$k]['extension']) ? $row[$k]['extension'] : "";
		$data['expedido'] = isset($row[$k]['expedido']) ? $row[$k]['expedido'] : '';
		$data['ap_paterno'] = $row[$k]['ap_paterno'];
		$data['ap_materno'] = $row[$k]['ap_materno'];
		$data['nombres'] = $row[$k]['nombres'];
		$data['razon_social'] = $row[$k]['nombres']. ' ' . $row[$k]['ap_paterno'] . ' ' . $row[$k]['ap_materno'];
		$data['genero'] = $row[$k]['genero'];
		$data['telefono'] = $row[$k]['telefono'];
		$data['email'] = "";
		$data['direccion'] = 'N/A';
		$data['fecha_nacimiento'] = $row[$k]['fecha_nacimiento'];
		$data['codigo_plan'] = $row[$k]['codigo_plan'];
		$data['precio'] = $row[$k]['deuda'];
		$data['canal'] = $row[$k]['codigo_canal'];
		$data['codigo_agencia'] = $row[$k]['codigo_agencia'];
		$k++;
	}
	var_dump($data);
	die();
	envia_correo_alerta($data);
	
}

function envia_correo_alerta($data){

	//echo "ENVIANDO CORREO DE ALERTA - UPDATE1<br>";

	//require '/var/www/html/innova2/PHPMailer/PHPMailerAutoload.php';

	//echo "ENMVIANDO CORREO DE ALERTA - UPDATE2<br>";

	$correo = 'mlazarte@innovasalud.bo';
	$enviar_nombre = "Marco Lazarte";
	$copia = 'mlazarte@innovasalud.bo';

	$asunto='INNOVASALUD - Error Migración Facturas';

	$cuerpo  = '<!DOCTYPE html>';
	$cuerpo .= '<html lang="es">';
	$cuerpo .= '<head>';
	$cuerpo .= '<meta http-equiv=”Content-Type” content=”text/html; charset=UTF-8″ />';
	$cuerpo .= '</head>';
	$cuerpo .= '<body>';
	$cuerpo .= '<h4>Mensaje del Sistema INNOVASALUD</h4>';
	$cuerpo .= '<h4>Estimado(a) : &nbsp;'. $enviar_nombre . '</h4>';
	$cuerpo .= '<p>Se ha producido un error al realizar la migración, las siguientes facturas cuentan con problemas:</p>';
	$cuerpo .= '<p style="margin-left:30px;"> Cliente: '. $data['razon_social'] . '</p>';
	$cuerpo .= '<p style="margin-left:30px;"> Cedula: '. $data['cedula'] . '</p>';
	$cuerpo .= '<p style="margin-left:30px;"> Codigo Plan: '. $data['codigo_plan'] . '</p>';
	$cuerpo .= '<p style="margin-left:30px;"> Id Registro: '. $data['id_registro_a_facturar'] . '</p>';
	$cuerpo .= '<p style="margin-left:30px;"> Status Fact: '. $data['status_fact'] . '</p>';
	$cuerpo .= '<p style="margin-left:30px;"> Mensaje Fact: '. $data['mensaje_fact'] . '</p>';
	$cuerpo .= '<br>';
	$cuerpo.='<p><i><strong>Este mensaje fue generado autom&aacute;ticamente y s&oacute;lo es informativo, no debe responder al mismo.</strong></i></p>';
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
	$mail->addCC($copia); //Modificar

	//Content
	$mail->isHTML(true);                                    //Set email format to HTML
	$mail->Subject = $asunto;                               //Modificar
	$mail->Body = $cuerpo;                                  //Modificar

	if($mail->send()){
		$mensaje = 'Correo Enviado con información de Error';
		echo "Correo enviado!<br>";
	} else {
		$mensaje = 'Error'. $mail->ErrorInfo;
		echo "Falló el envio del correo<br>";

	}

}

function buscaFacturasNoMigradas($db){

	$sql = "SELECT t.id as id, c.nombres, c.ap_paterno, c.ap_materno, c.genero, c.cod_cli,
				c.tipo_documento, c.num_documento as cedula, c.extension, c.expedido, c.fecha_nacimiento,
				c.telefono, t.precio as deuda, t.codigo_plan, u.codigoAlmacen as codigo_agencia ,t.codigo_canal
			FROM temp t, clientes c, usuario u
			WHERE t.id_contratante = c.id
			AND t.id_usuario = u.idusuario
			AND (t.id=19443)
			ORDER BY t.id";
	$data = mysqli_query($db, $sql);

	return $data;
}

ob_end_flush();
?>

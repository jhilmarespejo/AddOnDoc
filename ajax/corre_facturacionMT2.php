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
$res = buscaClientesParaFacturar($conexion);
$cnt = mysqli_num_rows($res);
echo "CANTIDAD: " . $cnt . "<br>";
if($cnt == '0'){
	echo "<br>";
	echo "No hay Registros para facturar!!";
	echo "<br><br>";
	dep($res);
}else{

	$numero = 1;
	while($row = mysqli_fetch_assoc($res)){
		//dep($row);
		//die();

		//dep($row);
		$codigo_plan_padre = $row['codigo_plan'];
		$id_registro_a_facturar = $row['id'];

		// Leemos datos del PLAN PADRE
		$filas = readPlanPadre($conexion, $codigo_plan_padre);
		$contador = 0;
		while ($fila = mysqli_fetch_assoc($filas)){
			$rs_plan[$contador++] = $fila;
		}
		//dep($rs_plan);


		$cod_ope = getParameterValues($conexion,'cod_ope');
		$cod_tra = getParameterValues($conexion,'cod_tra');

		echo "Numero: " . $numero++ . "<br>";
		echo "Cod Ope: " . $cod_ope . "<br>";
		echo "Cod Tra: " . $cod_tra . "<br>";
		echo "Cod Plan: " . $row['codigo_plan'] . "<br>";
		echo "Nombre: " . $row['nombres']. ' ' . $row['ap_paterno'] . ' ' . $row['ap_materno'] . "<br>";
		echo "Cedula: " . $row['cedula']. "<br>";
		echo "Id Registro: " . $row['id']. "<br>";

		$data = array();

		$data['id_registro_a_facturar'] = $row['id'];
		$data['cod_cli'] = $row['cod_cli'];
		$data['cod_ope'] = $cod_ope;
		$data['cod_tra'] = $cod_tra;
		$data['tipo_documento'] = $row['tipo_documento'];
		$data['cedula'] = $row['cedula'];
		$data['extension'] = isset($row['extension']) ? $row['extension'] : "";
		$data['expedido'] = isset($row['expedido']) ? $row['expedido'] : '';
		$data['ap_paterno'] = $row['ap_paterno'];
		$data['ap_materno'] = $row['ap_materno'];
		$data['nombres'] = $row['nombres'];
		$data['razon_social'] = $row['nombres']. ' ' . $row['ap_paterno'] . ' ' . $row['ap_materno'];
		$data['genero'] = $row['genero'];
		$data['telefono'] = $row['telefono'];
		$data['email'] = "";
		$data['direccion'] = 'N/A';
		$data['fecha_nacimiento'] = $row['fecha_nacimiento'];
		$data['codigo_plan'] = $row['codigo_plan'];
		$data['precio'] = $row['deuda'];
		$data['canal'] = $row['codigo_canal'];
		$data['codigo_agencia'] = $row['codigo_agencia'];

		//dep($data);
		//die()

		//if($codigo_plan_padre == 'PC01'){
		if($contador == '1'){

			echo "SIMPLE" . "<br><br>";
			$data['codigo_plan'] = $rs_plan[0]['codigo_plan_hijo'];
			$data['precio'] = $rs_plan[0]['precio_hijo'];
			$data['fecha_inicio'] = $fecha_inicio;

			$horaLlamada = date('Y-m-d H:i:s');
		//	$rc = llamamosWS_Sartawi($conexion,$data);
			$horaRetorno = date('Y-m-d H:i:s');
			//dep($rc);

			// Recogemos el mensaje de retorno del WS Sartawi
			$data['status_fact']  = $rc['status_fact'];
			$data['mensaje_fact'] = $rc['mensaje_fact'];
			$data['hora_llamada'] = $horaLlamada;
			$data['hora_retorno'] = $horaRetorno;

			//$rc['status_fact'] = 'E';
			//$data['status_fact']  = $rc['status_fact'];
			//$data['mensaje_fact'] = 'TODO BIEN';

			if($rc['status_fact'] == 'E'){
				// Actualizamos los campos FACTURACION, FECHA_FACTURACION y ESTADO de la table temp
				$rspta = actualizamosCamposFacturacion($conexion,$id_registro_a_facturar,$numero);
				if($rspta == 'FALSE'){
					echo "ERROR UPDATE STATUS FACT!!<br>";
					echo "ID REGS A FACTURAR: " . $id_registro_a_facturar . "<br>";
					echo "FECHA HORA: " . $horaLlamada . "<br>";
					//$data['status_fact']  = 'Codigo de Error WS';
					//$data['mensaje_fact'] = 'Error en el llamado al WS';
					envia_correo_alerta($data);
					$rspta = registraDatosLog($conexion,$data);
					die();
				}


			}else{

				// Envía correo alertando falla en la migracion
				alerta_falla_migracion($data);

			}

			// Guardamos en el LOG los datos enviados al WS
			$rspta = registraDatosLog($conexion,$data);



		//}else if($codigo_plan_padre == 'PC02'){
		}else{

			echo "MULTIPLE - MULTIPLE" . "<br><br>";
			//--------------------------------//
			// Primera factura                //
			// Ej: Fecha_inicio = 2023-12-26  //
			//--------------------------------//
			$data['codigo_plan'] = $rs_plan[0]['codigo_plan_hijo'];
			$data['precio'] = $rs_plan[0]['precio_hijo'];
			$data['fecha_inicio'] = $fecha_inicio;

			//dep($data);

			$horaLlamada = date('Y-m-d H:i:s');
		//	$rc = llamamosWS_Sartawi($conexion,$data);
			$horaRetorno = date('Y-m-d H:i:s');

			// Recogemos el mensaje de retorno del WS Sartawi
			$data['status_fact']  = $rc['status_fact'];
			$data['mensaje_fact'] = $rc['mensaje_fact'];
			$data['hora_llamada'] = $horaLlamada;
			$data['hora_retorno'] = $horaRetorno;

			//$rc['status_fact'] = 'E';
			//$data['status_fact']  = $rc['status_fact'];
			//$data['mensaje_fact'] = 'TODO BIEN1';
			//dep($data);

			if($rc['status_fact'] == 'E'){
				// Actualizamos los campos FACTURACION, FECHA_FACTURACION y ESTADO de la table temp
		//		$rspta = actualizamosCamposFacturacion($conexion,$id_registro_a_facturar,$numero);
				$rspta = 'TRUE';

				if($rspta=='FALSE'){
					echo "ERROR UPDATE STATUS FACT!!<br>";
					echo "ID REGS A FACTURAR: " . $id_registro_a_facturar . "<br>";
					echo "FECHA HORA: " . $horaLlamada . "<br>";
					envia_correo_alerta($data);
					$rspta = registraDatosLog($conexion,$data);
					die();
				}
			}else{

				// Envía correo alertando falla en la migracion
                                alerta_falla_migracion($data);

                        }

			// Guardamos en el LOG los datos enviados al WS
			$rspta = registraDatosLog($conexion,$data);
			//echo "Cod Tra1: " . $cod_tra . "<br>";
			echo "Primera Factura generada!<br>";


			//--------------------------------//
			// Segunda factura                //
			// Ej: Fecha_inicio = 2024-12-26  //
			//--------------------------------//
			$fechaactual = date('Y-m-d'); // 2016-12-29
			$nuevafecha = strtotime ('+1 year' , strtotime($fechaactual)); //Se añade un año mas
			$nuevafecha = date ('Ymd',$nuevafecha);

			$data['cod_tra'] = getParameterValues($conexion,'cod_tra');


			$cod_tra = $data['cod_tra'];
			//echo "Cod Tra2: " . $cod_tra . "<br>";

			$data['fecha_inicio'] = $nuevafecha;
			$data['codigo_plan'] = $rs_plan[1]['codigo_plan_hijo'];
			$data['precio'] = $rs_plan[1]['precio_hijo'];

			//dep($data);

			$horaLlamada = date('Y-m-d H:i:s');
			$rc = llamamosWS_Sartawi($conexion,$data);
			$horaRetorno = date('Y-m-d H:i:s');

			$data['status_fact']  = $rc['status_fact'];
			$data['mensaje_fact'] = $rc['mensaje_fact'];
			$data['hora_llamada'] = $horaLlamada;
			$data['hora_retorno'] = $horaRetorno;

			//$rc['status_fact'] = 'E';
			//$data['status_fact']  = $rc['status_fact'];
			//$data['mensaje_fact'] = 'TODO BIEN2';
			//dep($data);

			if($rc['status_fact'] == 'E'){
				// Actualizamos los campos FACTURACION, FECHA_FACTURACION y ESTADO de la table temp
		//		$rspta = actualizamosCamposFacturacion($conexion,$id_registro_a_facturar,$numero);
				$rspta = 'TRUE';

				if($rspta=='FALSE'){
					echo "ERROR UPDATE STATUS FACT!!<br>";
					echo "ID REGS A FACTURAR: " . $id_registro_a_facturar . "<br>";
					echo "FECHA HORA: " . $horaLlamada . "<br>";
					envia_correo_alerta($data);
					$rspta = registraDatosLog($conexion,$data);
					die();
				}
			}else{

				// Envía correo alertando falla en la migracion
                                alerta_falla_migracion($data);

                        }

			// Guardamos en el LOG los datos enviados al WS
			$rspta = registraDatosLog($conexion,$data);
			echo "Segunda Factura generada!<br>";

			//--------------------------------//
			// Tercera factura                //
			// Ej: Fecha_inicio = 2024-12-26  //
			//--------------------------------//
			$nuevafecha = strtotime ('+2 year' , strtotime($fechaactual)); //Se añade un año mas
			$nuevafecha = date ('Ymd',$nuevafecha);

			$data['cod_tra'] = getParameterValues($conexion,'cod_tra');
			$cod_tra = $data['cod_tra'];
                        //echo "Cod Tra3: " . $cod_tra . "<br>";



			$data['fecha_inicio'] = $nuevafecha;
			$data['codigo_plan'] = $rs_plan[1]['codigo_plan_hijo'];
			$data['precio'] = $rs_plan[1]['precio_hijo'];

			//dep($data);

			$horaLlamada = date('Y-m-d H:i:s');
		//	$rc = llamamosWS_Sartawi($conexion,$data);
			$horaRetorno = date('Y-m-d H:i:s');

			$data['status_fact']  = $rc['status_fact'];
			$data['mensaje_fact'] = $rc['mensaje_fact'];
			$data['hora_llamada'] = $horaLlamada;
			$data['hora_retorno'] = $horaRetorno;

			//$rc['status_fact'] = 'E';
			//$data['status_fact']  = $rc['status_fact'];
			//$data['mensaje_fact'] = 'TODO BIEN3';
			//dep($data);

			if($rc['status_fact'] == 'E'){
				//Actualizamos los campos FACTURACION, FECHA_FACTURACION y ESTADO de la table temp
		//		$rspta = actualizamosCamposFacturacion($conexion,$id_registro_a_facturar,$numero);
				$rspta = 'TRUE';

				if($rspta=='FALSE'){
					echo "ERROR UPDATE STATUS FACT!!<br>";
					echo "ID REGS A FACTURAR: " . $id_registro_a_facturar . "<br>";
					echo "FECHA HORA: " . $horaLlamada . "<br>";
					envia_correo_alerta($data);
					$rspta = registraDatosLog($conexion,$data);
					die();
				}
			}else{

				// Envía correo alertando falla en la migracion
                                alerta_falla_migracion($data);

                        }

			// Guardamos en el LOG los datos enviados al WS
			$rspta = registraDatosLog($conexion,$data);
			echo "Tercera Factura generada<br>";
		}


	}
}
//echo json_encode($data);


function envia_correo_alerta($data){

	$correo = 'mteran@innovasalud.bo';
	$enviar_nombre = "Marcelo Teran B.";
	$copia = 'mlazarte@innovasalud.bo';

	$asunto='INNOVASALUD - Gestion de Operacion';

	$cuerpo  = '<!DOCTYPE html>';
	$cuerpo .= '<html lang="es">';
	$cuerpo .= '<head>';
	$cuerpo .= '<meta http-equiv=”Content-Type” content=”text/html; charset=UTF-8″ />';
	$cuerpo .= '</head>';
	$cuerpo .= '<body>';
	$cuerpo .= '<h4>Mensaje del Sistema INNOVASALUD</h4>';
	$cuerpo .= '<h4>Estimado(a) : &nbsp;'. $enviar_nombre . '</h4>';
	$cuerpo .= '<p>Se ha producido un error al realizar el UPDATE del estado en la TEMP</p>';
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

	//echo "A punto de llamar a MAIL!!!!<br>";

	if($mail->send()){
		$mensaje = 'Correo Enviado con información de Error';
		//echo "Correo enviado!<br>";
	} else {
		$mensaje = 'Error'. $mail->ErrorInfo;
		//echo "Falló el envio del correo<br>";

	}



}


function alerta_falla_migracion($data){

	$correo = 'mteran@innovasalud.bo';
        $enviar_nombre = "Marcelo Teran B.";
        $copia = 'mlazarte@innovasalud.bo';

        $asunto='INNOVASALUD - Gestion de Operacion';

	$cuerpo  = '<!DOCTYPE html>';
        $cuerpo .= '<html lang="es">';
        $cuerpo .= '<head>';
        $cuerpo .= '<meta http-equiv=”Content-Type” content=”text/html; charset=UTF-8″ />';
        $cuerpo .= '</head>';
        $cuerpo .= '<body>';
        $cuerpo .= '<h4>Mensaje del Sistema INNOVASALUD</h4>';
        $cuerpo .= '<h4>Estimado(a) : &nbsp;'. $enviar_nombre . '</h4>';
        $cuerpo .= '<p>Se ha producido un error en la migracion de datos hacie el SAP</p>';
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

        //echo "A punto de llamar a MAIL!!!!<br>";

        if($mail->send()){
                $mensaje = 'Correo Enviado con información de Error';
                //echo "Correo enviado!<br>";
        } else {
                $mensaje = 'Error'. $mail->ErrorInfo;
                //echo "Falló el envio del correo<br>";

        }


}

function buscaClientesParaFacturar($db){

	$sql = "SELECT t.id as id, c.nombres, c.ap_paterno, c.ap_materno, c.genero, c.cod_cli,
				c.tipo_documento, c.num_documento as cedula, c.extension, c.expedido, c.fecha_nacimiento,
				c.telefono, t.precio as deuda, t.codigo_plan, u.codigoAlmacen as codigo_agencia ,t.codigo_canal
			FROM temp t, clientes c, usuario u
			WHERE t.id_contratante = c.id
			AND t.id_usuario = u.idusuario
			AND t.estado = 'C'
			LIMIT 1";

		$res = mysqli_query($db, $sql);

		if(!$res){
			echo "ERROR buscarClientesParaFacturar<br>";
			echo "Error: " . mysqli_error($db);
			die();
		}

		return $res;

}


function readPlanPadre($db, $codigo){

	$sql = "SELECT * FROM plan_padre WHERE codigo_plan_padre = '$codigo' AND estado = 'A'";

	$res = mysqli_query($db, $sql);

	if(!$res){
		echo "ERROR readPlanPadre<br>";
		echo "Error: " . mysqli_error($db);
		die();
	}

	return $res;

}


function getParameterValues($db, $parameter){

	$sql = "SELECT max(valor_actual) valor_actual FROM parametros
			WHERE parametro = '$parameter'";

	$res = mysqli_query($db, $sql);
	if(!$res){
		echo "ERROR getParameterValues<br>";
		echo "Error: " . mysqli_error($db);
		die();
	}

	$row = mysqli_fetch_assoc($res);
	$valor_actual = $row['valor_actual'];
	$valor_nuevo = $valor_actual + 1;

	$sql = "UPDATE parametros SET valor_actual = '$valor_nuevo' WHERE  parametro = '$parameter'";
	$res = mysqli_query($db, $sql);
	if(!$res){
		echo "ERROR updateParameterValues<br>";
		echo "Error: " . mysqli_error($db);
		die();
	}

	return $valor_actual;

}

function actualizamosCamposFacturacion($db, $id_registro_a_facturar,$numero){

	date_default_timezone_set('America/La_Paz');
	$fecha_facturacion = date('Y-m-d H:i:s');

	$sql = "UPDATE temp SET facturacion = 'FACTURADO', fecha_facturacion = '$fecha_facturacion', estado = 'F' 
				WHERE id = '$id_registro_a_facturar'";

	$res = mysqli_query($db, $sql);
	if(!$res){
		echo "ERROR updateParameterValues<br>";
		echo "Error: " . mysqli_error($db);
		die();
	}

	/* if($numero == '10'){
		$res = false;
	} */


	if($res){
		$cod = 'TRUE';
	}else{
		$cod = 'FALSE';
	}



	$sentence = "Fecha Fact:" . $fecha_facturacion . " - ID Registro:" . $id_registro_a_facturar . " - Return Code: " . $cod;
	$sql = "INSERT log_update_fact (sentence,fecha_creacion,codigo_ret) VALUES('$sentence','$fecha_facturacion','$cod')";

	$rspta = mysqli_query($db, $sql);
	 if(!$rspta){
                echo "ERROR Insert Log Update Fact<br>";
                echo "Error: " . mysqli_error($db);
                die();
        }


	return $cod;


}

function registraDatosLog($db,$data)
{

		date_default_timezone_set('America/La_Paz');
        	$fecha_creacion = date('Y-m-d H:i:s');


		$status_fact = $data['status_fact'];
		$mensaje_fact = $data['mensaje_fact'];
		$cod_cli = $data['cod_cli'];
		$cod_ope = $data['cod_ope'];
		$cod_tra = $data['cod_tra'];
		$tipo_documento = $data['tipo_documento'];
		$cedula = $data['cedula'];
		$extension = $data['extension'];
		$expedido = $data['expedido'];
		$ap_paterno = $data['ap_paterno'];
		$ap_materno = $data['ap_materno'];
		$nombres = $data['nombres'];
		$razon_social = $data['razon_social'];
		$genero = $data['genero'];
		$telefono = $data['telefono'];
		$email = $data['email'];
		$direccion = $data['direccion'];
		$fecha_nacimiento = $data['fecha_nacimiento'];
		$codigo_plan = $data['codigo_plan'];
		$precio = $data['precio'];
		$canal = $data['canal'];
		$codigo_agencia = $data['codigo_agencia'];
		$fecha_inicio = $data['fecha_inicio'];
		$hora_llamada = $data['hora_llamada'];
		$hora_retorno = $data['hora_retorno'];

		$sql = "INSERT INTO log_facturacion (status_fact, mensaje_fact, fecha_creacion,cod_cli, cod_ope,cod_tra,tipo_documento,
				cedula,extension,expedido,ap_paterno,ap_materno,nombres,razon_social,genero,telefono,
				email,direccion,fecha_nacimiento,codigo_plan,precio,canal,codigo_agencia,fecha_inicio,
				hora_llamada,hora_retorno) 
				VALUES ('$status_fact', '$mensaje_fact','$fecha_creacion' ,'$cod_cli', '$cod_ope','$cod_tra','$tipo_documento',
				'$cedula','$extension','$expedido','$ap_paterno','$ap_materno','$nombres','$razon_social','$genero',
				'$telefono','$email','$direccion','$fecha_nacimiento','$codigo_plan','$precio','$canal','$codigo_agencia',
				'$fecha_inicio','$hora_llamada','$hora_retorno')";

		$res = mysqli_query($db, $sql);

		if(!$res){
			echo "ERROR registraDatosLog<br>";
			echo "Error: " . mysqli_error($db);
			die();
		}

}

function llamamosWS_Sartawi($db,$data){

    //$varios = new Varios();
    //require_once "../config/Conexion.php";

    $url = 'https://web.getsap.com/Innova/Admisiones/api/TransfDatosCliente';

    date_default_timezone_set('America/La_Paz');
    $fecha_facturacion = date('YmdHis');
    //$fecha_inicio = date('Ymd');

    //echo "FEC NAC1: " . $data['fecha_nacimiento'] . "<br>";
    $fecha_nacimiento = substr($data['fecha_nacimiento'],0,4).substr($data['fecha_nacimiento'],5,2).substr($data['fecha_nacimiento'],8,2);
    //echo "FEC NAC2: " . $fecha_nacimiento . "<br>";
    //die();

    //-------------------------------------------//
    // Buscamos al usuario para el envío del WS  //
    //-------------------------------------------//
    $codAgencia = $data['codigo_agencia']; 
    $res = buscaUsuarioWS($db,$codAgencia);

    $usuarioWS = $res['usuario'];
    $claveWS   = $res['clave'];
    //dep($res);

    //die();

    $user = $usuarioWS;
    $psw  = $claveWS;


    // Datos del cliente
    $codigoCliente = $data['cod_cli'];
    $operacionId = $data['cod_ope'];
    $numTransaccion = $data['cod_tra'];

    $tipoDoc = $data['tipo_documento'];
    //$tipoDoc = 'C';

    $numDoc = $data['cedula'];
    $extensionDoc = $data['extension'];

    $expedidoDoc = $data['expedido'];
    //$expedidoDoc = 'CB';

    $apPaterno = $data['ap_paterno'];
    $apMaterno = $data['ap_materno'];
    $nombres = $data['nombres'];
    $razonSocial = $data['razon_social'];
    $genero = $data['genero'];
    $telefono = $data['telefono'];
    $email = "";
    $direccion = "N-A";
    $fecNacimiento = $fecha_nacimiento;
    $cuidad = "COCHABAMBA";
    $pais = "BOLIVIA";
    $ocupacion = "";
    $indicador = "D";

    $planElegido = $data['codigo_plan'];
    //$planElegido = "PPCE0038";

    $monto = $data['precio'];
    $fecha = $fecha_facturacion;

    $numeroPago = 0;
    $codAgencia = $data['codigo_agencia'];
    $codigoAsesor = "";
    $modalidad = "E";

    $canal = $data['canal'];
    //$canal = 'C006';

    $fechaInicio = $data['fecha_inicio'];

    // Datos del beneficiario
    $tipoBen = "1";
    $tipoDocBen = $data['tipo_documento'];
    $tipoDocBen = 'C';
    $numDocBen = $data['cedula'];
    $extensionBen = $data['extension'];
    $expedidoBen = $data['expedido'];
    $apellidoPaternoBen = $data['ap_paterno'];
    $apellidoMaternoBen = $data['ap_materno'];
    $nombresBen = $data['nombres'];
    $fechaNacimientoBen = $fecha_nacimiento;
    $generoBen = $data['genero'];
    $telefonoBen = $data['telefono'];
    $emailBen = "";
    $direccionBen = "N-A";
    $ciudadBen = "COCHABAMBA";
    $paisBen = "BOLIVIA";
    $parentescoBen = "Titular";
    $docIdentidadTitular = 0;

    // Construir el array $cliente
    $cliente = array(
        "user" => $user,
        "psw" => $psw,
        "codigoCliente" => $codigoCliente,
        "operacionId" => $operacionId,
        "numTransaccion" => $numTransaccion,
        "tipoDoc" => $tipoDoc,
        "numDoc" => $numDoc,
        "extensionDoc" => $extensionDoc,
        "expedidoDoc" => $expedidoDoc,
        "apPaterno" => $apPaterno,
        "apMaterno" => $apMaterno,
        "nombres" => $nombres,
        "razonSocial" => $razonSocial,
        "genero" => $genero,
        "telefono" => $telefono,
        "email" => $email,
        "direccion" => $direccion,
        "fecNacimiento" => $fecNacimiento,
        "cuidad" => $cuidad,
        "pais" => $pais,
        "ocupacion" => $ocupacion,
        "indicador" => $indicador,
        "planElegido" => $planElegido,
        "monto" => $monto,
        "fecha" => $fecha,
        "numeroPago" => $numeroPago,
        "codAgencia" => $codAgencia,
        "codigoAsesor" => $codigoAsesor,
        "modalidad" => $modalidad,
        "fechaInicio" => $fechaInicio,
        "canal" => $canal, // Agregado manualmente, ya que no está en tus datos originales
        "beneficiarios" => array(
            array(
                "tipoBen" => $tipoBen,
                "tipoDocBen" => $tipoDocBen,
                "numDocBen" => $numDocBen,
                "extensionBen" => $extensionBen,
                "expedidoBen" => $expedidoBen,
                "apellidoPaternoBen" => $apellidoPaternoBen,
                "apellidoMaternoBen" => $apellidoMaternoBen,
                "nombresBen" => $nombresBen,
                "fechaNacimientoBen" => $fechaNacimientoBen,
                "generoBen" => $generoBen,
                "telefonoBen" => $telefonoBen,
                "emailBen" => $emailBen,
                "direccionBen" => $direccionBen,
                "ciudadBen" => $ciudadBen,
                "paisBen" => $paisBen,
                "parentescoBen" => $parentescoBen,
                "docIdentidadTitular" => $docIdentidadTitular,
            )
        )
    );


    //dep($cliente);
    //die();


    // Encoded as a json string
    $data_string = json_encode($cliente);

    $ch=curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_POST  => true,
        CURLOPT_POSTFIELDS  => $data_string,
        CURLOPT_HEADER  => true,
        CURLOPT_HTTPHEADER  => array('Content-Type:application/json', 'Content-Length: ' . strlen($data_string)),
        CURLOPT_RETURNTRANSFER  => true
        )
    );


    $ret_val = array();

    // Aqui devuelvo el resultado
    $result = curl_exec($ch);

    if ($result === false) {
        //echo 'Error cURL: ' . curl_error($ch);
        $estado = curl_error($ch);
        $ret_val['status_fact']  = $estado;
        $ret_val['mensaje_fact'] = 'Error al ejecutar la funcion: "curl_error($ch)"';
    } else {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $headerSize);
        $body = substr($result, $headerSize);
        $json_response = json_decode($result, true);

        //echo 'Código HTTP: ' . $httpCode . PHP_EOL;
        //echo 'Encabezado de respuesta: ' . $header . PHP_EOL;
        //echo 'Cuerpo de respuesta: ' . $body . PHP_EOL;
        $responseData = json_decode($body, true); // true para obtener un array asociativo
        if ($responseData !== null) {
            // Procesar $responseData según tus necesidades
            $estado = $responseData['Estado'];
            $mensaje = isset($responseData['Mensaje']) ? $responseData['Mensaje']: "";
            //echo 'Estado: ' . $estado . "<br>";
            //echo 'Mensaje: ' . $mensaje . "<br>";
        } else {
            //echo 'Error al decodificar la respuesta JSON.';
        }

        $ret_val['status_fact']  = $estado;
        $ret_val['mensaje_fact'] = $mensaje;

    }

    curl_close($ch);

    //$ret_val['status_fact']  = 'OK';
    //$ret_val['mensaje_fact'] = 'BIEN!!';

    return $ret_val;



}

function buscaUsuarioWS($db, $codigo_agencia){

    $sql = "SELECT usuario, clave from usuarios_addon WHERE codigo_agencia ='$codigo_agencia'";
    $res = mysqli_query($db, $sql);
	if(!$res){
		echo "ERROR buscaUsuarioWS<br>";
		echo "Error: " . mysqli_error($db);
		die();
	}

    $row = mysqli_fetch_assoc($res);

    return $row;

}


function dep($data){
	$format = print_r('<pre>');
	$format .= print_r($data);
	$format .= print_r('</pre>');
	return $format;
}

ob_end_flush();
?>

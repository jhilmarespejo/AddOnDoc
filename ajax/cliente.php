<?php
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}


require_once "../modelos/Cliente.php";
require_once "../modelos/Varios.php";
require_once "../modelos/Consultas.php";

$cliente  = new Cliente();
$varios   = new Varios();
$consulta = new Consultas();

$id_usuario        = $_SESSION['idusuario'];
$codigo_canal      = $_SESSION['codigo_canal'];
$codigo_agencia    = $_SESSION['codigo_agencia'];
$fechaInicio       = date('Y-m-d');
date_default_timezone_set('America/La_Paz');




// Captura de datos POST
$encontrado        = isset($_POST["encontrado"])        ? limpiarCadena($_POST["encontrado"])        : "";
$planes            = isset($_POST["planes"])            ? limpiarCadena($_POST["planes"])            : "";
$id_cliente        = isset($_POST["id_cliente"])        ? limpiarCadena($_POST["id_cliente"])        : "";
$tipo_documento    = isset($_POST["tipo_documento"])    ? limpiarCadena($_POST["tipo_documento"])    : "";
$num_documento     = isset($_POST["num_documento"])     ? limpiarCadena($_POST["num_documento"])     : "";
$extension         = isset($_POST["extension"])         ? limpiarCadena($_POST["extension"])         : "";
$expedido          = isset($_POST["expedido"])          ? limpiarCadena($_POST["expedido"])          : "";
$ap_paterno        = isset($_POST["ap_paterno"])        ? limpiarCadena($_POST["ap_paterno"])        : "";
$ap_materno        = isset($_POST["ap_materno"])        ? limpiarCadena($_POST["ap_materno"])        : "";
$nombres           = isset($_POST["nombres"])           ? limpiarCadena($_POST["nombres"])           : "";
$genero            = isset($_POST["genero"])            ? limpiarCadena($_POST["genero"])            : "";
$fecha_nacimiento  = isset($_POST["fecha_nacimiento"])  ? limpiarCadena($_POST["fecha_nacimiento"])  : "";
$num_telefono      = isset($_POST["telefono"])          ? limpiarCadena($_POST["telefono"])          : "";
$numero_prestamo   = isset($_POST["numero_prestamo"])   ? limpiarCadena($_POST["numero_prestamo"])   : "";
$codigo_renovacion = isset($_POST["codigo_renovacion"]) ? limpiarCadena($_POST["codigo_renovacion"]) : "";
$donde             = isset($_POST["donde"])             ? limpiarCadena($_POST["donde"])             : "";


//$num_documento='0062436';
//$encontrado = 'NO';
//$tipo_documento = 'E';
//$donde = 'P';

// comentar en producción 
// $encontrado = 'NO';
// $planes = 'PPCE0125';
// // $id_cliente = '14';
// $tipo_documento = 'C';
// $num_documento = '3391781-1V';
// $extension = "";
// $ap_paterno = 'GUARDIA';
// $ap_materno = 'DE TERAN';
// $nombres = 'JAQUELINE';
// $genero = 'F';
// $fecha_nacimiento = '1959-06-02';
// $num_telefono = '76754521';
// $numero_prestamo = '2345678';
// $id_usuario = '139';
// $cod_cli = '95181';
// $donde = 'P';

// $_GET["op"] = 'guardarContratante';




switch ($_GET["op"]){

	case 'verificaRegistroPrevio':
		
		if ($numero_prestamo == '') {
			// Verifica si el registro sin número de préstamo ya existe
			$registroSinPrestamo = $cliente->verificarRegistroSinPrestamo($num_documento, $ap_paterno, $ap_materno, $fecha_nacimiento);

			if ($registroSinPrestamo['duplicado'] == 1) {
				die(json_encode([
					'status' => 'error',
					'message' => 'El cliente ya fue registrado previamente sin número. Intentar nuevamente?',
					'duplicado' => true
				]));
			} if ($registroSinPrestamo['duplicado'] == 2) {
				die(json_encode([
					'status' => 'error',
					'message' => 'El cliente ya fue registrado hoy',
					'duplicado' => true,
					'button' => false,
				]));
			}
			else {
				die(json_encode([
					'status' => 'ok',
					'message' => 'Registro válido.',
					'duplicado' => false
				]));
			}
		}
		else{
			
			$prestamoExiste = $cliente->verificarPrestamoExistente(
				$numero_prestamo,
				$num_documento,
				$fecha_nacimiento,
				$ap_paterno,
				$ap_materno
			);

			// dep($prestamoExiste); //exit;

			if ($prestamoExiste) {
				$fechaRegistro = substr($prestamoExiste['fechaRegistro'], 0, 10);
				$hoy = date('Y-m-d');

				// 1. El cliente ya tiene un registro hoy → no permitido
				if ($prestamoExiste['documento'] == $num_documento && $fechaRegistro === $hoy) {
					die(json_encode([
						'status' => 'error',
						'message' => 'El cliente ya fue registrado hoy',
						'button' => false,
					]));
				}

				// 2. El préstamo ya está asignado a otro cliente → no permitido
				if ($prestamoExiste['numPrestamo'] == $numero_prestamo && $prestamoExiste['documento'] != $num_documento) {
					die(json_encode([
						'status' => 'error',
						'message' => 'El número ' . $numero_prestamo . ' ya está asignado a otro cliente. Intentar nuevamente?'
					]));
				}
			}

			// No hay registros duplicados → válido
			die(json_encode([
				'status' => 'ok',
				'message' => ''
			]));

		}
		
		
		//echo json_encode($data);
	break;	
	
	case 'guardarContratante':
		date_default_timezone_set('America/La_Paz');
		$codigo_canal = $_SESSION['codigo_canal'];
		$codigo_agencia = $_SESSION['codigo_agencia'];
		$fechaInicio = date('Y-m-d');
		$tipoBanca = 'COMUNAL';
		// se asigna la variable $planes
		if($planes == 'PC0075'){
			$codigo_canal='C001';
			$tipoBanca = '1';
			$planes='PPAB0049';
		} else if ($planes == 'PC0069'){
			$planes='PPCE0125';
		}
		
		$data = ['status' => 'error', 'message' => 'Error desconocido'];
		
		try {
			
			if($encontrado == 'NO') {
				$cod_cli = $varios->getParameterValues('cod_cli');
				
				if($donde == 'P') {
					$dataBDPM = buscarCliente_en_BDPM($num_documento, $extension, $tipo_documento);
					
					if($dataBDPM['status'] == 'ok') {
						// Actualizar variables con datos de BDPM
						$ap_paterno = $dataBDPM['ap_paterno'] ?? $ap_paterno;
						$ap_materno = $dataBDPM['ap_materno'] ?? $ap_materno;
						$nombres = $dataBDPM['nombres'] ?? $nombres;
						$num_documento = $dataBDPM['num_documento'] ?? $num_documento;
						$tipo_documento = $dataBDPM['tipo_documento'] ?? $tipo_documento;
						$genero = $dataBDPM['genero'] ?? $genero;
						$fecha_nacimiento = $dataBDPM['fecha_nacimiento'] ?? $fecha_nacimiento;
						$num_telefono = $dataBDPM['telefono'] ?? $num_telefono;
					}
				}
				
				// Insertar nuevo registro
				$datoInsertado = $cliente->insertar(
					$id_usuario, $numero_prestamo, $codigo_canal, $codigo_agencia,
					$tipoBanca, 'C', $num_documento, $extension, $expedido,
					$ap_paterno, $ap_materno, $nombres, $fecha_nacimiento,
					$genero, $num_telefono, $planes, $fechaInicio
				);
				
			} else {
				// CASO: $encontrado == 'SI' (cliente ya existe en el sistema)
				// Insertar directamente
				$datoInsertado = $cliente->insertar(
					$id_usuario, $numero_prestamo, $codigo_canal, $codigo_agencia,
					$tipoBanca, 'C', $num_documento, $extension, $expedido,
					$ap_paterno, $ap_materno, $nombres, $fecha_nacimiento,
					$genero, $num_telefono, $planes, $fechaInicio
				);
			}
			
			// PROCESAMIENTO COMÚN PARA AMBOS CASOS 
			if ($datoInsertado) {
				$resultado = $cliente->procesarRegistroVit($numero_prestamo, $num_documento, $id_usuario, $datoInsertado);
				$data = [
					'status' => $resultado['success'] ? 'ok' : 'error',
					'message' => $resultado['message'] ?? ($encontrado == 'SI' 
						? 'Registro actualizado correctamente' 
						: 'Registro creado correctamente'),
					'numero_prestamo' => $numero_prestamo
				];
			} else {
				$data = ['status' => 'error', 'message' => 'Error al insertar el registro'];
			}
			
		} catch (Exception $e) {
			$data = ['status' => 'error', 'message' => 'Error en el proceso: ' . $e->getMessage()];
		}
		
		echo json_encode($data);
		
		// echo "COD CANAL: " . $codigo_canal . "<br>";
			// echo "COD AGENCIA: " . $codigo_agencia . "<br>";
			// echo "COD PLAN: " . $planes . "<br>";
			

			// Obtenemos el precio del Plan
			// $rspta = $varios->getPrecioDelPlan($planes,$codigo_canal);
			// $data_plan = mysqli_fetch_assoc($rspta);
			// $deuda = $data_plan['precio_padre'];
			
			// // dep($data_plan);
			
			// // Obtenemos TODOS los datos del cliente  y sacamos FEC NAC y GENERO//
			// $dataCust = $varios->obtieneDataCust($new_registro_tit);
			// $genero = $dataCust['genero'];
			// $fec_nac = $dataCust['fecha_nacimiento'];
			// // dep($dataCust);
			// //die();



			// if($planes == 'PC0079'){  // PLAN UNICO
			// 	$res = $varios->obtieneCodPlanHijo($genero, $fec_nac);
			// }else{
			// 	$res = $varios->buscaCodPlanHijo($planes);
			// }
			// $codigo_plan_hijo = $res['codigo_plan_hijo'];
			// // echo "COD PLAN HIJO: ". $codigo_plan_hijo . "<br>";
			// //die();


			// $ndx_temp = $varios->insertar_temp($id_usuario,$new_registro_tit,$planes,$codigo_plan_hijo,$fecha_creacion,$deuda,$codigo_canal,
			// 					$cedula_asesor,$codigo_agencia);

			
			
			// $data['id_cliente'] = $new_registro_tit;
			// $data['encontrado'] = $encontrado;
			// $data['id_temp']   = $ndx_temp;
			// $data['plan']   = $planes;
			// $data['deuda'] = $deuda;
			// $data['nombre'] = $nombres . ' ' . $ap_paterno . ' ' . $ap_materno;
			
			// $_SESSION['temp']   = $ndx_temp;
			
	
	
	break;


	case 'verificar_cliente_en_PM':


		//$num_documento = '12421039';

		//echo "<br>CEDULA No: " . $num_documento . "<br>";


		$data = buscarCliente_en_BDPM($num_documento,$extension,$tipo_documento);
/*
		dep($data);

                $status = $data['status'];

                if($status != 'ok'){

			$data['status'] = 'error';
                        $data['mensaje'] = 'Cliente no encontrado!';
                }


		$data['status'] = 'ok';
		$data['ap_paterno'] = 'BUSTILLOS';
		$data['ap_paterno'] = 'GONZALES';
		$data['nombres'] = 'YARA ADRIANA';
*/
		echo json_encode($data);

	break;


	case 'consulta_clientes_antiguos':

		//$num_documento = '8955230';
		//echo "CEDULA: " . $num_documento . "<br>";

		date_default_timezone_set('America/La_Paz');
                $fecha_hoy = date('Y-m-d');


		if($tipo_documento == 'E'){
			$num_documento = 'E-'.$num_documento;
		}
		$result = $consulta->consulta_clientes_antiguos($num_documento);
		$cant = mysqli_num_rows($result);

		//$res = mysqli_fetch_assoc($result);
		//$fec_pac = $res['fecha_fin'];


		//$fec_hoy_y = substr($fecha_hoy,0,4);
		//$fec_hoy_m = substr($fecha_hoy,5,2);
		//$fec_hoy_d = substr($fecha_hoy,8,2);


		//echo "FECHA HOY: " . $fecha_hoy . "<br>";
 		//echo "Y_HOY: " . $fec_hoy_y . "<br>";
		//echo "M_HOY: " . $fec_hoy_m . "<br>";
		//echo "D_HOY: " . $fec_hoy_d . "<br>";

		//$fec_new_y = $fec_hoy_y - 1;
		//$fec_new = $fec_new_y . '-' . $fec_hoy_m . '-' . $fec_hoy_d;

		//echo "FECHA HOY: " . $fecha_hoy . "<br>";
		//echo "FECHA NEW: " . $fec_new . "<br>";
		//echo "FECHA PAC: " . $fec_pac . "<br>";
		//if($fec_pac > $fec_new){
		//	echo "PACIENTE HABILITADO<br>";
		//}else{
		//	echo "NO HABILITADO<br>";
		//}

		if($cant == 0){
			$data['status'] = 'error';
			$data['mensaje'] = 'Cliente no encontrado!';
		}else{
			$res = mysqli_fetch_assoc($result);
			$data['status'] = 'ok';
			$data['nombre'] = $res['nombre'];
			$data['cedula'] = $res['cedula'];
			$data['codigo_plan'] = $res['codigo_plan'];
			$data['plan'] = $res['plan'];
			$data['fecha_fin'] = $res['fecha_fin'];


			$fec_pac = $res['fecha_fin'];
	                $fec_hoy_y = substr($fecha_hoy,0,4);
        	        $fec_hoy_m = substr($fecha_hoy,5,2);
                	$fec_hoy_d = substr($fecha_hoy,8,2);

			$fec_new_y = $fec_hoy_y - 1;
	                $fec_new = $fec_new_y . '-' . $fec_hoy_m . '-' . $fec_hoy_d;

			if($fec_pac > $fec_new){
				$data['habilitado'] = 'S';
        	        }else{
				$data['habilitado'] = 'N';
                	}


		}

		//dep($data);
		echo json_encode($data);



	break;

}


function buscarCliente_en_BDPM($cedula,$extension,$tipo_documento){

	//echo "CEDULA: " . $cedula . "<br>";
	//echo "TIP DOC: " . $tipo_documento . "<br>";
	$data = array();

	//---------------------------------------------------------//
	// LLAMAMOS Al WS DE PROMUJER PARA PEDIR DATOS DEL CLIENTE //
	// --------------------------------------------------------//
	if($tipo_documento == 'E'){
        	$cedula = 'E-'.$cedula;
        }

	if(is_null($extension)){
                $extension = '';
        }
        $cedula = $cedula . $extension;

	$res = buscaClienteEnPM($cedula);
	//dep($res);

	// Rescatamos el resultado dependiendo si nos devuleve un objeto o no.
	$estado = (isset($res->estado))?$res->estado:$res['estado'];


	if($estado == 'E'){
		$dataPM['id'] = '';

		$data['status'] = 'ok';
                $data['donde'] = 'P';

		$data['ap_paterno'] = trim($res->paterno);
		$data['ap_materno'] = trim($res->materno);

		$res->nombre2 = trim($res->nombre2);
		$len = strlen($res->nombre2);
		$nombre = trim($res->nombre1);
		if($len > 0){
			$nombre = $nombre . ' ' . trim($res->nombre2);
		}
		$data['nombres'] = $nombre;

		// Limpiamos la Cedula
		$cedula_data = limpiaCedula($res->documento);

		$data['num_documento']  = trim($cedula_data['ced']);
		$data['extension']      = trim($cedula_data['ext']);
		$data['tipo_documento'] = trim($cedula_data['tip']);


		if($res->sexo==0){
			$data['genero'] = 'F';
		}else{
			$data['genero'] = 'M';
		}
		$data['fecha_nacimiento'] = substr($res->fechaNac,0,10);
		$data['telefono'] = trim($res->celular);
		$data['correo'] = '';

	}else{

		//echo "Cliente No Encontrado<br>";
		$data['status'] = 'Cliente no encontrado';
		$data['result'] = '';

	}

	return $data;

}


function limpiaCedula($cedula){

        $cedula = trim($cedula);
        $len = strlen($cedula);

	$ret_tipoDoc = 'C';

	if($cedula[0] == 'E'){

		$cedula = substr($cedula,2,$len);
		$ret_cedula = $cedula;
		$ret_extension = '';
		$ret_expedido = 'E';
		$ret_tipoDoc = 'O';


	}else{

		$expedido = substr($cedula,$len-2,2);
		$extension = substr($cedula,$len-4,2);
		// echo "CEDULA: " . $cedula . "<br>";
		// echo "LEN: " . $len . "<br>";

		// echo "<br>EXPEDIDO: " . $expedido . "<br>";

		$exp_number0 = preg_match('@[0-9]@', $expedido[0]);
		$exp_number1 = preg_match('@[0-9]@', $expedido[1]);
		// echo "EXP NUM0: " . $exp_number0 . "<br>";
			// echo "EXP NUM1: " . $exp_number1 . "<br>";

		$ext_number0 = preg_match('@[0-9]@', $extension[0]);
		$ext_number1 = preg_match('@[0-9]@', $extension[1]);
		// echo "<br>EXTENSION: " . $extension . "<br>";
		// echo "EXT NUM0: " . $ext_number0 . "<br>";
		// echo "EXT NUM1: " . $ext_number1 . "<br>";


		if($exp_number0 == 0 && $exp_number1 == 0){
			// Tiene Expedición
			$ret_expedido = $expedido;

			if($ext_number0 == 1 && $ext_number1 == 0){
				// Tiene Extension
				$ret_extension = $extension;
				$ret_cedula = substr($cedula,0,$len-4);

			}else{

				$ret_extension = '';
				$ret_cedula = substr($cedula,0,$len-2);

			}

		}else if($exp_number0 == 1 && $exp_number1 == 0){
			// No tiene EXPEDIDO
			$ret_expedido = '';

			$extension = substr($cedula,$len-2,2);
			$ext_number0 = preg_match('@[0-9]@', $extension[0]);
			$ext_number1 = preg_match('@[0-9]@', $extension[1]);
			// echo "<br><br><br>EXTENSION: " . $extension . "<br>";
			// echo "EXT NUM0X: " . $ext_number0 . "<br>";
			// echo "EXT NUM1X: " . $ext_number1 . "<br>";

			$ret_extension = substr($cedula,$len-2,2);
			// echo "CEDULAXX: " . $cedula . "<br>";
			// echo "LENXX: " . $len . "<br>";
			// echo "RET EXTXX: " . $ret_extension . "<br>";
			$ret_cedula = substr($cedula,0,$len-2);


		}else{

			$ret_cedula = $cedula;
			$ret_extension = '';
			$ret_expedido = '';
		}

	}
	// echo "<br>RET CED: " . $ret_cedula . "<br>";
	// echo "RET EXT: " . $ret_extension . "<br>";
	// echo "RET EXP: " . $ret_expedido . "<br>";

	$data_cedula = array();
	$data_cedula['ced'] = $ret_cedula;
	$data_cedula['ext'] = $ret_extension;
	$data_cedula['exp'] = $ret_expedido;
	$data_cedula['tip'] = $ret_tipoDoc;
        //die();

	return $data_cedula;


}


function buscaClienteEnPM($cedula){
	//echo "buscaClientePM: ". $cedula . "<br>";

	$wsdl = "http://172.18.1.31:8080/innova/DatosClienteWS?wsdl";

	// Parámetros de configuración para el cliente SOAP
	$options = array(
		'trace' => 1, // Habilitar el seguimiento de solicitudes y respuestas para depuración
		'exceptions' => true // Habilitar excepciones para manejar errores
	);

	try {
		// Crear instancia de SoapClient
		$client = new SoapClient($wsdl, $options);

		// Llamar a un método del servicio web SOAP
		//$result = $client->NombreDelMetodoSOAP(array('parametro1' => 'valor1', 'parametro2' => 'valor2'));
		$result = $client->cliente(array('user' => 'CUH', 'psw' => '123', 'TipoDoc' => 'C', 'NumDoc' => $cedula, 'TipoUsuario' => 'N', 'Canal' => '1'));

		// Manejar la respuesta
		//var_dump($result);
		return $result->return;

	} catch (SoapFault $e) {
		// Capturar errores SOAP
		//echo "Error: " . $e->getMessage();
		$result = array();
		$result['estado'] = 'X';
		$result['mensaje'] = $e->getMessage();
		return $result;
	} catch (Exception $e) {
		// Capturar otros errores
		//echo "Error: " . $e->getMessage();
		$result = array();
		$result['estado'] = 'X';
		$result['mensaje'] = $e->getMessage();
		return $result;
	}
}


function dep($data){
	$format = print_r('<pre>');
	$format .= print_r($data);
	$format .= print_r('</pre>');
	return $format;
}



ob_end_flush();
?>

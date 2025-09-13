<?php

require_once('../config/Conexion.php');

if(!empty($_POST['cedula'])){
//if(!empty($_GET['cedula'])){

	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	error_reporting(E_ALL);

	$data = array();

	$extension = $_POST['extension'];
    if(is_null($extension)){
        $extension = '';
    }
    $cedula = $_POST['cedula'] . $extension;
	// $tipo_documento = $_POST['tipo_documento'];
	$tipo_documento = "C";

	//$tipo_documento = $_GET['tipo_documento'];
	//$cedula = $_GET['cedula'];

	error_log("buscarCliente.php: cedula: " . $cedula);

	// Search for user data in local database first
	$sql = "SELECT id, paterno,materno, nombre1,nombre2,documento, extension,tipoDoc, fechaNac, genero, celular, numPrestamo FROM vit_original WHERE documento = '$cedula'";

	
	//echo "SQL: " . $sql . "<br>";
	
	$result = mysqli_query($conexion, $sql);
	if(!$result){
		echo "Error mysqli: " . mysqli_error($conexion);
		die('Parando!!!');
	}

    $cnt = mysqli_num_rows($result);
    //echo "COUNT:" . $cnt . "<br>";
	//die();
	error_log("buscarCliente.php: CANTIDAD: " . $cnt);

	if($cnt > 0){
        	$userData = mysqli_fetch_assoc($result);
        	$data['status'] = 'ok';
        	$data['donde'] = 'I';
        	$data['result'] = $userData;
    }else{

			//---------------------------------------------------------//
			// LLAMAMOS Al WS DE PROMUJER PARA PEDIR DATOS DEL CLIENTE //
			// --------------------------------------------------------//
			if($tipo_documento == 'E'){
				$cedula = 'E-'.$cedula;
			}
			
			$res = buscaClienteEnPM($cedula);
			//dep($res);
			//die();

			// Rescatamos el resultado dependiendo si nos devuleve un objeto o no.
			$estado = (isset($res->estado))?$res->estado:$res['estado'];

			//echo "Estado1: " . $estado . "<br>";
			// CASO QUE SE HAYA ENCONTRADO AL CIENTE EN PM
			// $estado = 'X';

			if($estado == 'E'){
				$dataPM['id'] = '';
				$dataPM['ap_paterno'] = trim($res->paterno);
				$dataPM['ap_materno'] = trim($res->materno);

				$res->nombre2 = trim($res->nombre2);
				$len = strlen($res->nombre2);
				$nombre = trim($res->nombre1);
				if($len > 0){
					$nombre = $nombre . ' ' . trim($res->nombre2);
				}
				$dataPM['nombres'] = $nombre;

				// Limpiamos la Cedula
				$cedula_data = limpiaCedula($res->documento);

				$dataPM['num_documento']  = trim($cedula_data['ced']);
				$dataPM['extension']      = trim($cedula_data['ext']);
				$dataPM['tipo_documento'] = trim($cedula_data['tip']);


				if($res->sexo==0){
					$dataPM['genero'] = 'F';
				}else{
					$dataPM['genero'] = 'M';
				}
				$dataPM['fecha_nacimiento'] = substr($res->fechaNac,0,10);

				$telefono = trim($res->celular);
				$len = strlen($telefono);
				if($len == 0){
					$telefono = '12345678';
				}
				$dataPM['telefono'] = $telefono;
				$dataPM['correo'] = '';

				$data['status'] = 'ok';
				$data['donde'] = 'P';
				$data['result'] = $dataPM;

			}else{

				//echo "Cliente No Encontrado<br>";
				$data['status'] = 'Cliente no encontrado';
				$data['result'] = '';

			}

    	}

	
	//returns data as JSON format
	$rc = 'marcelo';
    //echo json_encode($data);
	error_log("Data Return: " . print_r($data, true));
	echo json_encode($data);
	//dep($data);

}else{
	echo "<script>alert('NO HAY POST ID')</script>";
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

?>

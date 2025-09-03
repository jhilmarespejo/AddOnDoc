<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Anulaciones
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	public function buscaRegistros($fecha_actual,$codigo_canal)
	{

		$sql = "SELECT t.id, a.nombre_agencia as agencia, k.nombre_ciudad as ciudad,
					(SELECT DISTINCT descripcion_plan_padre FROM plan_padre WHERE codigo_plan_padre=t.codigo_plan) as plan,
					t.precio, t.fecha_creacion as fechaInicio,
					CONCAT(nombres, ' ',ap_paterno, ' ',ap_materno) cliente, c.tipo_documento as documento, c.num_documento as cedula, c.genero, c.telefono, t.estado
				FROM temp t, clientes c, usuario u, agencias a, ciudades k
				WHERE t.id_contratante = c.id
				AND t.id_usuario = u.idusuario
				AND u.codigoAlmacen = a.codigo_agencia
				AND a.codigo_ciudad = k.id
				AND DATE(t.fecha_creacion) = '$fecha_actual'
				AND t.codigo_canal = '$codigo_canal'
				ORDER by t.fecha_creacion desc";

		return ejecutarConsulta($sql);
	}

	public function anularAdmision($id_admision, $id_usuario){

		require "../config/Conexion.php";
		date_default_timezone_set('America/La_Paz');
		$fec_anula = date('Y-m-d H:i:s');

		$sql = "UPDATE temp SET estado = 'A', fecha_anulacion = '$fec_anula', cobranza = 'ANULADO',facturacion='ANULADO',
					usuario_anulacion = '$id_usuario' WHERE id = '$id_admision'";

		//echo "SQL: " . $sql . "<br>";
		$resultado = mysqli_query($conexion, $sql);
		$cant = mysqli_affected_rows($conexion);
		if($cant > 0){
			$rspta['status'] = 'ok';
			$rspta['cant'] = $cant;
		}else{
			$rspta['status'] = 'error';
			$rspta['cant'] = $cant;
		}

		//dep($rspta);
		//return ejecutarConsulta($sql);
		return $rspta;

	}

	function dep($data){
		$format = print_r('<pre>');
		$format .= print_r($data);
		$format .= print_r('</pre>');

		return $format;
	}
	public function getDataInfo($id_admision){

		$sql = "SELECT t.id, c.nombres, c.ap_paterno, c.num_documento as cedula, t.codigo_tra, 
					t.codigo_plan, p.descripcion_plan_padre, t.fecha_creacion
				FROM temp t, clientes c, plan_padre p
				WHERE t.id_contratante = c.id
				AND t.codigo_plan = p.codigo_plan_padre
				AND t.id = '$id_admision'";

		return ejecutarConsultaSimpleFila($sql);

	}

	public function validamosFacturaServicios($data)
	{

		//var_dump($data);

		// ----------------------------------------------------------- //
		//Configuración de conexión al Service Layer de SAP B1 - LOGIN //
		// ----------------------------------------------------------- //
		$host = "https://52.177.52.183";
		$port = 50000;

		$username = "GETSAP\\innova07";
		$password = "MARCOlazarte#3872$";
		$companyDB = "INNOVASALUD";

		// Datos de autenticación
		$authData = array(
			"UserName" => $username,
			"Password" => $password,
			"CompanyDB" => $companyDB
		);

		// Convertir los datos de autenticación a JSON
		$authJson = json_encode($authData);

		//dep($authData);
		// Inicializar cURL para autenticación
		//echo "LLAMANDO CURL_INIT<br>";
		$ch = curl_init();
		//echo " VOLVI DEL CURL_INIT<br>";
		curl_setopt($ch, CURLOPT_URL, "{$host}:{$port}/b1s/v1/Login");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Usar solo en desarrollo, NO en producción
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Usar solo en desarrollo, NO en producción
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $authJson);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($authJson)
		));

		// Ejecutar la solicitud de autenticación
		$response = curl_exec($ch);

		if (curl_errno($ch)) {
			//echo 'Error en la solicitud curl:' . curl_error($ch);
			$agregaResponse = ([
				"msg" => 'Error de autenticación: ' . curl_error($ch),
				"statusCode" => 404
			]);
			curl_close($ch);
			return $agregaResponse;
		}

		// Decodificar la respuesta JSON
		$loginResponse = json_decode($response, true);

		// Verificar si la autenticación fue exitosa
		if (!isset($loginResponse['SessionId'])) {
			//echo "Error de autenticación: " . $loginResponse['error']['message']['value'];
			$agregaResponse = ([
				"msg" => 'Error de autenticación: ' . curl_error($ch),
				"statusCode" => 404
			]);
			curl_close($ch);
			return $agregaResponse;
		}

		// Obtener el SessionId para usar en las solicitudes futuras
		$sessionId = $loginResponse['SessionId'];
		//echo " Session ID: " . $sessionId . "<br>";


		//die();


		// ---------------------------------------------- //
	        // 1 Verificamos si la factura está activa en SAP //
        	// ---------------------------------------------- //
		$cedula  = $data['num_documento'];
		$cod_cli = $data['codigo_cli'];
		$cod_tra = $data['codigo_tra'];
		$cod_ope = $data['codigo_ope'];

		//var_dump($data);

		$endpoint = "/b1s/v1/Invoices?\$filter=U_NIT%20eq%20'".$cedula."'%20and%20U_CodExtCliente%20eq%20'".$cod_cli."'%20and%20U_NumTransaccionExt%20eq%20'".$cod_tra."'%20and%20U_IdOperacionExt%20eq%20'".$cod_ope."'&\$select=U_ESTADOFC";
        	// Construir la URL completa
        	$url = "{$host}:{$port}{$endpoint}";

		//echo "<br><br>URL: " . $url . "<br>";

        	curl_setopt($ch, CURLOPT_URL, $url);
        	curl_setopt($ch, CURLOPT_HTTPGET, true);
        	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            	    'Content-Type: application/json',
            	    "Cookie: B1SESSION={$sessionId}"
        	));

        	$response = curl_exec($ch);
		//var_dump($response);

		if (curl_errno($ch)) {
			$agregaResponse = ([
					"msg" => 'Error en la solicitud GET: ' . curl_error($ch),
					"statusCode" => 404
				]);
				curl_close($ch);
				return $agregaResponse;
		}

		// Decodificar la respuesta JSON de los clientes
		$respuesta = json_decode($response, true);
		//echo "<br><br><br><br>CERO<br>";
		//var_dump($respuesta);
		$estado = $respuesta['value'][0]['U_ESTADOFC'];

		if($estado != 'V')
		{
		    $rspta['estado'] = 'X';
		    $rspta['msg'] = 'Factura no está válida';
		    return $rspta;
		}

		//echo "<br><br>ESTADO: " . $estado . "<br><br>";
		//die();


		// ----------------------------------------------- //
	        // 2 Verificamos si tomó algún servicio de su plan //
        	// ----------------------------------------------- //
		$endpoint = "/b1s/v1/\$crossjoin(U_ECON,U_EMEVH,BusinessPartners,Invoices)?\$expand=U_EMEVH(\$select=U_ItemCode,U_ItemName)&\$filter=U_ECON/U_eDocEntry%20eq%20U_EMEVH/U_eDocEntry%20and%20BusinessPartners/CardCode%20eq%20U_ECON/U_CardCode%20and%20Invoices/DocNum%20eq%20U_EMEVH/U_DocNum%20and%20Invoices/DocEntry%20eq%20U_EMEVH/U_DocEntry%20and%20U_EMEVH/U_ObjType%20eq%2013%20and%20BusinessPartners/FederalTaxID%20eq%20'".$cedula."'%20and%20Invoices/U_ESTADOFC%20eq%20'V'%20and%20Invoices/U_CodExtCliente%20eq%20'".$cod_cli."'%20and%20Invoices/U_NumTransaccionExt%20eq%20'".$cod_tra."'%20and%20Invoices/U_IdOperacionExt%20eq%20'".$cod_ope."'";


		$url = "{$host}:{$port}{$endpoint}";

		//echo "URL1: " . $url . "<br>";

	        curl_setopt($ch, CURLOPT_URL, $url);
        	curl_setopt($ch, CURLOPT_HTTPGET, true);
	        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            	    'Content-Type: application/json',
	            "Cookie: B1SESSION={$sessionId}"
        	));

        	$response = curl_exec($ch);

		if (curl_errno($ch)) {
			$agregaResponse = ([
				"msg" => 'Error en la solicitud GET: ' . curl_error($ch),
				"statusCode" => 404
			]);
			curl_close($ch);
			return $agregaResponse;
		}

		// Decodificar la respuesta JSON de los clientes
		$respuesta = json_decode($response, true);
		//var_dump($respuesta);
		$servicios_tomados = count($respuesta['value']);
		//echo "<br><br>SERVICIOS TOMADOS: " . $servicios_tomados;

		if($servicios_tomados > 0)
		{
		    $rspta['estado'] = 'X';
		    $rspta['msg'] = 'El cliente tomo: '. $servicios_tomados .' servicios. No se puede anular la factura';
		    return $rspta;
		}

		//echo "<br><br>DOS<br>";
		//var_dump($respuesta);
		$rspta['estado'] = 'E';
		$rspta['msg'] = 'Se puede anular la factura';
		//die();
		return $rspta;


	}

	public function obtenerDataParaAnular($id)
	{

		$sql = "SELECT c.ap_paterno, c.ap_materno, c.num_documento, t.*
					FROM temp t, clientes c
					WHERE t.id_contratante = c.id
					AND t.id = '$id'";

		return ejecutarConsultaSimpleFila($sql);

	}
}

?>

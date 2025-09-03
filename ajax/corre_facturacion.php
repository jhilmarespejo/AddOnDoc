<?php
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
//require_once "/var/www/html/AddOnInnova/modelos/Cobranzas.php";
//require_once "/var/www/html/AddOnInnova/modelos/Varios.php";
//require_once "/var/www/html/AddOnInnova/ajax/llamamosWS_Sartawi.php";


require_once "../modelos/Cobranzas.php";
require_once "../modelos/Varios.php";
require_once "llamamosWS_Sartawi.php";


$cobranzas = new Cobranzas();
$varios    = new Varios();


// FECHA INICIO VIGENCIA DEL PLAN A FACTURAR
date_default_timezone_set('America/La_Paz');
$fecha_inicio = date('Ymd');

// Buscamos que facturas están pendientes de Facturación
$res = $varios->buscaClientesParaFacturar();
if(!$res){
	echo "No hay Registros para facturar!!";
	dep($res);
}else{


	while($row = mysqli_fetch_assoc($res)){
		//dep($row);
		//die();

				//dep($row);
		$codigo_plan_padre = $row['codigo_plan'];
		$id_registro_a_facturar = $row['id'];

		// Leemos datos del PLAN PADRE
		$filas = $varios->readPlanPadre($codigo_plan_padre);
		$contador = 0;
		while ($fila = mysqli_fetch_assoc($filas)){
			$rs_plan[$contador++] = $fila;
		}
		//dep($rs_plan);

		$cod_ope = $varios->getParameterValues('cod_ope');
		$cod_tra = $varios->getParameterValues('cod_tra');

		//echo "Cod Ope: " . $cod_ope . "<br>";
		//echo "Cod Tra: " . $cod_tra . "<br>";

		$data = array();

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

			$data['codigo_plan'] = $rs_plan[0]['codigo_plan_hijo'];
			$data['precio'] = $rs_plan[0]['precio_hijo'];
			$data['fecha_inicio'] = $fecha_inicio;

			$horaLlamada = date('Y-m-d H:i:s');
			$rc = llamamosWS_Sartawi($data);
			$horaRetorno = date('Y-m-d H:i:s');

			// Recogemos el mensaje de retorno del WS Sartawi
			$data['status_fact']  = $rc['status_fact'];
			$data['mensaje_fact'] = $rc['mensaje_fact'];
			$data['hora_llamada'] = $horaLlamada;
			$data['hora_retorno'] = $horaRetorno;

			if($rc['status_fact'] == 'E'){
				// Actualizamos los campos FACTURACION, FECHA_FACTURACION y ESTADO de la table temp
				$rspta = $cobranzas->actualizamosCamposFacturacion($id_registro_a_facturar);
			}

			// Guardamos en el LOG los datos enviados al WS
			$rspta = $varios->registraDatosLog($data);



		//}else if($codigo_plan_padre == 'PC02'){
		}else{

			//--------------------------------//
			// Primera factura                //
			// Ej: Fecha_inicio = 2023-12-26  //
			//--------------------------------//
			$data['codigo_plan'] = $rs_plan[0]['codigo_plan_hijo'];
			$data['precio'] = $rs_plan[0]['precio_hijo'];
			$data['fecha_inicio'] = $fecha_inicio;

			//dep($data);

			$horaLlamada = date('Y-m-d H:i:s');
			$rc = llamamosWS_Sartawi($data);
			$horaRetorno = date('Y-m-d H:i:s');

			// Recogemos el mensaje de retorno del WS Sartawi
			$data['status_fact']  = $rc['status_fact'];
			$data['mensaje_fact'] = $rc['mensaje_fact'];
			$data['hora_llamada'] = $horaLlamada;
			$data['hora_retorno'] = $horaRetorno;

			//$rc['status_fact'] = 'E';
			if($rc['status_fact'] == 'E'){
				// Actualizamos los campos FACTURACION, FECHA_FACTURACION y ESTADO de la table temp
				$rspta = $cobranzas->actualizamosCamposFacturacion($id_registro_a_facturar);
			}

			// Guardamos en el LOG los datos enviados al WS
			$rspta = $varios->registraDatosLog($data);


			//--------------------------------//
			// Segunda factura                //
			// Ej: Fecha_inicio = 2024-12-26  //
			//--------------------------------//
			$fechaactual = date('Y-m-d'); // 2016-12-29
			$nuevafecha = strtotime ('+1 year' , strtotime($fechaactual)); //Se añade un año mas
			$nuevafecha = date ('Ymd',$nuevafecha);

			$data['cod_tra'] = $varios->getParameterValues('cod_tra');
			$data['fecha_inicio'] = $nuevafecha;
			$data['codigo_plan'] = $rs_plan[1]['codigo_plan_hijo'];
			$data['precio'] = $rs_plan[1]['precio_hijo'];

			//dep($data);

			$horaLlamada = date('Y-m-d H:i:s');
			$rc = llamamosWS_Sartawi($data);
			$horaRetorno = date('Y-m-d H:i:s');

			$data['status_fact']  = $rc['status_fact'];
			$data['mensaje_fact'] = $rc['mensaje_fact'];
			$data['hora_llamada'] = $horaLlamada;
			$data['hora_retorno'] = $horaRetorno;

			if($rc['status_fact'] == 'E'){
				// Actualizamos los campos FACTURACION, FECHA_FACTURACION y ESTADO de la table temp
				$rspta = $cobranzas->actualizamosCamposFacturacion($id_registro_a_facturar);
			}

			// Guardamos en el LOG los datos enviados al WS
			$rspta = $varios->registraDatosLog($data);


			//--------------------------------//
			// Tercera factura                //
			// Ej: Fecha_inicio = 2024-12-26  //
			//--------------------------------//
			$nuevafecha = strtotime ('+2 year' , strtotime($fechaactual)); //Se añade un año mas
			$nuevafecha = date ('Ymd',$nuevafecha);

			$data['cod_tra'] = $varios->getParameterValues('cod_tra');
			$data['fecha_inicio'] = $nuevafecha;
			$data['codigo_plan'] = $rs_plan[1]['codigo_plan_hijo'];
			$data['precio'] = $rs_plan[1]['precio_hijo'];

			//dep($data);

			$horaLlamada = date('Y-m-d H:i:s');
			$rc = llamamosWS_Sartawi($data);
			$horaRetorno = date('Y-m-d H:i:s');

			$data['status_fact']  = $rc['status_fact'];
			$data['mensaje_fact'] = $rc['mensaje_fact'];
			$data['hora_llamada'] = $horaLlamada;
			$data['hora_retorno'] = $horaRetorno;

			if($rc['status_fact'] == 'E'){
				//Actualizamos los campos FACTURACION, FECHA_FACTURACION y ESTADO de la table temp
				$rspta = $cobranzas->actualizamosCamposFacturacion($id_registro_a_facturar);
			}

			// Guardamos en el LOG los datos enviados al WS
			$rspta = $varios->registraDatosLog($data);



		}

	}
}
//echo json_encode($data);


function dep($data){
	$format = print_r('<pre>');
	$format .= print_r($data);
	$format .= print_r('</pre>');
	return $format;
}

ob_end_flush();
?>

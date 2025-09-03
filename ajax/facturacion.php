<?php
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
//require_once "/var/www/html/AddOnInnova/modelos/Cobranzas.php";
//require_once "/var/www/html/AddOnInnova/modelos/Varios.php";

require_once "../modelos/Cobranzas.php";
require_once "../modelos/Varios.php";

require_once "llamamosWS_Sartawi.php";

$cobranzas = new Cobranzas();
$varios    = new Varios();

$id_usuario = $_SESSION['idusuario'];


$id_registro = isset($_POST["id_registro"])? limpiarCadena($_POST["id_registro"]):"";



switch ($_GET["op"]){

	case 'ventasfecha':
		$fecha_inicio=$_REQUEST["fecha_inicio"];
		$fecha_fin=$_REQUEST["fecha_fin"];

		$rspta=$varios->consulta_facturacion_fecha($fecha_inicio,$fecha_fin);
 		//Vamos a declarar un array
 		$data= Array();

		$i = 1;
 		while ($reg=$rspta->fetch_object()){
			$no_anular = 0;
 			$data[]=array(
 				"0"=>($reg->estado =='C')?'<button class="btn btn-success" onclick="llamarWS_Facturacion('.$reg->id.')"><i class="fa fa-money"></i></button>':
				 ' <button class="btn btn-success" onclick="llamarWS_Facturacion('.$no_anular.')"><i class="fa fa-check"></i></button>',
 				"1"=>$reg->agencia,
 				"2"=>$reg->ciudad,
 				"3"=>$reg->plan,
 				"4"=>$reg->precio,
 				"5"=>$reg->cliente,
				"6"=>$reg->cedula,
				"7"=>$reg->fechaCobranzas,
				"8"=>$reg->fechaFacturacion,
				"9"=>($reg->estado == 'F')?'<span class="label bg-green">Facturado</span>':
				'<span class="label bg-green">Cobrado</span>'
 				/* "10"=>($reg->estado=='V')?'<span class="label bg-green">Vendido</span>':
 				'<span class="label bg-red">Anulado</span>' */
 				);
 		}
 		$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
 		echo json_encode($results);

	break;


	case 'llamarWS_Facturacion':

		// FECHA INICIO VIGENCIA DEL PLAN A FACTURAR
		date_default_timezone_set('America/La_Paz');
		$fecha_inicio = date('Ymd');

		$id_registro = 420;
		$rspta = $varios->getRegistroParaFacturar($id_registro);
		$row = mysqli_fetch_assoc($rspta);

		//dep($row);
        //die();

		$codigo_plan_padre = $row['codigo_plan'];
		$id_registro_a_facturar = $row['id'];

		// Leemos datos del PLAN PADRE
		$filas = $varios->readPlanPadre($codigo_plan_padre);
		$contador = 0;
		while ($fila = mysqli_fetch_assoc($filas)){
			$rs_plan[$contador++] = $fila;
		}
		dep($rs_plan);
        	//die();


		$data = array();

		$data['cod_cli'] = $row['cod_cli'];
		$data['cod_ope'] = $row['codigo_ope'];
		$data['cod_tra'] = $row['codigo_tra'];
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
		$data['direccion'] = 'N-A';
		$data['fecha_nacimiento'] = $row['fecha_nacimiento'];
		$data['codigo_plan'] = $row['codigo_plan'];
		$data['precio'] = $row['deuda'];
		$data['canal'] = $row['codigo_canal'];
		//$data['canal'] = 'C001';   //---------------------------------------------------------------
		$data['codigo_agencia'] = $row['codigo_agencia'];

        //dep($data);
        //die();

		if($contador == '1'){

            //echo "SOLO TIENE UNO. PLAN: " . $row['codigo_plan'] ."<br>";
            //echo "PLAN PADRE: " . $rs_plan[0]['descripcion_plan_padre'] ."<br>";
            //echo "PLAN HIJO : " . $rs_plan[0]['descripcion_plan_hijo'] ."<br><br>";


			$data['codigo_plan'] = $rs_plan[0]['codigo_plan_hijo'];
			$data['precio'] = $rs_plan[0]['precio_hijo'];
			$data['fecha_inicio'] = $fecha_inicio;

			$horaLlamada = date('H:i:s');
			$rc = llamamosWS_Sartawi($data);
			$horaRetorno = date('H:i:s');

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
    			dep($data);

    		$return_code = array();
            if($rc_code[0]){
                $return_code['status_fact'] = 'ok';
            }else{
                $return_code['status_fact'] = 'error';
            }


		}else{

            //echo "PLAN PADRE: " . $rs_plan[0]['descripcion_plan_padre'] ."<br>";
            //echo "PLAN HIJO : " . $rs_plan[0]['descripcion_plan_hijo'] ."<br><br>";

			//--------------------------------//
			// Primera factura                //
			// Ej: Fecha_inicio = 2023-12-26  //
			//--------------------------------//
			$data['codigo_plan'] = $rs_plan[0]['codigo_plan_hijo'];
			$data['precio'] = $rs_plan[0]['precio_hijo'];
			$data['fecha_inicio'] = $fecha_inicio;

			echo "FECHA INICIO0: " . $fecha_inicio . "<br>";

			$horaLlamada = date('H:i:s');
			$rc = llamamosWS_Sartawi($data);
			$horaRetorno = date('H:i:s');


			// Recogemos el mensaje de retorno del WS Sartawi
			$data['status_fact']  = $rc['status_fact'];
			$data['mensaje_fact'] = $rc['mensaje_fact'];
			$data['hora_llamada'] = $horaLlamada;
			$data['hora_retorno'] = $horaRetorno;

            		$rc_code = array();

			$rc['status_fact'] = 'E';
			if($rc['status_fact'] == 'E'){
				// Actualizamos los campos FACTURACION, FECHA_FACTURACION y ESTADO de la table temp
				$rspta = $cobranzas->actualizamosCamposFacturacion($id_registro_a_facturar);
				$rc_code[0] = true;

			}
			// Guardamos en el LOG los datos enviados al WS
			$rspta = $varios->registraDatosLog($data);
    			dep($data);
			die();


			//--------------------------------//
			// Segunda factura                //
			// Ej: Fecha_inicio = 2024-12-26  //
			//--------------------------------//

			$cantidad_hijo = $rs_plan[1]['cantidad_hijo'];
			//echo "CANTIDAD HIJO: " . $cantidad_hijo . "<br>";

			for($k = 0; $k < $cantidad_hijo; $k++){

                //echo "PLAN PADRE: " . $rs_plan[1]['descripcion_plan_padre'] ."<br>";
                //echo "PLAN HIJO".($k+1)." : " . $rs_plan[1]['descripcion_plan_hijo'] ."<br><br>";

			    $adicion_anio = '+'.($k+1).' year';
    			//$nuevafecha = strtotime ('+1 year' , strtotime($fecha_inicio)); //Se añade un año mas
    			$nuevafecha = strtotime ($adicion_anio , strtotime($fecha_inicio)); //Se añade un año mas
    			$nuevafecha = date ('Ymd',$nuevafecha);
    			
    			//echo "ADICION AÑO: " . $adicion_anio . "<br>";
    			//echo "FECHA INICIO".($k+1). ": " . $fecha_inicio . "<br>";
    			//echo "NUEVA FECHA".($k+1) . " :" . $nuevafecha . "<br>";
			    
    			$data['cod_tra'] = $varios->getParameterValues('cod_tra');
    			$data['fecha_inicio'] = $nuevafecha;
    			$data['codigo_plan'] = $rs_plan[1]['codigo_plan_hijo'];
    			$data['precio'] = $rs_plan[1]['precio_hijo'];
			    
    			$horaLlamada = date('H:i:s');
    			$rc = llamamosWS_Sartawi($data);
    			$horaRetorno = date('H:i:s');
    
    			$data['status_fact']  = $rc['status_fact'];
    			$data['mensaje_fact'] = $rc['mensaje_fact'];
    			$data['hora_llamada'] = $horaLlamada;
    			$data['hora_retorno'] = $horaRetorno;
    
    			if($rc['status_fact'] == 'E'){
    				// Actualizamos los campos FACTURACION, FECHA_FACTURACION y ESTADO de la table temp
    				$rspta = $cobranzas->actualizamosCamposFacturacion($id_registro_a_facturar);
                    $rc_code[$k+1] = true;
    			}
    			// Guardamos en el LOG los datos enviados al WS
    			$rspta = $varios->registraDatosLog($data);
        		//dep($data);


			}

            $return_code = array();
            if($rc_code[0] && $rc_code[1] && $rc_code[2] == true){
                $return_code['status_fact'] = 'ok';
            }else{
                $return_code['status_fact'] = 'error';
            }

		}

		//echo json_encode($data);
		echo json_encode($return_code);

	break;

	case 'generarFactura':


		// FECHA INICIO VIGENCIA DEL PLAN A FACTURAR
		date_default_timezone_set('America/La_Paz');
		$fecha_inicio = date('Ymd');

		// Buscamos que facturas están pendientes de Facturación
		$res = $varios->buscaClientesParaFacturar();

		while($row = mysqli_fetch_assoc($res)){
			//dep($row);

			//dep($row);
			$codigo_plan_padre = $row['codigo_plan'];
			$id_registro_a_facturar = $row['id'];

			// Leemos datos del PLAN PADRE
			$filas = $varios->readPlanPadre($codigo_plan_padre);
			$i = 0;
			while ($fila = mysqli_fetch_assoc($filas)){
				$rs_plan[$i++] = $fila;
			}
			
			$cod_ope = $varios->getParameterValues('cod_ope');
			$cod_tra = $varios->getParameterValues('cod_tra');

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

			if($codigo_plan_padre == 'PC01'){

				$data['codigo_plan'] = $rs_plan[0]['codigo_plan_hijo'];
				$data['precio'] = $rs_plan[0]['precio_hijo'];
				$data['fecha_inicio'] = $fecha_inicio;
				
				$horaLlamada = date('H:i:s');
				$rc = llamamosWS_Sartawi($data);
				$horaRetorno = date('H:i:s');
	
				// Recogemos el mensaje de retorno del WS Sartawi
				$data['status_fact']  = $rc['status_fact'];
				$data['mensaje_fact'] = $rc['mensaje_fact'];
				$data['hora_llamada'] = $horaLlamada;
				$data['hora_retorno'] = $horaRetorno;
	
				if($rc['status_fact'] == 'E'){
					// Actualizamos los campos FACTURACION, FECHA_FACTURACION y ESTADO de la table temp
					$rspta = $cobranzas->actualizamosCamposFacturacion($id_registro_a_facturar);
	
					// Guardamos en el LOG los datos enviados al WS
					$rspta = $varios->registraDatosLog($data);
				}
	
	
			//}else if($codigo_plan_padre == 'PC02'){
			}else{
				
				//--------------------------------//
				// Primera factura                //
				// Ej: Fecha_inicio = 2023-12-26  //
				//--------------------------------//
				$data['codigo_plan'] = $rs_plan[0]['codigo_plan_hijo'];
				$data['precio'] = $rs_plan[0]['precio_hijo'];
				$data['fecha_inicio'] = $fecha_inicio;
	
				$horaLlamada = date('H:i:s');
				$rc = llamamosWS_Sartawi($data);
				$horaRetorno = date('H:i:s');
	
				// Recogemos el mensaje de retorno del WS Sartawi
				$data['status_fact']  = $rc['status_fact'];
				$data['mensaje_fact'] = $rc['mensaje_fact'];
				$data['hora_llamada'] = $horaLlamada;
				$data['hora_retorno'] = $horaRetorno;
	
				$rc['status_fact'] = 'E';
				if($rc['status_fact'] == 'E'){
					// Actualizamos los campos FACTURACION, FECHA_FACTURACION y ESTADO de la table temp
					$rspta = $cobranzas->actualizamosCamposFacturacion($id_registro_a_facturar);
	
					// Guardamos en el LOG los datos enviados al WS
					$rspta = $varios->registraDatosLog($data);
				}
	
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
	
				$horaLlamada = date('H:i:s');
				$rc = llamamosWS_Sartawi($data);
				$horaRetorno = date('H:i:s');
	
				$data['status_fact']  = $rc['status_fact'];
				$data['mensaje_fact'] = $rc['mensaje_fact'];
				$data['hora_llamada'] = $horaLlamada;
				$data['hora_retorno'] = $horaRetorno;
	
				if($rc['status_fact'] == 'E'){
					// Actualizamos los campos FACTURACION, FECHA_FACTURACION y ESTADO de la table temp
					$rspta = $cobranzas->actualizamosCamposFacturacion($id_registro_a_facturar);
	
					// Guardamos en el LOG los datos enviados al WS
					$rspta = $varios->registraDatosLog($data);
				}
	
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
	
				$horaLlamada = date('H:i:s');
				$rc = llamamosWS_Sartawi($data);
				$horaRetorno = date('H:i:s');
	
	
				$data['status_fact']  = $rc['status_fact'];
				$data['mensaje_fact'] = $rc['mensaje_fact'];
				$data['hora_llamada'] = $horaLlamada;
				$data['hora_retorno'] = $horaRetorno;
	
				if($rc['status_fact'] == 'E'){
					//Actualizamos los campos FACTURACION, FECHA_FACTURACION y ESTADO de la table temp
					$rspta = $cobranzas->actualizamosCamposFacturacion($id_registro_a_facturar);
	
					// Guardamos en el LOG los datos enviados al WS
					$rspta = $varios->registraDatosLog($data);
				}
	
	
			}

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

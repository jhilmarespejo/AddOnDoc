<?php
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}

require_once "../modelos/Cobranzas.php";
require_once "../modelos/Varios.php";


$cobranzas = new Cobranzas();
$varios    = new Varios();

$id_usuario = $_SESSION['idusuario'];


$codigo_agencia = isset($_POST['codigo_agencia'])?limpiarCadena($_POST["codigo_agencia"]):"";
$cedula = isset($_POST["cedula"])? limpiarCadena($_POST["cedula"]):"";
$precio = isset($_POST["precio"])? limpiarCadena($_POST["precio"]):"";
$registro_a_facturar = isset($_POST["registro_a_facturar"])? limpiarCadena($_POST["registro_a_facturar"]):"";

$id_registro = isset($_POST["id"])? limpiarCadena($_POST["id"]):"";



switch ($_GET["op"]){


	case 'buscaClienteConDeuda':

		//$cedula = '821048';
		//$codigo_agencia = '302';

		$rspta=$cobranzas->buscaCobranzaxCedula($cedula,$codigo_agencia);

		//dep($rspta);

		$data= Array();

		$ndx = 1;
 		while ($reg=$rspta->fetch_object()){
			//$estado = '<input type="checkbox" name="cobra'.$reg->id. '" id="cobra'. $reg->id . ' onclick="registroSeleccionado('. $reg->id.')">';
			$estado = '<button class="btn btn-success" onclick="registroSeleccionado('.$reg->id.',\''.$reg->cod_plan.'\')"><i class="fa fa-check"></i></button>';
 			$data[]=array(
 				"0"=>$reg->id,
 				"1"=>$reg->nombreCli,
 				"2"=>$reg->fecha_creacion,
 				"3"=>$reg->plan,
 				"4"=>$reg->deuda,
				"5"=>$estado
 				/* "9"=>($reg->estado=='V')?'<span class="label bg-green">Vendido</span>':
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



	case 'buscaCobranzaxCedula':

		//$cedula = $_REQUEST['cedula'];
		//$codigo_agencia = $_REQUEST['codigo_agencia'];
		//$codigo_agencia = '302';
		//$cedula = '821048';
		$rspta=$cobranzas->buscaCobranzaxCedula($cedula,$codigo_agencia);


		$data = array();

		if($rspta){
			$data['status'] = 'ok';
			$data['data'] = $reg=$rspta->fetch_object();
		}else{
			$data['status'] = 'error';
			$data['data'] = 'No se encontró paciente';
		}

  		while ($reg=$rspta->fetch_object()){
			$codigo_plan_padre = $reg->codigo_plan;
			$codigo_plan_hijo = $reg->plan;

			if($codigo_plan_padre == 'PC02'){
				$deuda = 500;
			}else{
				$deuda = $reg->deuda;
			}

			if(($codigo_plan_padre == 'PC01' && $codigo_plan_hijo == 'PPCE0062') ||
			($codigo_plan_padre == 'PC02' && $codigo_plan_hijo == 'PPCE0063')){
				$data[]=array(
					"0"=>$reg->id,
					 "1"=>$reg->nombre,
					"2"=>$reg->fecha_creacion,
					"3"=>$reg->plan,
					"4"=>$reg->canal,
					"5"=>$deuda,
					"6"=>'<input type="checkbox" id='.$reg->id.' onchange="muestraDatos()">'
					 //"7"=>($reg->estado)?'<span class="label bg-green">Por Facturar</span>':
					 //'<span class="label bg-red">Desactivado</span>'
				 );

			}

 		}
 		$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
 		echo json_encode($results);
		 //dep($data);
	break;



	case 'buscarDatosCobranzas':

		//$cedula = '821048';

		$data = array();

		$rspta = $cobranzas->buscarDatosCobranzas($cedula,$codigo_agencia,$id_registro);
		$reg=$rspta->fetch_object();

		//dep($reg);
		//die();

		if($reg){

			$codigo_plan = $reg->codigo_plan;
			if($codigo_plan == 'PC02'){
				$deuda = 500;
			}else if($codigo_plan == 'PC03' || $codigo_plan == 'PC04'){
				$deuda = 620;
			}else if($codigo_plan == 'PC05'){
				$deuda = 1100;
			}else{
				$deuda = $reg->deuda;
			}

			$data['status'] = 'ok';
			$data['nombre'] = $reg->nombre;
			$data['deuda'] = $deuda;

		}else{

			$data['status'] = 'error';
			$data['nombre'] = "";
			$data['deuda'] = "";
		}

		echo json_encode($data);

	break;





	case 'generarFactura':

		/*
		$codigo_agencia = 'ELT-JP';
		$id_usuario = '139';
		$precio = '150';
		$registro_a_facturar = '736';
		*/

		// Generamos los códigos de operación y transacción
		$cod_ope = $varios->getParameterValues('cod_ope');
		$cod_tra = $varios->getParameterValues('cod_tra');

		//echo "COD OPE:" . $cod_ope . "<br>";
		//echo "COD TRA:" . $cod_tra . "<br>";

		// Generamos el número de contrato del paciente
		$contrato =  $varios->getNumeroContrato($registro_a_facturar);

		//echo "CONTRATO: " . $contrato . "<br>";

		$ret_val = $cobranzas->generarFactura_c($cod_ope, $cod_tra,$id_usuario,$registro_a_facturar,$contrato);


		if($ret_val['status'] == 'ok'){

			$id_registro_a_facturar = $ret_val['data']['id'];
			$_SESSION['id_registro_a_facturar'] = $registro_a_facturar;

			$data = array();

			$data['status']  = $ret_val['status'];
			$data['status_fact']  = $ret_val['status'];
			$data['msg'] = $ret_val['msg'];

		}else{
			$data['status']  = 'error';
			$data['status_fact']  = $ret_val['status'];
			$data['msg'] = $ret_val['msg'];
		}

		echo json_encode($data);
		//dep($data);

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

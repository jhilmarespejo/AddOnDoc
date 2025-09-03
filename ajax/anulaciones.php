<?php 
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
require_once "../modelos/Anulaciones.php";

$anulacion = new Anulaciones();
//$codigo_canal   = isset($_POST["codigo_canal"])? limpiarCadena($_POST["codigo_canal"]):"";
//$codigo_agencia = isset($_POST["codigo_agencia"])? limpiarCadena($_POST["codigo_agencia"]):"";

$id_usuario     = $_SESSION['idusuario'];
$codigo_agencia = $_SESSION['codigo_agencia'];
$codigo_canal	= $_SESSION['codigo_canal'];

$estado = isset($_POST["estado"])? limpiarCadena($_POST["estado"]):"";
$id_admision = isset($_POST["id_admision"])? limpiarCadena($_POST["id_admision"]):"";

switch ($_GET["op"]){

	case 'ventasfecha':
		// $fecha_inicio=$_REQUEST["fecha_inicio"];
		// $fecha_fin=$_REQUEST["fecha_fin"];
		// $codigo_canal = $_SESSION['codigo_canal'];

		date_default_timezone_set('America/La_Paz');
		$fecha_actual = date('Ymd');

		$rspta=$anulacion->buscaRegistros($fecha_actual,$codigo_canal);
 		//Vamos a declarar un array
 		$data= Array();

 		while ($reg=$rspta->fetch_object()){
			$no_anular = 0;
			$cobranza = 0;
			if($reg->estado =='P'){
				$estado2 = '<span class="label bg-green">Pendiente</span>';
				$estado1 = '<button class="btn btn-success" onclick="anularAdmision('.$reg->id.','.'\'' .$reg->estado.'\''.')"><i class="fa fa-trash"></i></button>';
			}else if($reg->estado =='A'){
				$estado2 = '<span class="label bg-red">Anulado</span>';
				$estado1 = '<button class="btn btn-danger" onclick="anularAdmision('.$no_anular.','.'\''.$reg->estado.'\''.')"><i class="fa fa-eye"></i></button>';
			}else if($reg->estado =='C'){
				$estado2 = '<span class="label bg-blue">Cobrado</span>';
				$estado1 = '<button class="btn btn-success" onclick="anularAdmision('.$reg->id.','.'\'' .$reg->estado.'\''.')"><i class="fa fa-trash"></i></button>';
				$cobranza = $reg->precio;
			}else if($reg->estado =='F'){
				$estado2 = '<span class="label bg-blue">Cobrado</span>';
				$estado1 = '<button class="btn btn-success" onclick="anularAdmision('.$reg->id.','.'\'' .$reg->estado.'\''.')"><i class="fa fa-trash"></i></button>';
				$cobranza = $reg->precio;
			}
 			$data[]=array(
 				"0"=>$estado1,
				"1"=>$reg->id,
 				"2"=>$reg->agencia,
 				"3"=>$reg->ciudad,
 				"4"=>$reg->plan,
 				"5"=>$reg->precio,
				"6"=>$cobranza,
 				"7"=>$reg->cliente,
				"8"=>$reg->cedula,
				"9"=>$reg->fechaInicio,
				"10"=>$estado2
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


	case 'anularAdmision':

		//$id_admision = 1437;
		// $estado = 'F';
		// $id_usuario = 1;

		// --------------------------------------------------------//
		// Verificamos si utilizó alguno de los servicios del PLAN //
		// --------------------------------------------------------//
		$data = $anulacion->obtenerDataParaAnular($id_admision);
		//dep($data);

		/*
		$data['cedula']     = '8710721';
		$data['codigo_cli'] = '259';
		$data['codigo_tra'] = '10000493';
		$data['codigo_ope'] = '10007381';
		dep($data);
		die();
		*/

		$rspta = $anulacion->validamosFacturaServicios($data);
		//dep($rspta);
		//die();

		if($rspta['estado'] != 'E')
		{
		    echo json_encode($rspta);
		}else{

			$rspta = $anulacion->anularAdmision($id_admision, $id_usuario);
			echo json_encode($rspta);
		}


		//echo json_encode($rspta);

	break;
}

function alerta_anulacion($id_admision){

	require_once "../modelos/Anulaciones.php";
	$anulacion = new Anulaciones();

	$rspta = $anulacion->getDataInfo($id_admision);

	dep($rspta);
	//echo json_encode($rspta);
	return $rspta;

}
function dep($data){
	$format = print_r('<pre>');
	$format .= print_r($data);
	$format .= print_r('</pre>');

	return $format;
}
?>

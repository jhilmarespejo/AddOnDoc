<?php 
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
require_once "../modelos/Consultas.php";

$consulta=new Consultas();
//$codigo_canal   = isset($_POST["codigo_canal"])? limpiarCadena($_POST["codigo_canal"]):"";
//$codigo_agencia = isset($_POST["codigo_agencia"])? limpiarCadena($_POST["codigo_agencia"]):"";

$id_usuario     = $_SESSION['idusuario'];
$codigo_agencia = $_SESSION['codigo_agencia'];
$codigo_canal	= $_SESSION['codigo_canal'];

switch ($_GET["op"]){

	case 'ventasfecha':
		$fecha_inicio=$_REQUEST["fecha_inicio"];
		$fecha_fin=$_REQUEST["fecha_fin"];
		$codigo_canal = $_SESSION['codigo_canal'];

		$rspta=$consulta->ventasfecha($fecha_inicio,$fecha_fin,$codigo_canal);
 		//Vamos a declarar un array
 		$data= Array();

		$cnt = 0;
 		while ($reg=$rspta->fetch_object()){
			$no_anular = 0;
			$cobranza = 0;
			if($reg->estado =='P'){
				$estado2 = '<span class="label bg-green">Pendiente</span>';
				$estado1 = '<button class="btn btn-success" onclick="anularAdmision('.$reg->id. ')"><i class="fa fa-trash"></i></button>';
			}else if($reg->estado =='A'){
				$estado2 = '<span class="label bg-red">Anulado</span>';
				$estado1 = '<button class="btn btn-danger" onclick="anularAdmision('.$no_anular.')"><i class="fa fa-eye"></i></button>';
			}else if($reg->estado =='C'){
				$estado2 = '<span class="label bg-blue">Cobrado</span>';
				$estado1 = '<button class="btn btn-primary" onclick="anularAdmision('.$no_anular.')"><i class="fa fa-eye"></i></button>';
				$cobranza = $reg->precio;
			}else if($reg->estado =='F'){
				$estado2 = '<span class="label bg-blue">Cobrado</span>';
				$estado1 = '<button class="btn btn-primary" onclick="anularAdmision('.$no_anular.')"><i class="fa fa-eye"></i></button>';
				$cobranza = $reg->precio;
			}
 			$data[]=array(
 				"0"=>$estado1,
 				"1"=>$reg->agencia,
 				"2"=>$reg->ciudad,
 				"3"=>$reg->plan,
 				"4"=>$reg->precio,
				"5"=>$cobranza,
 				"6"=>$reg->cliente,
				"7"=>$reg->cedula,
				"8"=>$reg->fechaInicio,
				"9"=>$estado2
 				/* "10"=>($reg->estado=='V')?'<span class="label bg-green">Vendido</span>':
 				'<span class="label bg-red">Anulado</span>' */
 				);
			$cnt++;
			if($cnt >= 5000)
				break;
 		}
 		$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
			"iTotalDisplayRecords"=>$cnt,
 			"aaData"=>$data);
 		echo json_encode($results);

	break;


	case 'ventasfechacanalagencia':
		$fecha_inicio   = $_REQUEST["fecha_inicio"];
		$fecha_fin      = $_REQUEST["fecha_fin"];
		$codigo_canal   = $_REQUEST['codigo_canal'];
		$codigo_agencia = $_REQUEST['codigo_agencia'];

		/* $fecha_inicio   = '2024-03-01';
		$fecha_fin      = '2024-04-05';
		$codigo_canal   = 'C001';
		$codigo_agencia = 'TODO'; */

		$rspta=$consulta->ventasfechacanalagencia($fecha_inicio,$fecha_fin,$codigo_canal,$codigo_agencia);
 		//Vamos a declarar un array 
 		$data= Array();

		/* $reg=$rspta->fetch_object();
		dep($reg);
		die(); */

		$i = 1;
 		while ($reg=$rspta->fetch_object()){
			$no_anular = 0;
			if($reg->estado =='P'){
				$estado2 = '<span class="label bg-green">Pendiente</span>';
			}else if($reg->estado =='A'){
				$estado2 = '<span class="label bg-red">Anulado</span>';
			}else if($reg->estado =='C'){
				$estado2 = '<span class="label bg-blue">Cobrado</span>';
			}else if($reg->estado =='F'){
				$estado2 = '<span class="label bg-blue">Facturado</span>';
			}
 			$data[]=array(
 				"0"=>$i++,
 				"1"=>$reg->agencia,
 				"2"=>$reg->ciudad,
 				"3"=>$reg->plan,
 				"4"=>$reg->precio,
 				"5"=>$reg->nombre,
				"6"=>$reg->cedula,
				"7"=>$reg->genero,
				"8"=>$reg->fechaInicio,
				"9"=>$estado2
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

	case 'ventasfechacanal':
		$fecha_inicio=$_REQUEST["fecha_inicio"];
		$fecha_fin=$_REQUEST["fecha_fin"];
		$codigo_canal = $_REQUEST['codigo_canal'];

		$rspta=$consulta->ventasfecha($fecha_inicio,$fecha_fin,$codigo_canal);
 		//Vamos a declarar un array
 		$data= Array();

 		while ($reg=$rspta->fetch_object()){
			$no_anular = 0;
			if($reg->estado =='P'){
				$estado2 = '<span class="label bg-green">Pendiente</span>';
				$estado1 = '<button class="btn btn-success" onclick="anularAdmision('.$reg->id. ')"><i class="fa fa-pencil"></i></button>';
			}else if($reg->estado =='A'){
				$estado2 = '<span class="label bg-red">Anulado</span>';
				$estado1 = '<button class="btn btn-danger" onclick="anularAdmision('.$no_anular.')"><i class="fa fa-eye"></i></button>';
			}else if($reg->estado =='C'){
				$estado2 = '<span class="label bg-blue">Cobrado</span>';
				$estado1 = '<button class="btn btn-primary" onclick="anularAdmision('.$no_anular.')"><i class="fa fa-eye"></i></button>';
			}else if($reg->estado =='F'){
				$estado2 = '<span class="label bg-blue">Cobrado</span>';
				$estado1 = '<button class="btn btn-primary" onclick="anularAdmision('.$no_anular.')"><i class="fa fa-eye"></i></button>';
			}
 			$data[]=array(
 				"0"=>$estado1,
 				"1"=>$reg->agencia,
 				"2"=>$reg->ciudad,
 				"3"=>$reg->plan,
 				"4"=>$reg->precio,
 				"5"=>$reg->cliente,
				"6"=>$reg->cedula,
				"7"=>$reg->genero,
				"8"=>$reg->fechaInicio,
				"9"=>$estado2
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

	case 'ventasfechaagencia':

		$fecha_inicio   = $_REQUEST["fecha_inicio"];
		$fecha_fin      = $_REQUEST["fecha_fin"];
		$codigo_agencia = $_REQUEST["codigo_agencia"];

		$rspta=$consulta->ventasfechaagencia($fecha_inicio,$fecha_fin,$codigo_agencia);

		$data= Array();

 		while ($reg=$rspta->fetch_object()){
			$cobranza = 0;
			if($reg->estado =='P'){
				$estado = '<span class="label bg-green">Pendiente</span>';
			}else if($reg->estado =='A'){
				$estado = '<span class="label bg-red">Anulado</span>';
			}else if($reg->estado =='C'){
				$estado = '<span class="label bg-blue">Cobrado</span>';
				$cobranza = $reg->precio;
			}else if($reg->estado =='F'){
				$estado = '<span class="label bg-blue">Cobrado</span>';
				$cobranza = $reg->precio;
			}
 			$data[]=array(
 				"0"=>$reg->agencia,
 				"1"=>$reg->ciudad,
 				"2"=>$reg->plan,
 				"3"=>$reg->precio,
				"4"=>$cobranza,
 				"5"=>$reg->nombre,
				"6"=>$reg->cedula,
				"7"=>$reg->fechaInicio,
				"8"=>$estado
 				);
 		}
 		$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
 		echo json_encode($results);

	break;

	case 'ventasfechacajero':

		$fecha_inicio   = $_REQUEST["fecha_inicio"];
		$fecha_fin      = $_REQUEST["fecha_fin"];
/*
		$codigo_agencia = $_REQUEST["codigo_agencia"];
		$id_usuario     = $_REQUEST["id_usuario"];
		$codigo_canal   = $_REQUEST['codigo_canal'];

		$fecha_inicio   = '2024-04-23';
		$fecha_fin      = '2024-04-23';
		$codigo_agencia = '302';
		$id_usuario     = '172';
		$codigo_canal   = 'C011';
*/



		$rspta=$consulta->ventasfechacajero($fecha_inicio,$fecha_fin,$codigo_agencia,$id_usuario,$codigo_canal);

		$data= Array(); 

		$i = 1;
 		while ($reg=$rspta->fetch_object()){
			if($reg->estado =='P'){
				$estado = '<span class="label bg-green">Pendiente</span>';
			}else if($reg->estado =='A'){
				$estado = '<span class="label bg-red">Anulado</span>';
			}else if($reg->estado =='C'){
				$estado = '<span class="label bg-blue">Cobrado</span>';
			}else if($reg->estado =='F'){
				$estado = '<span class="label bg-blue">Cobrado</span>';
			}
			// $accion = '<button class="btn btn-primary" onclick="reimprimirRecibo('.$reg->id. ')"><i class="fa fa-eye"></i></button>';
			// $accion ='<a href="../reportes/m_recibo.php?id_registro='.$reg->id.' class="btn btn-o btn-primary" style="background:#007aff; color:#ffffff; 
			// 	border-radius:5px; border-width: 2px; font-weight: bold; padding-left: 8px; padding-top: 5px;padding-bottom: 5px;padding-right: 8px;" target="_blank" >
			// 	<span class="fa fa-eye"></span></a>';
			$accion='';
 			$data[]=array(
				// "0"=>$accion,
 				"0"=>$reg->agencia,
 				"1"=>$reg->ciudad,
 				"2"=>$reg->plan,
 				"3"=>$reg->precio,
 				"4"=>$reg->nombre,
				"5"=>$reg->cedula,
				"6"=>$reg->genero,
				"7"=>$reg->fechaCobranzas,
				"8"=>$estado
 				);
 		}
 		$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
 		echo json_encode($results);

	break;
}

function dep($data){
	$format = print_r('<pre>');
	$format .= print_r($data);
	$format .= print_r('</pre>');

	return $format;
}
?>

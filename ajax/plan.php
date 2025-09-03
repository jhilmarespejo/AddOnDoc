<?php 
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}

require_once "../modelos/Plan.php";

$plan=new Plan();

//var_dump($_POST);

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
$id_canal=isset($_POST["id_canal"])? limpiarCadena($_POST["id_canal"]):"";
$nombre_canal=isset($_POST["nombre_canal"])? limpiarCadena($_POST["nombre_canal"]):"";
$comision=isset($_POST["comision"])? limpiarCadena($_POST["comision"]):"";
$estado=isset($_POST["estado"])? limpiarCadena($_POST["estado"]):"";

switch ($_GET["op"]){
	case 'guardaryeditar':
		if (empty($id)){
			$rspta=$plan->insertar($codigo_padre,$nombre_padre,$contrato,$canal,$estado);
			echo $rspta ? "Plan registrado" : "Plan no se pudo registrar";
		} else {
			$rspta=$plan->editar($id,$codigo_padre,$nombre_padre,$contrato,$canal,$estado);
			echo $rspta ? "Plan actualizado" : "Plan no se pudo actualizar";
		}
	break;

	case 'desactivar':
		$rspta=$plan->desactivar($id);
 		echo $rspta ? "Plan Desactivado" : "Plan no se puede desactivar";
	break;

	case 'activar':
		$rspta=$plan->activar($id);
 		echo $rspta ? "Plan activado" : "Plan no se puede activar";
	break;

	case 'mostrar':
		//$id = $_GET['id'];
		$rspta=$plan->mostrar($id);
 		//Codificar el resultado utilizando json
		//var_dump($rspta);
 		echo json_encode($rspta);
	break;

	case 'listar':
		$rspta=$plan->listar();
 		//Vamos a declarar un array
 		$data= Array();
		
 		while ($reg=$rspta->fetch_object()){
 			$data[]=array(
				"0"=>($reg->estado == 'A') ? '<button class="btn btn-warning" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
 					' <button class="btn btn-danger" onclick="desactivar('.$reg->id.')"><i class="fa fa-close"></i></button>':
 					'<button class="btn btn-warning" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
 					' <button class="btn btn-primary" onclick="activar('.$reg->id.')"><i class="fa fa-check"></i></button>',										
 				"1"=>$reg->codigo_plan,
				"2"=>$reg->plan,
				"3"=>$reg->contrato,
				"4"=>$reg->canal,
 				"5"=>($reg->estado)?'<span class="label bg-green">Activado</span>':
 				'<span class="label bg-red">Desactivado</span>'
 				);
 		}
 		$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
 		echo json_encode($results);

	break;
	
	case 'selectPlanes':
		$codigo_canal = $_SESSION['codigo_canal'];
		$rspta=$plan->listarPlanes($codigo_canal);

		echo '<option value=' . '0' . '>' . 'Seleccione un Plan' . '</option>';	

		while ($reg = $rspta->fetch_object())
		{
			echo '<option value=' . $reg->codigo_plan . '-'. $reg->beneficiario . '>'
								  . $reg->plan . '</option>';
		}

	break;
}
//Fin de las validaciones de acceso

ob_end_flush();
?>
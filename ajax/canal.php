<?php 
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}

require_once "../modelos/Canal.php";

$canal=new Canal();

//var_dump($_POST);

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
$id_canal=isset($_POST["id_canal"])? limpiarCadena($_POST["id_canal"]):"";
$nombre_canal=isset($_POST["nombre_canal"])? limpiarCadena($_POST["nombre_canal"]):"";
$comision=isset($_POST["comision"])? limpiarCadena($_POST["comision"]):"";
$estado=isset($_POST["estado"])? limpiarCadena($_POST["estado"]):"";

switch ($_GET["op"]){
	case 'guardaryeditar':
		if (empty($id)){
			$rspta=$canal->insertar($id_canal,$nombre_canal,$comision,$estado);
			echo $rspta ? "Canal registrado" : "Canal no se pudo registrar";
		} else {
			$rspta=$canal->editar($id,$id_canal,$nombre_canal,$comision,$estado);
			echo $rspta ? "Canal actualizado" : "Canal no se pudo actualizar";
		}
	break;

	case 'desactivar':
		$rspta=$canal->desactivar($id);
 		echo $rspta ? "Canal Desactivado" : "Canal no se puede desactivar";
	break;

	case 'activar':
		$rspta=$canal->activar($id);
 		echo $rspta ? "Canal activado" : "Canal no se puede activar";
	break;

	case 'mostrar':
		//$id = $_GET['id'];
		$rspta=$canal->mostrar($id);
 		//Codificar el resultado utilizando json
		//var_dump($rspta);
 		echo json_encode($rspta);
	break;

	case 'listar':
		$rspta=$canal->listar();
 		//Vamos a declarar un array
 		$data= Array();
		
 		while ($reg=$rspta->fetch_object()){
 			$data[]=array(
				"0"=>($reg->estado == 'A') ? '<button class="btn btn-warning" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
 					' <button class="btn btn-danger" onclick="desactivar('.$reg->id.')"><i class="fa fa-close"></i></button>':
 					'<button class="btn btn-warning" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
 					' <button class="btn btn-primary" onclick="activar('.$reg->id.')"><i class="fa fa-check"></i></button>',										
 				"1"=>$reg->id_canal,
				"2"=>$reg->nombre_canal,
				"3"=>$reg->comision,
 				"4"=>($reg->estado == 'A')?'<span class="label bg-green">Activado</span>':
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
}
//Fin de las validaciones de acceso

ob_end_flush();
?>
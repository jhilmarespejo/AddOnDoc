<?php 
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}

require_once "../modelos/Contratos_c.php";
require_once "../modelos/Varios.php";

$contrato_c=new Contrato_c();
$varios = new Varios();

//var_dump($_POST);

$nombre_plan_padre=isset($_POST["nombre_plan_padre"])? limpiarCadena($_POST["nombre_plan_padre"]):"";
$precio_padre=isset($_POST["precio_padre"])? limpiarCadena($_POST["precio_padre"]):"";
$id_beneficiario=isset($_POST["id_beneficiario"])? limpiarCadena($_POST["id_beneficiario"]):"";
$id_ciudad=isset($_POST["id_ciudad"])? limpiarCadena($_POST["id_ciudad"]):"";
$id_canal=isset($_POST["id_canal"])? limpiarCadena($_POST["id_canal"]):"";
$gestion=isset($_POST["gestion"])? limpiarCadena($_POST["gestion"]):"";
$id_tipo_contrato=isset($_POST["id_tipo_contrato"])? limpiarCadena($_POST["id_tipo_contrato"]):"";
$fecha_inicio=isset($_POST["fecha_inicio"])? limpiarCadena($_POST["fecha_inicio"]):"";

$nombre_plan_hijo1=isset($_POST["nombre_plan_hijo1"])? limpiarCadena($_POST["nombre_plan_hijo1"]):"";
$codigo_plan_hijo1=isset($_POST["codigo_plan_hijo1"])? limpiarCadena($_POST["codigo_plan_hijo1"]):"";
$precio_plan_hijo1=isset($_POST["precio_plan_hijo1"])? limpiarCadena($_POST["precio_plan_hijo1"]):"";
$cantidad_plan_hijo1=isset($_POST["cantidad_plan_hijo1"])? limpiarCadena($_POST["cantidad_plan_hijo1"]):"";
$vigencia=isset($_POST["vigencia"])? limpiarCadena($_POST["vigencia"]):"";
$genero_plan=isset($_POST["genero_plan"])? limpiarCadena($_POST["genero_plan"]):"";
$visible=isset($_POST["visible"])? limpiarCadena($_POST["visible"]):"";



$procesa_2do_hijo=isset($_POST["procesa_2do_hijo"])? limpiarCadena($_POST["procesa_2do_hijo"]):"";

$nombre_plan_hijo2=isset($_POST["nombre_plan_hijo1"])? limpiarCadena($_POST["nombre_plan_hijo2"]):"";
$codigo_plan_hijo2=isset($_POST["codigo_plan_hijo1"])? limpiarCadena($_POST["codigo_plan_hijo2"]):"";
$precio_plan_hijo2=isset($_POST["precio_plan_hijo1"])? limpiarCadena($_POST["precio_plan_hijo2"]):"";
$cantidad_plan_hijo2=isset($_POST["cantidad_plan_hijo1"])? limpiarCadena($_POST["cantidad_plan_hijo2"]):"";

$codigo_operador = $_SESSION['idusuario'];


switch ($_GET["op"]){ 
	case 'guardar_c':
/*
		$procesa_2do_hijo = 'SI';
		$codigo_operador = 1; 
		$nombre_plan_padre = 'PLAN MUJERX MULTIANUAL';
		$precio_padre = '640';
		$id_beneficiario = 'S';
		$vigencia = '12';
		$id_canal = 'C001';
		$id_ciudad = 1;
		$gestion = '24';
		$id_tipo_contrato = '1';
		$fecha_inicio = '2024-02-24';
		$nombre_plan_hijo1 = 'PLAN MUJERX';
		$codigo_plan_hijo1 = 'PPCE0094';
		$precio_plan_hijo1 = '260';
		$cantidad_plan_hijo1 = '1';

		$nombre_plan_hijo2 = 'PLAN MUJERX MULTIANUAL';
		$codigo_plan_hijo2 = 'PPCE0095';
		$precio_plan_hijo2 = '190';
		$cantidad_plan_hijo2 = '2';
 */


		//----------------------------------------------/
		// Generamos el siguiente CODIGO del PLAN PADRE /
		//----------------------------------------------/
 		$rspta = $contrato_c->getNextPlanPadre();

		$cod_plan_padre_actual = $rspta['codigo_plan_padre'];
		$orden_aparicion       = intval($rspta['orden']) + 1;
		$c_num_plan_padre = intval(substr($cod_plan_padre_actual,2,4)) + 1;

		// Left padding if number < $str_length
		$str_length = 4;
		$str = substr("0000{$c_num_plan_padre}", -$str_length);
		$cod_plan_padre_nuevo = 'PC'.$str;

		//------------------------------------//
		// INSERTAMOS en la tabla plan_padre  //
		//------------------------------------//
		//echo "PRIMERO INSERT<br>";
		$id_p_padre = $contrato_c->insertar_plan_padre($cod_plan_padre_nuevo,$nombre_plan_padre,$orden_aparicion,$precio_padre,
						$id_beneficiario,$codigo_plan_hijo1,$nombre_plan_hijo1,$cantidad_plan_hijo1,
						$precio_plan_hijo1,$vigencia,$id_canal,$genero_plan,$visible,$codigo_operador);

		echo $rspta ? "Plan Padre registrado1" : "Plan Padre no se pudo registrar1";

		if($procesa_2do_hijo == 'SI'){
			//echo "SEGUNDO INSERT<br>";
			$orden_aparicion++;
			$id_p_padre = $contrato_c->insertar_plan_padre($cod_plan_padre_nuevo,$nombre_plan_padre,$orden_aparicion,$precio_padre,
						$id_beneficiario,$codigo_plan_hijo2,$nombre_plan_hijo2,$cantidad_plan_hijo2,
						$precio_plan_hijo2,$vigencia,$id_canal,$genero_plan,$visible,$codigo_operador);

			//echo $rspta ? "Plan Padre registrado2" : "Plan Padre no se pudo registrar2";

		}

		//------------------------------------------//
		// AHORA TRABAJAREMOS EN LA TABLA CONTRATOS //
		//------------------------------------------//

		$contrato_sm = $codigo_plan_hijo1;
		$ciudad_sm = $varios->getCiudadSM($id_ciudad);
		$contrato1 = $contrato_c->generaNumeroContrato($codigo_plan_hijo1,$ciudad_sm,$gestion);
		$numero_contrato1 = substr($contrato1,15,4);
		//echo "NUM CONTRATO1: " . $numero_contrato1 . "<br>";

		date_default_timezone_set('America/La_Paz');
		$ldate=date('Y-m-d H:i:s');

		// Seteamos valores iniciales.
		$estado = "1";
		$valor_inicial = "1";
		$valor_actual = "0";
		$rspta = $contrato_c->insertarContrato($nombre_plan_padre,$id_ciudad,$gestion,$numero_contrato1,$contrato1,$contrato_sm,
					$id_tipo_contrato,$valor_inicial,$valor_actual,$id_canal,$estado,$ldate,$codigo_operador);


		if($procesa_2do_hijo == 'SI'){

			$contrato_sm = $codigo_plan_hijo2;
			$ciudad_sm = $varios->getCiudadSM($id_ciudad);
			$contrato2 = $contrato_c->generaNumeroContrato($codigo_plan_hijo2,$ciudad_sm,$gestion);
			$numero_contrato2 = substr($contrato2,15,4);
			//echo "NUM CONTRATO2: " . $numero_contrato2 . "<br>";

			$rspta = $contrato_c->insertarContrato($nombre_plan_hijo2,$id_ciudad,$gestion,$numero_contrato2,$contrato2,$contrato_sm,
								$id_tipo_contrato,$valor_inicial,$valor_actual,$id_canal,$estado,$ldate,$codigo_operador);

			echo $rspta ? "Contrato Padre registrado2" : "Contrato Padre no se pudo registrar2";

		}


		break;

	case 'listar':
		$codigo_canal = $_SESSION['codigo_canal'];
		//echo "COD CANAL: " . $codigo_canal . "<br>";
		//echo "COD CANAL: " . $_SESSION['codigo_canal'] . "<br>";

		$rspta=$contrato_c->listar($codigo_canal);
 		//Vamos a declarar un array
 		$data= Array();

		 //$reg=$rspta->fetch_object();
		 //dep($reg);

 		while ($reg=$rspta->fetch_object()){
			if($reg->estado == 1)
			{
				$estado = 'ACTIVO';
			}
 			$data[]=array(
				/* "0"=>($reg->estado == '1') ? '<button class="btn btn-warning" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
 					' <button class="btn btn-danger" onclick="desactivar('.$reg->id.')"><i class="fa fa-close"></i></button>':
 					'<button class="btn btn-warning" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
 					' <button class="btn btn-primary" onclick="activar('.$reg->id.')"><i class="fa fa-check"></i></button>', */
 				"0"=>$reg->nombre_plan,
				"1"=>$reg->contrato,
				"2"=>$reg->ciudad,
				"3"=>$reg->nom_tip_contrato,
				"4"=>$reg->canal,
 				"5"=>($reg->estado)?'<span class="label bg-green">ACTIVO</span>':
 				'<span class="label bg-red">BAJA</span>'
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

function dep($data){
	$format = print_r('<pre>');
	$format .= print_r($data);
	$format .= print_r('</pre>');

	return $format;
}

ob_end_flush();
?>

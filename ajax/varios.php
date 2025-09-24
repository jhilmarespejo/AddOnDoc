<?php 
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}

require_once "../modelos/Varios.php";

$varios=new Varios();

//var_dump($_POST);
$parametro      = isset($_POST["parametro"])? limpiarCadena($_POST["parametro"]):"";
$valor          = isset($_POST["valor"])? limpiarCadena($_POST["valor"]):"";
//$codigo_canal   = isset($_POST["codigo_canal"])? limpiarCadena($_POST["codigo_canal"]):"";
$codigo_agencia = isset($_POST["codigo_agencia"])? limpiarCadena($_POST["codigo_agencia"]):"";
$plan_elegido   = isset($_POST["valorSeleccionado"])? limpiarCadena($_POST["valorSeleccionado"]):"";
$cedula         = isset($_POST["cedula"])? limpiarCadena($_POST["cedula"]):"";

$id_usuario   = $_SESSION['idusuario'];
$codigo_canal = $_SESSION['codigo_canal'];

switch ($_GET["op"]){  

	case 'listar':
		$rspta=$varios->listar();
 		//Vamos a declarar un array
 		$data= Array();
		
 		while ($reg=$rspta->fetch_object()){
 			$data[]=array(
				"0"=>($reg->estado == 'A') ? '<button class="btn btn-warning" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
 					' <button class="btn btn-danger" onclick="desactivar('.$reg->id.')"><i class="fa fa-close"></i></button>':
 					'<button class="btn btn-warning" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
 					' <button class="btn btn-primary" onclick="activar('.$reg->id.')"><i class="fa fa-check"></i></button>',										
 				"1"=>$reg->codigo_padre,
				"2"=>$reg->nombre_padre,
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

	case 'listarPlanesCanal':

		$codigo_canal = $_SESSION['codigo_canal'];
		$genero_paciente = isset($_POST["genero_paciente"])? limpiarCadena($_POST["genero_paciente"]):"";
		//$codigo_canal = 'C001';
		//$genero_paciente = 'M';

		$rspta = $varios->listarPlanesCanal($codigo_canal,$genero_paciente);

		echo '<option value=' . '0' . '>' . 'Seleccione una opción' . '</option>';	

		while ($reg = $rspta->fetch_object())
		{
			if( $reg->codigo_plan == 'PC0069' ){
				echo '<option value=' . $reg->codigo_plan . '>' . $reg->plan . ' - VITALICIA'. '</option>';
			}elseif( $reg->codigo_plan == 'PC0075' ){
				echo '<option value=' . $reg->codigo_plan . '>' . $reg->plan . ' - BANCO SOL'. '</option>';
			}
		}

	break;

	case 'listarCanales':
		$rspta=$varios->listarCanales();

		echo '<option value=' . '0' . '>' . 'Seleccione un Canal' . '</option>';

		while ($reg = $rspta->fetch_object())
		{
			echo '<option value=' . $reg->id_canal . '>'
								  . $reg->nombre_canal . '</option>';
		}

	break;

	case 'listarAgencias':
		$rspta=$varios->listarAgencias($codigo_canal);

		echo '<option value=' . '0' . '>' . 'Seleccione una Agencia' . '</option>';	
		echo '<option value=' . 'TODO' . '>' . 'TODAS' . '</option>';	

		while ($reg = $rspta->fetch_object())
		{
			echo '<option value=' . $reg->codigo_agencia . '>'
								  . $reg->nombre_agencia . '</option>';
		}

	break;

	case 'selectCiudades':
		$rspta=$varios->listarCiudades();

		echo '<option value=' . '0' . '>' . 'Seleccione una Ciudad' . '</option>';	

		while ($reg = $rspta->fetch_object())
		{
			echo '<option value=' . $reg->id . '>'
								  . $reg->nombre_ciudad . '</option>';
		}

	break;

	case 'guardaCliente':
		$rspta = $varios->guardaCliente($parametro, $valor);
		echo $rspta ? "Beneficiario guardado" : "Beneficirio no se pudo guardar";
		
	break;

	case 'anularAdmision':


		$id_admision = $_POST['id_admision'];
		//$id_admision = 1;

		$rspta = $varios->anularAdmision($id_admision,$id_usuario);

		if($rspta){
			$data['status'] = 'ok'; 
			$data['msg'] = 'Registro Anulado'; 
		}else{
			$data['status'] = 'error'; 
			$data['msg'] = 'Registro no pudo ser Anulado!!'; 
		}
		echo json_encode($data);
	break;

	case 'listarCiudades':

		$rspta = $varios->listarCiudades();

		echo '<option value=' . '0' . '>' . 'Seleccione una Ciudad' . '</option>';	

		while ($reg = $rspta->fetch_object())
		{
			echo '<option value=' . $reg->id . '>' . $reg->nombre_ciudad . '</option>';
		}
	
	break;

	case 'listarTipoContrato':

		$rspta = $varios->listarTipoContrato();

		echo '<option value=' . '0' . '>' . 'Seleccione Tipo Contrato' . '</option>';	

		while ($reg = $rspta->fetch_object())
		{
			echo '<option value=' . $reg->id . '>' . $reg->nom_tip_contrato . '</option>';
		}

	break;

	case 'listarCanalesXXX':

		$rspta = $varios->listarCanales();

		echo '<option value=' . '0' . '>' . 'Seleccione un Canal' . '</option>';	

		while ($reg = $rspta->fetch_object())
		{
			echo '<option value=' . $reg->id_canal . '>' . $reg->nombre_canal . '</option>';
		}

	break;

	case 'obtieneGeneroDelPlan':

		//$codigo_canal = 'C001';
		//$plan_elegido = 'PC0006';

 		$rspta = $varios->obtieneGeneroDelPlan($codigo_canal,$plan_elegido);

		$row = mysqli_fetch_assoc($rspta);

		$genero = $row['genero'];

		$data = array();
		$data['status'] = 'Ok';
		$data['plan_elegido'] = $plan_elegido;
		echo $genero;

	break;

	case 'buscaClienteAntiguo':

		//$cedula = '6322891';
		//$plan_elegido = 'PC0006';

		$rspta = $varios->buscaClienteAntiguo($cedula,$plan_elegido);
		//dep($rspta); 
		//die();

		$cnt = mysqli_num_rows($rspta);
		//echo "CNT: " . $cnt . "<br>";

		$data = array();
		if($cnt){

			$row = mysqli_fetch_assoc($rspta);
	
			$data['status'] = 'ok';
			$data['codigo_plan_renovacion'] = $row['codigo_plan_renovacion'];
			$data['nombre_plan_renovacion'] = $row['nombre_plan_renovacion'];	
	
		}else{

			$encontrado = false;
			$data['status'] = 'cliente sin plan anterior';
			$data['cedula'] = $cedula;
			$data['plan']   = $plan_elegido;

		}

		echo json_encode($data);

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

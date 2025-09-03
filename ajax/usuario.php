<?php
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
//require_once "/var/www/html/AddOnInnova/modelos/Usuario.php";
//require_once "/var/www/html/AddOnInnova//modelos/Varios.php";

require_once '../modelos/Usuario.php';
require_once '../modelos/Varios.php';


$usuario = new Usuario();
$varios  = new Varios();

$id_operador = isset($_SESSION["idusuario"])? limpiarCadena($_SESSION["idusuario"]):""; 

$valida_login = isset($_POST["valida_login"])? limpiarCadena($_POST["valida_login"]):""; 

$cedula      = isset($_POST["cedula"])? limpiarCadena($_POST["cedula"]):"";
$id_usuario  = isset($_POST["id_usuario"])? limpiarCadena($_POST["id_usuario"]):"";
$id_agencia  = isset($_POST["id_agencia"])? limpiarCadena($_POST["id_agencia"]):"";

$nombre           = isset($_POST["nombre_usuario"])? limpiarCadena($_POST["nombre_usuario"]):"";
$cargo            = isset($_POST["cargo"])? limpiarCadena($_POST["cargo"]):"";
$id_role          = isset($_POST["id_role"])? limpiarCadena($_POST["id_role"]):"";
$codigoAlmacen    = isset($_POST["id_agencia"])? limpiarCadena($_POST["id_agencia"]):"";
$tipo_documento   = isset($_POST["tipo_documento"])? limpiarCadena($_POST["tipo_documento"]):"";
$numero_documento = isset($_POST["numero_documento"])? limpiarCadena($_POST["numero_documento"]):"";
$extension        = isset($_POST["extension"])? limpiarCadena($_POST["extension"]):"";
$expedido         = isset($_POST["expedido"])? limpiarCadena($_POST["expedido"]):"";
$login            = isset($_POST["login"])? limpiarCadena($_POST["login"]):"";
$clave            = isset($_POST["clave"])? limpiarCadena($_POST["clave"]):"";
$correo           = isset($_POST["correo"])? limpiarCadena($_POST["correo"]):"";
$codigo_canal     = isset($_POST["codigo_canal"])? limpiarCadena($_POST["codigo_canal"]):"";
$telefono         = isset($_POST["telefono"])? limpiarCadena($_POST["telefono"]):"";
$id_condicion        = isset($_POST["id_condicion"])? limpiarCadena($_POST["id_condicion"]):"";

/*
$nombre           = 'ALE MARIACA';
$cargo            = 'SUPERVISOR NACIONAL';
$id_role          = '9';
$codigoAlmacen    = 'LPZ-OB';
$tipo_documento   = 'C';
$numero_documento = '12345678';
$extension        = '';
$expedido         = '';
$login            = 'amariaca';
$correo           = 'amariaca@gmail.com';
$codigo_canal     = 'C001';
$telefono         = '75201403';
*/

/*$id_usuario  = ''; */


switch ($_GET["op"]){

	case 'guardaryeditar':

		$role = $usuario->getNombreRole($id_role);
		$codigo_canal = $_SESSION['codigo_canal'];
		//echo "ROLE: " . $role . "<br>";

		$pass_res = 'Success';

		if($pass_res == 'Success'){

			if (empty($id_usuario)){

				// Obtenemos los permisos asignados al ROLE seleccionado para este usuario
				$permisos = $varios->getPermisos($id_role);

				// Obtenemos el código de Ciudad basado en el Código Almacen
				$codigo_ciudad = $varios->getCiudadFromAgencia($codigoAlmacen);

				// Insertamos los datos del usuario en la tabla usuarios y obtenemos el ID asignado a este usuario
				$imagen = "1487132068.jpg";
				$rspta=$usuario->insertar($id_role,$role,$nombre,$tipo_documento,$numero_documento,$extension,$expedido,$login,
						$codigo_canal,$codigoAlmacen,$codigo_ciudad,$correo,$telefono,$cargo,$imagen,$id_operador);

				// La llamada anterior nos devuelve el ID con el que se creó el usuario
				$id_usuario = $rspta;

				// Con el ID obtenido asignamos los permisos al usuario en función al ROLE asignado al usuario
				$rspta = $varios->asignaPermisos($id_usuario,$permisos,$id_role,$role,$id_operador);

				echo $rspta ? "Usuario registrado" : "Usuario no se pudo registrar";

			}else{

				$rspta=$usuario->editar($id_usuario,$id_role,$role,$nombre,$tipo_documento,$numero_documento,$extension,$login,
						$codigo_canal,$codigoAlmacen,$correo,$telefono,$cargo,$id_condicion,$id_operador);

				// Obtenemos los permisos asignados al ROLE seleccionado para este usuario
				$permisos = $varios->getPermisos($id_role);

				// Asignamos los permisos al usuario en función al nuevo ROLE asignado al usuario
				$rspta = $varios->asignaPermisos($id_usuario,$permisos,$id_role,$role,$id_operador);

				echo $rspta ? "Usuario actualizado" : "Usuario no se pudo actualizar";

			} // if empty usuario

		}else{ // END pass_res = Success

			echo $pass_res;

		}


	break;

	case 'verificar':

		$logina = isset($_POST["logina"])? limpiarCadena($_POST["logina"]):"";
		$clavea = isset($_POST["clavea"])? limpiarCadena($_POST["clavea"]):"";


		//$logina = 'admin';
		//$clavea = 'Alvaro$1234';
		//$clavea = 'P4!y3ct0Add#Inn0';


		//Hash SHA256 en la contraseña
		$clavehash = md5($clavea);

		//echo "LOGIN: " . $logina . "<br>";
		//echo "CLAVE: " . $clavea . "<br>";
		//echo "CLAVEHASH: " . $clavehash . "<br>";

		$rspta=$usuario->verificar($logina, $clavehash);
		$fetch=$rspta->fetch_object();

		if($fetch){
			$usuario_validado = 'SI';
		}else{
			$usuario_validado = 'NO';
		}

		if($fetch->id_condicion == '2'){

			$hizo_primer_cambio = $usuario->valida_primer_cambio($logina);
			//echo "HIZO PRIMER CAMBIO:" . $hizo_primer_cambio . '<br>';

			$dias_transcurridos = $usuario->obtiene_dias_desde_cambio($logina);
			//echo "DIAS TRANSCURRIDOS:" . $dias_transcurridos . '<br>';

			$max_days = $usuario->get_max_days();
			//echo "MAX DAYS:" . $max_days . '<br>';

			$data = array();
			if($usuario_validado == 'SI'){

				if(!($dias_transcurridos > $max_days || $hizo_primer_cambio == 'NO')){

					$_SESSION['idusuario']=$fetch->idusuario;
					$_SESSION['nombre']=$fetch->nombre;
					$_SESSION['imagen']=$fetch->imagen;
					$_SESSION['login']=$fetch->login;
					$_SESSION['nombre_agencia']=$fetch->nombre_agencia;
					$_SESSION['codigo_agencia']=$fetch->codigo_agencia;
					$_SESSION['codigo_canal']=$fetch->codigo_canal;
					$_SESSION['nombre_canal']=$fetch->nombre_canal;
					$_SESSION['nombre_ciudad']=$fetch->nombre_ciudad;
					$_SESSION['datos_asesor']=$fetch->datos_asesor;

					//Obtenemos los permisos del usuario
					//$marcados = $usuario->listarmarcados($fetch->id_role,$fetch->codigo_canal);
					$marcados = $usuario->listarmarcados($fetch->id_role);

					//Declaramos el array para almacenar todos los permisos marcados
					$valores=array();

					//Almacenamos los permisos marcados en el array
					while ($per = $marcados->fetch_object())
					{
						array_push($valores, $per->idpermiso);
					}
					//dep($valores);

					in_array(1,$valores)?$_SESSION['escritorio']=1:$_SESSION['escritorio']=0;
					in_array(2,$valores)?$_SESSION['admision']=1:$_SESSION['admision']=0;
                                        in_array(3,$valores)?$_SESSION['cobranza']=1:$_SESSION['cobranza']=0;
                                        in_array(4,$valores)?$_SESSION['reportexcajero']=1:$_SESSION['reportexcajero']=0;
                                        in_array(5,$valores)?$_SESSION['reportexag']=1:$_SESSION['reportexag']=0;
                                        in_array(6,$valores)?$_SESSION['reporte']=1:$_SESSION['reporte']=0;
                                        in_array(7,$valores)?$_SESSION['usuario']=1:$_SESSION['usuario']=0;
                                        in_array(8,$valores)?$_SESSION['admision_cobranza']=1:$_SESSION['admision_cobranza']=0;
                                        in_array(9,$valores)?$_SESSION['reporte_innova']=1:$_SESSION['reporte_innova']=0;
                                        in_array(10,$valores)?$_SESSION['contratos']=1:$_SESSION['contratos']=0;
                                        in_array(11,$valores)?$_SESSION['facturacion']=1:$_SESSION['facturacion']=0;
                                        in_array(12,$valores)?$_SESSION['canal']=1:$_SESSION['canal']=0;
                                        in_array(13,$valores)?$_SESSION['consulta_ws']=1:$_SESSION['consulta_ws']=0;
                                        in_array(14,$valores)?$_SESSION['consulta_old']=1:$_SESSION['consulta_old']=0;
					in_array(15,$valores)?$_SESSION['consulta_vit']=1:$_SESSION['consulta_vit']=0;
					in_array(16,$valores)?$_SESSION['anulacion']=1:$_SESSION['anulacion']=0;

					$data['status'] = 'ok'; 
					$data['datos'] = $_SESSION;

					$data['primer_cambio'] = $hizo_primer_cambio;
					$data['dias_transcurridos'] = $dias_transcurridos;
					$data['max_days'] = $max_days;


				}else{

					$_SESSION['escritorio']=1;
					$_SESSION['admision']=0;
					$_SESSION['cobranza']=0;
					$_SESSION['reportexcajero']=0;
					$_SESSION['reportexag']=0;
					$_SESSION['reporte']=0;
					$_SESSION['usuario']=0;
					$_SESSION['administracion']=0;
					$_SESSION['reporte_innova']=0;
					$_SESSION['canales']=0;
					$_SESSION['planes']=0;

					$_SESSION['imagen'] = '';
					$_SESSION['login'] = $logina;

					$data['primer_cambio'] = $hizo_primer_cambio;
					$data['dias_transcurridos'] = $dias_transcurridos;
					$data['max_days'] = $max_days;
					$data['login'] = $logina;

					// Debe cambiar la contraseña
					$data['status'] = 'error';
					$data['accion'] = 'CambioPassword';
					if($dias_transcurridos > $max_days){
						$data['motivo'] = 'MuchosDias';
					}
					if($hizo_primer_cambio == 'NO'){
						$data['motivo'] = 'NoHizoPrimerCambio';
					}

				} // if dias transcurridos y primer cambio


			}else{

				// Usuario o clave inválida
				// Usuario o Password inválido
				$data['status'] = 'error';
				$data['accion'] = 'UsuarioPasswordInvalido';
				$data['motivo'] = 'UsuarioPasswordInvalido';


			} // END if usuario validado

		}else{

			$data['status'] = 'error';
			$data['accion'] = 'UsuarioPasswordInvalido';
			$data['motivo'] = 'UsuarioPasswordInvalido';


		}  // END if id_condition == 2 /


		//echo "HOLA<br>";
		echo json_encode($data);

		//dep($data);
		//dep($_SESSION);
	break;

	case 'salir':
		//Limpiamos las variables de sesión
        	session_unset();

        	//Destruìmos la sesión
        	session_destroy();

        	//Redireccionamos al login
        	header("Location: ../index.php");

	break;


	case 'listar':

		$codigo_canal = $_SESSION['codigo_canal'];
		$rspta = $usuario->listar($codigo_canal);


		$data= Array();

		while ($reg=$rspta->fetch_object()){
			$editar = ' <button class="btn btn-success" onclick="mostrar('.$reg->idusuario.')"><i class="fa fa-pencil"></i></button>'. 
					  ' <button class="btn btn-danger" onclick="desactivar('.$reg->idusuario.')"><i class="fa fa-close"></i></button>';
 			$data[]=array(
 				"0"=>$editar,
 				"1"=>$reg->nombre,
 				"2"=>$reg->cargo,
 				"3"=>$reg->role,
 				"4"=>$reg->nombre_agencia,
				// "5"=>$reg->condicion,
				"5"=>($reg->id_condicion==2) ? '<span class="label bg-green">Activo</span>':
				'<span class="label bg-red">&nbsp;&nbsp;Baja&nbsp;&nbsp;</span>'
 				);
 		}
		$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
 		echo json_encode($results);


	break;


	case 'mostrar':

		$rspta=$usuario->mostrar($id_usuario);
		//Codificar el resultado utilizando json
		echo json_encode($rspta);

	break;

	case "selectAgencia":
		require_once "../modelos/Agencia.php";
		$agencia = new Agencia();
		$codigo_canal = $_SESSION['codigo_canal'];
		//echo "Cod Canal: " . $codigo_canal . "<br>";

		$rspta = $agencia->select($codigo_canal);
		//var_dump($rspta);

		echo '<option value=' . '0' . '>' . 'Elija una Agencia' . '</option>';

		while ($reg = $rspta->fetch_object())
		{
			echo '<option value=' . $reg->id_agencia . '>' . $reg->nombre . '</option>';
		}
	break;

	case "selectRole":
		$codigo_canal = $_SESSION['codigo_canal'];
		$rspta = $usuario->selectRole($codigo_canal);

		echo '<option value=' . '0' . '>' . 'Elija un Rol' . '</option>';

		while ($reg = $rspta->fetch_object())
		{
			echo '<option value=' . $reg->id_role . '>' . $reg->nombre_rol . '</option>';
		}
	break;

	case "selectEstado":
		$rspta = $usuario->selectEstado();

		echo '<option value=' . '0' . '>' . 'Elija un Estado' . '</option>';

		while ($reg = $rspta->fetch_object())
		{
			echo '<option value=' . $reg->id_condicion . '>' . $reg->nombre_condicion . '</option>';
		}
	break;

	case 'validaLogin':

		$rspta = $usuario->validaLogin($valida_login);

		$data = array();
		if($rspta){
			$data['status'] = 'error';
			$data['msg'] = 'Login ya existe. Favor elija otro!!';
		}else{
			$data['status'] = 'ok';
			$data['msg'] = 'Puede proceder!';
		}

		echo json_encode($data);

	break;

	case 'desactivar':
		$rspta=$usuario->desactivar($id_usuario);
 		echo $rspta ? "Usuario Desactivado" : "Usuario no se puede desactivar";
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

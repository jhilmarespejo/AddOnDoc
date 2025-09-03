<?php 
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
require_once "../modelos/ConsultasVitalicia.php";

$consulta_vit=new Consultas();
//$codigo_canal   = isset($_POST["codigo_canal"])? limpiarCadena($_POST["codigo_canal"]):"";
//$codigo_agencia = isset($_POST["codigo_agencia"])? limpiarCadena($_POST["codigo_agencia"]):"";


$id_usuario     = $_SESSION['idusuario'];
$codigo_agencia = $_SESSION['codigo_agencia'];
$codigo_canal	= $_SESSION['codigo_canal'];



//$id_usuario = '139';
//$codigo_agencia = 'ELT-JP';
//$codigo_canal = 'C001';

switch ($_GET["op"]){

	 case 'ventasfecha':

                $fecha_inicio=$_REQUEST["fecha_inicio"];
                $fecha_fin=$_REQUEST["fecha_fin"];
                $codigo_canal = $_SESSION['codigo_canal'];

	/*
		$fecha_inicio = '2025-05-03';
		$fecha_fin = '2025-05-05';
		$codigo_canal = 'C001';
	*/

                $rspta=$consulta_vit->ventasfecha($fecha_inicio,$fecha_fin,$codigo_canal);
		//dep($rspta);
		//die();
                //Vamos a declarar un array
                $data= Array();

		while ($reg=$rspta->fetch_object()){
                        $no_anular = 0;
                        $cobranza = 0;

			$f_ini = substr($reg->fechaInicio,0,10);
                        $fec_ini = new DateTime($reg->fechaInicio);
                        $fec_ini->modify('+1 year');
                        $f_fin   = $fec_ini ->format('Y-m-d');

                        if($reg->estado =='P'){
                                $estado2 = '<span class="label bg-green">Pendiente</span>';
                                $estado1 = '<button class="btn btn-success" onclick="anularAdmision('.$reg->id. ')"><i class="fa fa-trash"></i></button>';
                        }else if($reg->estado =='A'){
                                $estado2 = '<span class="label bg-red">Anulado</span>';
                                $estado1 = '<button class="btn btn-danger" onclick="anularAdmision('.$no_anular.')"><i class="fa fa-eye"></i></button>';
                        }else if($reg->estado =='C'){
                                $estado2 = '<span class="label bg-blue">Registrado</span>';
                                $estado1 = '<button class="btn btn-primary" onclick="anularAdmision('.$no_anular.')"><i class="fa fa-eye"></i></button>';
                                $cobranza = $reg->precio;
                        }else if($reg->estado =='F'){
                                $estado2 = '<span class="label bg-blue">Cobrado</span>';
                                $estado1 = '<button class="btn btn-primary" onclick="anularAdmision('.$no_anular.')"><i class="fa fa-eye"></i></button>';
                                $cobranza = $reg->precio;
                        }
                        $data[]=array(
                                "0"=>$reg->agencia,
                                "1"=>$reg->ciudad,
                                "2"=>$reg->plan,
				"3"=>$reg->numPrestamo,
                                "4"=>$reg->precio,
                                "5"=>$reg->nombre,
                                "6"=>$reg->cedula,
                                "7"=>$f_ini,
				"8"=>$f_fin,
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

		//$fecha_inicio = '2025-05-01';
		//$fecha_fin    = '2025-05-04';
		$rspta=$consulta_vit->ventasfechaagencia($fecha_inicio,$fecha_fin,$codigo_agencia);

		//dep($rspta);

		$data= Array();

 		while ($reg=$rspta->fetch_object()){
			$cobranza = 0;

			$f_ini = substr($reg->fechaInicio,0,10);
			$fec_ini = new DateTime($reg->fechaInicio);
                	$fec_ini->modify('+1 year');
                	$f_fin   = $fec_ini ->format('Y-m-d');


			if($reg->estado =='P'){
				$estado = '<span class="label bg-green">Pendiente</span>';
			}else if($reg->estado =='A'){
				$estado = '<span class="label bg-red">Anulado</span>';
			}else if($reg->estado =='C'){
				$estado = '<span class="label bg-green">Registrado</span>';
				$cobranza = $reg->precio;
			}else if($reg->estado =='F'){
				$estado = '<span class="label bg-blue">Cobrado</span>';
				$cobranza = $reg->precio;
			}
 			$data[]=array(
 				"0"=>$reg->agencia,
 				"1"=>$reg->ciudad,
 				"2"=>$reg->plan,
				"3"=>$reg->numPrestamo,
 				"4"=>$reg->precio,
 				"5"=>$reg->nombre,
				"6"=>$reg->cedula,
				"7"=>$f_ini,
				"8"=>$f_fin,
				"9"=>$estado
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

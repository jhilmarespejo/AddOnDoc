<?php 
//Incluímos inicialmente la conexión a la base de datos

//require "/var/www/html/AddOnInnova/config/Conexion.php";
//require "/var/www/html/AddOnInnova/modelos/Varios.php";

require "../config/Conexion.php";


Class Cobranzas
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	public function buscaCobranzaxCedula($cedula,$codigo_agencia){

		$sql = "SELECT t.id, CONCAT(nombres, ' ',ap_paterno, ' ',ap_materno) nombreCli, t.precio as deuda, 
					t.codigo_plan as cod_plan, t.cobranza, t.estado,t.fecha_creacion, t.codigo_plan,
					(select descripcion_plan_padre from plan_padre where codigo_plan_padre = t.codigo_plan limit 1) as plan,
					u.nombre as cajero, u.codigoAlmacen
				FROM temp t, clientes c, usuario u
				WHERE t.id_contratante = c.id
				AND t.id_usuario = u.idusuario
				AND t.cobranza = 'PENDIENTE'
				AND c.num_documento = '$cedula'
				AND u.codigoAlmacen = '$codigo_agencia'";

		return ejecutarConsulta($sql);

	}

	public function buscarDatosCobranzas($cedula,$codigo_agencia,$id_registro){


		$sql = "SELECT t.id, CONCAT(nombres, ' ',ap_paterno, ' ',ap_materno) nombre,
				t.precio as deuda, t.codigo_plan
				FROM temp t, clientes c, usuario u
				WHERE t.id_contratante = c.id
				AND t.id_usuario = u.idusuario
				AND t.cobranza = 'PENDIENTE'
				AND t.id = '$id_registro'";

		return ejecutarConsulta($sql);


	}

	public function generarFactura_c($cod_ope, $cod_tra,$id_usuario,$registro_a_facturar,$contrato){

		date_default_timezone_set('America/La_Paz');
		$fecha_cobranzas = date('Y-m-d H:i:s');
		//define("DB_NAMEX", "innovasa_AddOnInnova_test");

		//echo "NOMBRE BDX: " . DB_NAMEX . "<br>";

		$sql = "SELECT t.id, c.nombres, c.ap_paterno, c.ap_materno, c.num_documento as cedula, 
				c.genero, c.fecha_nacimiento, t.codigo_plan, t.cobranza,
				(SELECT distinct descripcion_plan_padre FROM plan_padre WHERE codigo_plan_padre = t.codigo_plan) plan,
				t.precio as deuda, t.codigo_canal, c.telefono, c.cod_cli
			FROM temp t, clientes c
			WHERE t.id_contratante = c.id
			AND t.cobranza = 'PENDIENTE'
			AND t.id = '$registro_a_facturar'";

		//echo "SQL: " . $sql . "<br>";
		$rspta =  ejecutarConsulta($sql);
		//var_dump($rspta);

		$rowcount=mysqli_num_rows($rspta);

		//echo "NUM FILAS: " . $rowcount . "<br>";

		$ret_val = array();
		if(!$rowcount){

			//echo "VERIFICO EXP:" . !$rowcount . "<br>";
			$ret_val['status'] = 'error';
			$ret_val['msg'] = 'Cliente facturado anteriormente';
			$ret_val['data'] = '';

		}else{

			$row = mysqli_fetch_assoc($rspta);

			$id = $row['id'];
			$cod_plan = $row['codigo_plan'];
			$cod_cli  = $row['cod_cli'];
			$deuda    = $row['deuda'];

			$ret_val['status'] = 'ok';
			$ret_val['msg'] = 'Cliente facturado de manera satisfactoria!';
			$ret_val['data'] = $row;

			$sql = "UPDATE temp SET fecha_cobranzas = '$fecha_cobranzas', cobranza= 'COBRADO', 
							codigo_plan = '$cod_plan', codigo_cli = '$cod_cli', codigo_ope = '$cod_ope', 
							codigo_tra = '$cod_tra', precio = '$deuda', estado = 'C', 
							usuario_cobranza = '$id_usuario', contrato = '$contrato'
						WHERE id = '$id'";

			$rc = ejecutarConsulta($sql);

			//$rc = true;
			if(!$rc){
				// En caso de que falle el UPDATE
				$row = $rc;
				$ret_val['status'] = 'error';
				$ret_val['msg'] = 'Error al UPDATE la table TEMP';
				$ret_val['data'] = '';

			}

		}
		//dep($ret_val);
		return $ret_val;


	}

	public function actualizamosCamposFacturacion($id_registro_a_facturar, $factura = null){

		date_default_timezone_set('America/La_Paz');
		$fecha_facturacion = date('Y-m-d H:i:s');

		if(!$factura){
			$sql = "UPDATE temp SET facturacion = 'FACTURADO', fecha_facturacion = '$fecha_facturacion', estado = 'F' 
					WHERE id = '$id_registro_a_facturar'";

		} else {
			$sql = "UPDATE temp SET 
						facturacion = 'FACTURADO', 
						fecha_facturacion = '$fecha_facturacion', 
						estado = 'F',
						factura = '$factura'
					WHERE id = '$id_registro_a_facturar'";

		}
		//echo "SQL UPDATE: " . $sql . "<br>";
		return ejecutarConsulta($sql);

	}

	function dep($data){
		$format = print_r('<pre>');
		$format .= print_r($data);
		$format .= print_r('</pre>');

		return $format;
	}

}

?>

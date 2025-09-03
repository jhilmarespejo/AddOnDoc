<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

class Varios
{
	//Implementamos nuestro constructor
	public function __construct() {}

	public function readPlanPadre($codigo)
	{

		$sql = "SELECT * FROM plan_padre WHERE codigo_plan_padre = '$codigo'";

		return ejecutarConsulta($sql);
	}

	// Obtener valores de cod cli, ope, tra
	public function getParameterValues($parameter)
	{

		$sql = "SELECT max(valor_actual) valor_actual FROM parametros
				WHERE parametro = '$parameter'";

		$rspta = ejecutarConsulta($sql);
		$row = mysqli_fetch_assoc($rspta);
		$valor_actual = $row['valor_actual'];
		$valor_nuevo = $valor_actual + 1;

		$sql = "UPDATE parametros SET valor_actual = '$valor_nuevo' WHERE  parametro = '$parameter'";
		$rspta = ejecutarConsulta($sql);

		return $valor_actual;
	}

	public function getNextContractValue($contrato)
	{

		$sql = "SELECT valor_actual valor_actual FROM contratos
				WHERE contrato = '$contrato'";

		$rspta = ejecutarConsulta($sql);
		$row = mysqli_fetch_assoc($rspta);
		$valor_actual = $row['valor_actual'];
		$valor_nuevo = $valor_actual + 1;

		$sql = "UPDATE parametros SET valor_actual = '$valor_nuevo' WHERE  contrato = '$contrato'";
		$rspta = ejecutarConsulta($sql);

		return $valor_actual;
	}

	public function getNumeroContrato($id)
	{
		// Sacamos el PLAN de la tabla "temp" del registro con id = '$id'

		$sql = "SELECT t.codigo_plan,  p.descripcion_plan_padre, t.codigo_plan_hijo
					FROM temp t, plan_padre p
					WHERE t.codigo_plan = p.codigo_plan_padre 
					AND t.id = '$id'";

		//echo "SQL: " . $sql . "<br>";
		$res = ejecutarConsulta($sql);
		$rowcount = mysqli_num_rows($res);
		$row = mysqli_fetch_assoc($res);

		//echo "NUM ROW: " . $rowcount . "<br>";
		//dep($row);

		if ($rowcount == 1) {
			$contrato = $row['codigo_plan_hijo'];
		} else {
			$row = mysqli_fetch_assoc($res);
			$contrato = $row['codigo_plan_hijo'];
		}
		//echo "CONTRATO: " . $contrato . "<br>";
		//die();

		// Buscamos el valor siguiente para ese PLAN
		$sql = "SELECT contrato, valor_actual FROM contratos WHERE contrato_sm = '$contrato'";
		//echo "SQL: " . $sql . "<br>";

		$rspta = ejecutarConsulta($sql);
		$row = mysqli_fetch_assoc($rspta);

		$contrato_lg  = $row['contrato'];
		$valor_actual = $row['valor_actual'];

		$valor_nuevo = $valor_actual + 1;

		$sql = "UPDATE contratos SET valor_actual = '$valor_nuevo' WHERE  contrato_sm = '$contrato'";
		$rspta = ejecutarConsulta($sql);


		// Left padding if number < $str_length
		$str_length = 7;
		$cc = substr("0000000{$valor_nuevo}", -$str_length);

		$numero_contrato_cli = $contrato_lg . '-' . $cc;

		return $numero_contrato_cli;
	}

	public function buscaUsuarioWS($codigo_agencia)
	{

		$sql = "SELECT usuario, clave from usuarios_addon WHERE codigo_agencia ='$codigo_agencia'";
		$res = ejecutarConsulta($sql);
		$row = mysqli_fetch_assoc($res);

		return $row;
	}

	public function getCiudadFromAgencia($codigo_agencia)
	{

		$sql = "SELECT codigo_ciudad FROM agencias WHERE codigo_agencia = '$codigo_agencia'";

		$res = ejecutarConsulta($sql);
		$row = mysqli_fetch_assoc($res);

		return $row['codigo_ciudad'];
	}

	public function getCiudadSM($id_ciudad)
	{
		$sql = "SELECT ciudad_sm FROM ciudades WHERE id = '$id_ciudad'";

		$rs = ejecutarConsulta($sql);
		$row = mysqli_fetch_assoc($rs);

		return $row['ciudad_sm'];
	}

	//Implementar un método para listar los registros
	public function listar()
	{
		$sql = "SELECT id, codigo_plan, plan, contrato, canal, estado 
				FROM planes WHERE estado='A'";
		return ejecutarConsulta($sql);
	}

	//Implementar un método para listar los registros y mostrar en el select
	public function listarPaises()
	{
		$sql = "SELECT * FROM paises where estado='A'";
		return ejecutarConsulta($sql);
	}

	public function guardaCliente($parametro, $valor)
	{
		$sql = "INSERT INTO temp (parametro, valor) VALUES ('$parametro', '$valor')";
		return ejecutarConsulta($sql);
	}


	function getPrecioDelPlan($cod_plan, $codigo_canal)
	{
		$sql = "SELECT * FROM plan_padre
					WHERE codigo_plan_padre = '$cod_plan'
					AND codigo_canal = '$codigo_canal'";

		//echo "SQL get PRECIO: " . $sql . "<br>";
		return ejecutarConsulta($sql);
	}


	// Insertamos datos temporales
	public function insertar_temp($id_usuario, $id_contratante, $planes, $cod_plan_hijo, $fecha_creacion, $deuda, $codigo_canal, $cedula_asesor, $codigo_agencia)
	{
		$sql = "INSERT INTO temp(id_usuario,id_contratante,cedula_asesor,agencia_venta,codigo_plan,codigo_plan_hijo,codigo_canal,fecha_creacion,precio,estado) 
				VALUES('$id_usuario','$id_contratante','$cedula_asesor','$codigo_agencia','$planes','$cod_plan_hijo','$codigo_canal','$fecha_creacion','$deuda','P')";

		//echo "SQL: " . $sql . "<br>";
		//die();
		return ejecutarConsulta_retornarID($sql);
	}

	public function anularAdmision($id_admision, $id_usuario)
	{

		date_default_timezone_set('America/La_Paz');
		$fec_anula = date('Y-m-d H:i:s');

		$sql = "UPDATE temp SET estado = 'A', fecha_anulacion = '$fec_anula', cobranza = 'ANULADO',
					usuario_anulacion = '$id_usuario', estado = 'A'
					WHERE id = '$id_admision'";

		//echo "SQL: " . $sql . "<br>";
		return ejecutarConsulta($sql);
	}

	public function buscaClientesParaFacturar()
	{

		$sql = "SELECT t.id as id, c.nombres, c.ap_paterno, c.ap_materno, c.genero, c.cod_cli,
					c.tipo_documento, c.num_documento as cedula, c.extension, c.expedido, c.fecha_nacimiento,
					c.telefono, t.precio as deuda, t.codigo_plan, u.codigoAlmacen as codigo_agencia ,t.codigo_canal
				FROM temp t, clientes c, usuario u
				WHERE t.id_contratante = c.id
				AND t.id_usuario = u.idusuario
				AND t.estado = 'C'";

		return ejecutarConsulta($sql);
	}

	public function validate_strong_password($password)
	{
		// Validate password strength
		$uppercase = preg_match('@[A-Z]@', $password);
		$lowercase = preg_match('@[a-z]@', $password);
		$number    = preg_match('@[0-9]@', $password);
		$specialChars = preg_match('@[^\w]@', $password);


		if (!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 8) {
			$msg = 'La contraseña debe tener por lo menos 8 caracteres y debe incluir una mayúscula, una minúscula, un número y un caracter especial!!';
		} else {
			$msg = 'Success';
		}

		return $msg;
	}

	public function listarCiudades()
	{
		$sql = "SELECT * FROM ciudades WHERE estado = 'A'";

		return ejecutarConsulta($sql);
	}

	public function listarTipoContrato()
	{
		$sql = "SELECT * FROM tipo_contrato";

		return ejecutarConsulta($sql);
	}

	public function listarCanales()
	{
		$sql = "SELECT * FROM canal WHERE estado = 'A'";

		return ejecutarConsulta($sql);
	}

	public function listarAgencias($codigo_canal)
	{
		$sql = "SELECT * FROM agencias WHERE codigo_canal = '$codigo_canal'
					AND condicion = '1'";

		return ejecutarConsulta($sql);
	}

	public function consulta_facturacion_fecha($fecha_inicio, $fecha_fin)
	{

		$sql = "SELECT distinct t.id, a.nombre_agencia as agencia, k.nombre_ciudad as ciudad,
					(select distinct descripcion_plan_padre from plan_padre x where x.codigo_plan_padre = t.codigo_plan) as plan, 
                    t.precio, t.fecha_cobranzas as fechaCobranzas, t.fecha_facturacion as fechaFacturacion, 
					CONCAT(nombres, ' ',ap_paterno, ' ',ap_materno) cliente, c.tipo_documento as documento, c.num_documento as cedula, 
					c.genero, c.telefono, t.estado, t.codigo_plan
				FROM temp t, clientes c, usuario u, agencias a, ciudades k, plan_padre p
				WHERE t.id_contratante = c.id
				AND t.id_usuario = u.idusuario
				AND u.codigoAlmacen = a.codigo_agencia
				AND a.codigo_ciudad = k.id
                AND t.codigo_plan = p.codigo_plan_padre
				AND DATE(t.fecha_creacion) >= '$fecha_inicio' 
				AND DATE(t.fecha_creacion) <= '$fecha_fin'
				AND t.estado = 'C'
				ORDER by t.fecha_creacion";

		return ejecutarConsulta($sql);
	}

	public function getRegistroParaFacturar($id_registro)
	{
		$sql = "SELECT t.id as id, c.nombres, c.ap_paterno, c.ap_materno, c.genero, c.cod_cli,t.codigo_ope, t.codigo_tra,
					c.tipo_documento, c.num_documento as cedula, c.extension, c.expedido, c.fecha_nacimiento,
					c.telefono, t.precio as deuda, t.codigo_plan, u.codigoAlmacen as codigo_agencia ,t.codigo_canal
				FROM temp t, clientes c, usuario u
				WHERE t.id_contratante = c.id
				AND t.id_usuario = u.idusuario
				AND t.id = '$id_registro'";

		//echo "SQL: " . $sql . "<br>";

		return ejecutarConsulta($sql);
	}

	public function registraDatosLog($data)
	{
		$status_fact = $data['status_fact'];
		$mensaje_fact = $data['mensaje_fact'];
		$cod_cli = $data['cod_cli'];
		$cod_ope = $data['cod_ope'];
		$cod_tra = $data['cod_tra'];
		$tipo_documento = $data['tipo_documento'];
		$cedula = $data['cedula'];
		$extension = $data['extension'];
		$expedido = $data['expedido'];
		$ap_paterno = $data['ap_paterno'];
		$ap_materno = $data['ap_materno'];
		$nombres = $data['nombres'];
		$razon_social = $data['razon_social'];
		$genero = $data['genero'];
		$telefono = $data['telefono'];
		$email = $data['email'];
		$direccion = $data['direccion'];
		$fecha_nacimiento = $data['fecha_nacimiento'];
		$codigo_plan = $data['codigo_plan'];
		$precio = $data['precio'];
		$canal = $data['canal'];
		$codigo_agencia = $data['codigo_agencia'];
		$fecha_inicio = $data['fecha_inicio'];
		$hora_llamada = $data['hora_llamada'];
		$hora_retorno = $data['hora_retorno'];

		$sql = "INSERT INTO log_facturacion (status_fact, mensaje_fact, cod_cli, cod_ope,cod_tra,tipo_documento,
				cedula,extension,expedido,ap_paterno,ap_materno,nombres,razon_social,genero,telefono,
				email,direccion,fecha_nacimiento,codigo_plan,precio,canal,codigo_agencia,fecha_inicio,
				hora_llamada,hora_retorno) 
				VALUES ('$status_fact', '$mensaje_fact', '$cod_cli', '$cod_ope','$cod_tra','$tipo_documento',
				'$cedula','$extension','$expedido','$ap_paterno','$ap_materno','$nombres','$razon_social','$genero',
				'$telefono','$email','$direccion','$fecha_nacimiento','$codigo_plan','$precio','$canal','$codigo_agencia',
				'$fecha_inicio','$hora_llamada','$hora_retorno')";

		return ejecutarConsulta($sql);
	}

	public function listarPlanesCanal($codigo_canal, $genero)
	{
		$sql = "SELECT distinct codigo_plan_padre codigo_plan , descripcion_plan_padre plan, orden
					FROM plan_padre
					WHERE codigo_canal = '$codigo_canal'
					AND estado = 'A'
					AND visible = 'SI'
					AND genero IN ('" . $genero . "','X')
					ORDER BY orden";

		//echo "SQL: " . $sql . "<br>";
		return ejecutarConsulta($sql);
	}

	public function getPermisos($id_role)
	{
		$sql = "SELECT * FROM permisox
					WHERE id_role = '$id_role'";

		return ejecutarConsulta($sql);
	}

	public function asignaPermisos($id_usuario, $permisos, $id_role, $role, $id_operador)
	{

		date_default_timezone_set('America/La_Paz');
		$ldate = date('Y-m-d H:i:s');

		// Reseteamos los permisos anteriores en caso de que haya
		$sql = "UPDATE usuario_permiso SET estado = '0'
					WHERE idusuario = '$id_usuario'";

		$rs = ejecutarConsulta($sql);

		// Asignamos los nuevos permisos en tabla usuario_permiso
		foreach ($permisos as $val) {
			$id_permiso = $val['id_permiso'];
			$sql = "INSERT usuario_permiso (idusuario,idpermiso,estado,fecha_update,usuario_update) 
						VALUES ('$id_usuario','$id_permiso','1','$ldate','$id_operador')";

			$rs = ejecutarConsulta($sql);
		}

		// Actualizamos el id_role y el role en la tabla usuario
		$sql = "UPDATE usuario SET id_role = '$id_role', role = '$role' 
					WHERE idusuario = '$id_usuario'";

		return ejecutarConsulta($sql);
	}

	public function obtieneGeneroDelPlan($codigo_canal, $codigo_plan)
	{
		$sql = "SELECT distinct codigo_plan_padre, genero 
					FROM plan_padre
					WHERE codigo_canal = '$codigo_canal'
					AND estado = 'A'
					AND codigo_plan_padre = '$codigo_plan'";

		//echo "SQL: " . $sql . "<br>";
		return ejecutarConsulta($sql);
	}

	public function buscaClienteAntiguo($cedula, $plan_elegido)
	{
		/*
		$sql = "SELECT codigo_plan_renovacion, nombre_plan_renovacion 
		            FROM plan_renovacion
                    WHERE codigo_plan = '$plan_elegido'
                    AND codigo_plan_antiguo = (SELECT DISTINCT codigo_plan FROM clientes_antiguos WHERE cedula = '$cedula'
			AND DATE(fecha_fin) = (SELECT MAX(DATE(fecha_fin)) FROM clientes_antiguos WHERE cedula = '$cedula'))";
*/
		//echo "SQL: " . $sql . "<br>"; 

		$sql = "SELECT DISTINCT x.codigo_plan_renovacion, (SELECT rr.nombre_plan_renovacion FROM plan_renovacion rr WHERE rr.codigo_plan_renovacion=x.codigo_plan_renovacion LIMIT 1) nombre_plan_renovacion
  FROM (
SELECT r.codigo_plan_renovacion, r.nombre_plan_renovacion 
	FROM plan_renovacion r
 WHERE r.codigo_plan = '$plan_elegido'
   AND (r.codigo_plan_antiguo = (SELECT DISTINCT codigo_plan 
                                FROM clientes_antiguos a
                               WHERE a.cedula = '$cedula'
                                 AND DATE(a.fecha_fin) = (SELECT MAX(DATE(a1.fecha_fin)) FROM clientes_antiguos a1 WHERE a1.cedula = '$cedula')
                                 AND DATE(a.fecha_fin)>= DATE_SUB(NOW(),INTERVAL 1 YEAR)) 
   OR r.codigo_plan_antiguo = (SELECT DISTINCT p.codigo_plan_hijo
                                FROM temp t JOIN clientes c ON t.id_contratante = c.id
                                     JOIN plan_padre p ON p.codigo_plan_padre=t.codigo_plan
                        			 WHERE t.cobranza = 'COBRADO'
                        			   AND c.num_documento='$cedula'
                                 AND t.estado='F'
				 AND (p.descripcion_plan_hijo like '%RENO%'
                        	   OR p.descripcion_plan_hijo like '%MUJER%'
                        	   OR p.descripcion_plan_hijo like '%HOMBRE%'
                        	   OR p.descripcion_plan_hijo like '%COMBO%')
                                 AND DATE(t.fecha_cobranzas) >= DATE_SUB(NOW(),INTERVAL 1 YEAR)
                                 AND DATE(t.fecha_cobranzas) = (SELECT MAX(DATE(t1.fecha_cobranzas)) fecha FROM temp t1 WHERE t1.estado='F'
                                 AND t1.id_contratante=c.id)))) x";

		return ejecutarConsulta($sql);
	}

	public function obtieneElPlanRevovacion($codigo_plan)
	{

		$sql = "SELECT r.codigo_plan_renovacion, r.nombre_plan_renovacion
		FROM plan_padre p, plan_renovacion r
		WHERE p.codigo_plan_padre = r.codigo_plan
		AND p.codigo_plan_padre = '$codigo_plan'";

		return ejecutarConsulta($sql);
	}

	public function obtieneCodPlanHijo($gender, $fec_nac)
	{

		$sql = "
			SELECT cod_plan as codigo_plan_hijo, price 
			FROM age_equivalence
			WHERE gender = ?
			AND TIMESTAMPDIFF(YEAR, ?, CURDATE()) 
			    - (DATE_FORMAT(CURDATE(), '%m%d') < DATE_FORMAT(?, '%m%d'))
			    BETWEEN r_min_age AND r_max_age
			LIMIT 1";

		$resultado = ejecutarConsultaPreparada($sql, "sss", $gender, $fec_nac, $fec_nac);

		$plan = $resultado->fetch_assoc();

		return $plan;
	}

	public function obtieneDataCust($id_cliente)
	{

		$sql = "SELECT * FROM clientes WHERE id = '$id_cliente'";

		$res = ejecutarConsultaSimpleFila($sql);

		return $res;
	}

	public function buscaCodPlanHijo($cod_plan_padre)
	{

		$sql = "SELECT * FROM plan_padre WHERE codigo_plan_padre = '$cod_plan_padre'";

		$res = ejecutarConsultaSimpleFila($sql);

		return $res;
	}
}

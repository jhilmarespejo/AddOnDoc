<?php 
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
//Incluímos inicialmente la conexión a la base de datos
//require "/var/www/html/innova2/config/Conexion.php";
require '../config/Conexion.php';

Class Usuario
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	public function insertar($id_role,$role,$nombre,$tipo_documento,$numero_documento,$extension,$expedido,$login,$codigo_canal,
				$codigoAlmacen,$codigo_ciudad,$correo,$telefono,$cargo,$imagen,$id_operador)
	{
		date_default_timezone_set('America/La_Paz');
		$ldate=date('Y-m-d H:i:s');

		$clave = '123';
		$clavehash = md5($clave);

		$sql="INSERT INTO usuario (id_role,role,nombre,tipo_documento,num_documento,extension,expedido,login,codigo_canal,
						codigoAlmacen,codigo_ciudad,correo,clave,control,telefono,cargo,imagen,fecha_create,id_condicion,usuario_creacion)
		VALUES ('$id_role','$role','$nombre','$tipo_documento','$numero_documento','$extension','$expedido','$login',
					'$codigo_canal','$codigoAlmacen','$codigo_ciudad','$correo','$clavehash','$clave','$telefono','$cargo','$imagen','$ldate','2',$id_operador)";

		//echo "SQL: " . $sql . "<br><br>";
		return ejecutarConsulta_retornarID($sql);

	}

	//Implementamos un método para editar registros
	public function editar($id_usuario,$id_role,$role,$nombre,$tipo_documento,$numero_documento,$extension,$login,$codigo_canal,$codigoAlmacen,$correo,$telefono,$cargo,$id_condicion,$id_operador)
	{
		date_default_timezone_set('America/La_Paz');
		$ldate=date('Y-m-d H:i:s');

		$id_operador = $_SESSION['idusuario'];

		// Anulamos los permisos actuales
		$sql = "UPDATE usuario_permiso SET estado = '0', fecha_update = '$ldate', 
					usuario_update = '$id_operador'
					WHERE idusuario = '$id_usuario'
					AND idpermiso != 1";
		$rs = ejecutarConsulta($sql);


		// Agregamos los nuevos permisos
		switch($id_role){
			case '1':
				$role = 'VENDEDOR';

				// Insertamos permiso de VENDEDOR
				$id_permiso = 2;
				$sql = "INSERT usuario_permiso (idusuario,idpermiso,estado,fecha_update,usuario_update) 
					VALUES($id_usuario,$id_permiso,'1','$ldate',$id_operador)";
				$rs = ejecutarConsulta($sql);

			break;

			case '2':
				$role = 'COBRADOR';

				// Insertamos permisos de COBRADOR
				$id_permiso = 3;
				$sql = "INSERT usuario_permiso (idusuario,idpermiso,estado,fecha_update,usuario_update) 
					VALUES($id_usuario,$id_permiso,'1','$ldate',$id_operador)";
				$rs = ejecutarConsulta($sql);

				$id_rep_cajero = 9;
				$sql = "INSERT usuario_permiso (idusuario,idpermiso,estado,fecha_update,usuario_update) 
					VALUES($id_usuario,$id_rep_cajero,'1','$ldate',$id_operador)";
				$rs = ejecutarConsulta($sql);

				break;

			case '3':
				$role = 'SUPERVISOR PAF';

				// Agregamos permiso de SUPERVISOR PAF
				$id_permiso = 8;
				$sql = "INSERT usuario_permiso (idusuario,idpermiso,estado,fecha_update,usuario_update) 
					VALUES($id_usuario,$id_permiso,'1','$ldate',$id_operador)";
				$rs = ejecutarConsulta($sql);

				break;

				case '4':
				$role = 'SUPERVISOR NACIONAL';

				// Agregamos permiso de SUPERVISOR NACIONAL
				$id_permiso = 7;
				$sql = "INSERT usuario_permiso (idusuario,idpermiso,estado,fecha_update,usuario_update) 
					VALUES($id_usuario,$id_permiso,'1','$ldate',$id_operador)";
				$rs = ejecutarConsulta($sql);

				$id_gestion_usuario = 10; // Gestion usuarios
				$sql = "INSERT usuario_permiso (idusuario,idpermiso,estado,fecha_update,usuario_update) 
					VALUES($id_usuario,$id_gestion_usuario,'1','$ldate',$id_operador)";
				$rs = ejecutarConsulta($sql);


			break;

		}


		$sql="UPDATE usuario SET 
				id_role = '$id_role',
				role='$role',
				nombre='$nombre',
				tipo_documento='$tipo_documento',
				num_documento='$numero_documento',
				extension='$extension',
				login='$login',
				codigo_canal='$codigo_canal',
				codigoAlmacen='$codigoAlmacen',
				correo = '$correo',
				telefono='$telefono',
				cargo='$cargo',
				id_condicion='$id_condicion',
				fecha_update = '$ldate',
				usuario_update = '$id_operador' 
			WHERE idusuario='$id_usuario'";

		//echo "SQL: " . $sql . "<br>";
		return ejecutarConsulta($sql);
	}

	public function getNombreRole($id_role)
	{
		$sql = "SELECT * FROM roles
					WHERE id_role = '$id_role'";

		$res = ejecutarConsulta($sql);
		$row = mysqli_fetch_assoc($res);

		$role = $row['nombre_rol'];

		return $role;

	}


	// Listar los permisos que tiene cada ROL
	//public function listarmarcados($id_role,$codigo_canal)
	public function listarmarcados($id_role)
	{
		$sql = "SELECT id_permiso idpermiso
		FROM permisos 
		WHERE id_role = '$id_role'
		AND estado = 'A'";

		//echo "SQL: " . $sql . "<br>";
		return ejecutarConsulta($sql);
	}


	//Función para verificar el acceso al sistema
	public function verificar($login,$clave)
    {
		$sql = "SELECT idusuario, u.nombre, u.num_documento, u.tipo_documento,u.telefono,u.id_role,u.codigo_canal,u.login, 
					u.clave, u.control as password,s.codigo_agencia as codigo_agencia, imagen,
					c.nombre_ciudad,s.nombre_agencia, k.nombre_canal, datos_asesor, u.id_condicion
                     FROM usuario u, agencias s, ciudades c, canal k
                     where u.clave = '$clave'
                     and u.login = '$login'
                     and u.codigoAlmacen = s.codigo_agencia
                     and u.codigo_ciudad = c.id
                     and u.codigo_canal = k.id_canal";

		//echo "SQL: " . $sql . "<br>";
		return ejecutarConsulta($sql);

    }


	public function obtiene_dias_desde_cambio($login){

		$debug = 0;
		$sql = "SELECT TIMESTAMPDIFF(DAY, min(fecha_update), curdate()) AS dias_transcurridos
					FROM usuario
					WHERE login = '$login'";

		//echo "SQL: " . $sql . "<br>";
		$res = ejecutarConsulta($sql);
		if(!$res){
			echo "SQL: " . $sql . "<br>";
			echo "Error Obtiene dias desde Cambio " . "<br>";
			die();
		}


		$row = mysqli_fetch_assoc($res);
		$dias_transcurridos = $row['dias_transcurridos'];

		if($debug){
			echo "DIAS TRASNCURRIDOS: " . $dias_transcurridos . "<br>";
		}

		return $dias_transcurridos;

	}

	public function valida_primer_cambio($login){

		$debug = 0;

		$sql = "SELECT * FROM users_used_passwords
					WHERE login = '$login' AND estado = 'A' AND habilitado = 'SI'";

		$res = ejecutarConsulta($sql);
		if(!$res){
			echo "SQL: " . $sql . "<br>";
			echo "Error Valida Primer Cambio " . "<br>";
			die();
		}

		$numRows = mysqli_num_rows($res);

		$retCode = $numRows ? 'SI':'NO';

		if($debug){
			echo "Realizó el primer cambio? " . $retCode . "<br>";    
		}

		return $retCode;

	}

	public function get_max_days(){

		$debug = 0;

		$sql = "SELECT * FROM parametros where parametro = 'max_days'";
		$res = ejecutarConsulta($sql);
		if(!$res){
			echo "SQL: " . $sql . "<br>";
			echo "Error Obtiene Max Dias para Cambio Contraseña " . "<br>";
			die();
		}


		$row = mysqli_fetch_assoc($res);
		$max_days = $row['valor_inicial'];

		if($debug){
			echo "MAX DAYS: " . $max_days . "<br>";
		}
		return $max_days;
	}

	//Función para verificar el acceso al sistema
	public function buscarCliente($cedula)
    	{
    		$sql="SELECT * FROM clientes WHERE num_documento='$cedula'";
			//echo "SQL: " . $sql . "<br>";

		$res = ejecutarConsulta($sql);
    		return $res;
    	}

	public function buscarUsuario($id_usuario){
		$sql = "SELECT * FROM usuario
				WHERE idusuario = '$id_usuario'";

		$res = ejecutarConsulta($sql);
		return $res;

	}

	public function listar($codigo_canal){

		$sql = "SELECT idusuario, nombre, cargo, role, codigoAlmacen, a.nombre_agencia, u.id_condicion
					FROM usuario u, agencias a
					WHERE u.codigoAlmacen = a.codigo_agencia
					AND u.codigo_canal = '$codigo_canal'
					AND u.idusuario NOT IN('140','139','147')";

		return ejecutarConsulta($sql);

	}

	public function mostrar($id_usuario)
	{
		$sql="SELECT idusuario, nombre, cargo, role, id_role, codigoAlmacen, a.nombre_agencia as agencia, clave, tipo_documento, 
				num_documento as numero_documento,correo,
				extension, expedido, telefono, u.codigo_canal, login, u.id_condicion as id_condicion
			FROM usuario u, agencias a
			WHERE u.codigoAlmacen = a.codigo_agencia
			AND idusuario='$id_usuario'";

		return ejecutarConsultaSimpleFila($sql);

	}

	public function validaLogin($login)
	{
		$sql = "SELECT * FROM usuario WHERE login = '$login'";

		return ejecutarConsultaSimpleFila($sql);

	}

	//Implementamos un método para desactivar categorías
	public function desactivar($id)
	{
		$sql="UPDATE usuario SET id_condicion='1' WHERE idusuario='$id'";
		return ejecutarConsulta($sql);
	}

	public function selectRole($codigo_canal)
	{
		// C0XX = es común para todos
		$sql = "SELECT * FROM roles
				WHERE codigo_canal IN ('$codigo_canal','C0XX')";

		return ejecutarConsulta($sql);	
	}

	public function selectEstado()
	{
		$sql = "SELECT id, id_estado as id_condicion, estado as nombre_condicion FROM estado";

		return ejecutarConsulta($sql);	

	}

}

?>

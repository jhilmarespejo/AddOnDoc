<?php 
//Incluímos inicialmente la conexión a la base de datos
require_once "../config/Conexion.php";

Class Forgot_password
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	public function get_nombre_del_usuario($correo)
	{

		$sql = "SELECT * FROM usuario WHERE correo = '$correo'";

		//echo "SQL: " . $sql . "<br>";
		$res = ejecutarConsulta($sql);


		// SI FALLA LA CONEXION
		if(!$res){

			$data_user['status'] = 'Error';
			$data_user['mensaje'] = 'Conexión a la base fallo';

		}else{

			$num_rows = mysqli_num_rows($res);

			$data_user = array();
			if($num_rows > 0){

				$row = mysqli_fetch_assoc($res);

				$data_user['status']        = 'ok';
				$data_user['mensaje']       = 'Success';
				$data_user['nombre']        = $row['nombre'];
				$data_user['login']         = $row['login'];
				$data_user['codigo_canal']  = $row['codigo_canal'];

			}else{

				$data_user['status'] = 'Error';
				$data_user['mensaje'] = 'Correo no encontrado';
                                $data_user['correo'] = $correo;

			}

		}
		//dep($data_user);
		return $data_user;

	}

	public function get_number()
	{
		$numero_aleatorio = mt_rand(10000,99999);

		return $numero_aleatorio;

	}

	public function reset_password($clave, $login)
	{
		global $conexion;

		date_default_timezone_set('America/La_Paz');
		$currentTime = date( 'Y-m-d H:i:s');

		$data_user = array();
		$data_user['status'] = 'ok';

		$clavehash = md5($clave);

		$sql = "UPDATE usuario SET clave = '$clavehash', control='$clave', fecha_update = '$currentTime' 
			WHERE login = '$login'";

		$res = ejecutarConsulta($sql);

		if(!$res){

			$data_user['status'] = 'Error';
			$data_user['mensaje_user'] = 'Error al ejecutar el update tabla usuario';

		}else{

			$filas_afectadas = mysqli_affected_rows($conexion);

			//echo "Filas afectadas: " . $filas_afectadas . "<br>";
			$res = ($filas_afectadas>0)?true:false;

		}


		$sql="UPDATE users_used_passwords SET estado='I',fecha_cambio='$currentTime', habilitado='NO' 
				WHERE login = '$login'";

		$ans = ejecutarConsulta($sql);

		if(!$ans){

			$data_user['status'] = 'Error';
			$data_user['mensaje_used_psswd'] = 'Error al ejecutar el update used passwords';

		}else{

			$filas_afectadas = mysqli_affected_rows($conexion);

			//echo "Filas afectadas: " . $filas_afectadas . "<br>";
			$ans = ($filas_afectadas>0)?true:false;


		}

		return $data_user;
	}

}


?>

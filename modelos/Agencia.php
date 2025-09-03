<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Agencia
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}


	//Implementar un método para listar los registros y mostrar en el select
	public function select($codigo_canal)
	{
		$sql="SELECT codigo_agencia as id_agencia, nombre_agencia as nombre 
				FROM agencias 
				WHERE condicion='1'
				AND codigo_canal = '$codigo_canal'";

		return ejecutarConsulta($sql);
	}
}

?>

<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Canal
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	public function insertar($id_canal,$nombre_canal,$comision,$estado)
	{
		$fecha_registro=date('Y-m-d H:i:s');
		$fecha_inicio  =date('Y-m-d H:i:s');
		$fecha_fin     =date('Y-m-d H:i:s');
		$usuario       = 1;

		$sql = "INSERT INTO canal (id_canal, nombre_canal, comision, fecha_inicio, fecha_fin, estado,
					usuario_registro, fecha_registro) 
				VALUES ('$id_canal', '$nombre_canal', '$comision', '$fecha_inicio', '$fecha_fin', '$estado',
					'$usuario', '$fecha_registro')";

		//echo "SQL:" . $sql . "<br>";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para editar registros
	public function editar($id,$id_canal,$nombre_canal,$comision,$estado)
	{
		$sql="UPDATE canal SET id_canal = '$id_canal', nombre_canal='$nombre_canal', comision='$comision', 
				estado = '$estado' WHERE id='$id'";
		//echo "SQL:" . $sql . "<br>";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para desactivar categorías
	public function desactivar($id)
	{
		$sql="UPDATE canal SET estado='I' WHERE id='$id'";

		return ejecutarConsulta($sql);
	}

	//Implementamos un método para activar categorías
	public function activar($id)
	{
		$sql="UPDATE canal SET estado='A' WHERE id='$id'";
		return ejecutarConsulta($sql);
	}

	//Implementar un método para mostrar los datos de un registro a modificar
	public function mostrar($id)
	{
		$sql="SELECT id,id_canal,nombre_canal,comision,estado FROM canal WHERE id='$id'";
		//echo "SQL:" . $sql . "<br>";
		return ejecutarConsultaSimpleFila($sql);
	}

	//Implementar un método para listar los registros
	public function listar()
	{
		$sql="SELECT id, id_canal, nombre_canal, comision, estado 
				FROM canal WHERE estado='A'";

		$sql="SELECT id, id_canal, nombre_canal, comision, estado 
				FROM canal";

		return ejecutarConsulta($sql);
	}
	//Implementar un método para listar los registros y mostrar en el select
	public function select()
	{
		$sql="SELECT * FROM canal where condicion='A'";
		return ejecutarConsulta($sql);
	}
}

?>

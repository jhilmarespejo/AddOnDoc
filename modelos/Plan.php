<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Plan
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	public function insertar($codigo_plan,$plan,$contrato,$canal,$estado)
	{
		$fecha_registro = date('Y-m-d H:i:s');
		$fecha_inicio   = date('Y-m-d H:i:s');
		$fecha_fin      = date('Y-m-d H:i:s');
		$usuario        = 1;

		$sql = "INSERT INTO planes (codigo_plan,plan,contrato,canal,estado,usuario_registro,
					fecha_create,fecha_update) 
				VALUES ('$codigo_plan','$plan','$contrato','$canal','$estado','$usuario',
					'$fecha_registro','$fecha_registro')";

		//echo "SQL:" . $sql . "<br>";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para editar registros
	public function editar($id,$codigo_plan,$plan,$contrato,$canal,$estado)
	{
		$fecha_update=date('Y-m-d H:i:s');
		$sql="UPDATE planes SET codigo_plan='$codigo_plan', plan='$plan', 
					contrato='$contrato', canal='$canal',estado = '$estado', fecha_update = '$fecha_update'
				WHERE id='$id'";
		//echo "SQL:" . $sql . "<br>";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para desactivar categorías
	public function desactivar($id)
	{
		$sql="UPDATE planes SET estado='I' WHERE id='$id'";
		return ejecutarConsulta($sql);
	}

	//Implementamos un método para activar categorías
	public function activar($id)
	{
		$sql="UPDATE planes SET estado='A' WHERE id='$id'";
		return ejecutarConsulta($sql);
	}

	//Implementar un método para mostrar los datos de un registro a modificar
	public function mostrar($id)
	{
		$sql = "SELECT p.id,p.codigo_plan,p.plan,p.contrato, p.codigo_canal, c.canal, p.estado 
					FROM planes p, canal c
					WHERE p.canal = c.id_canal
					AND p.id = '$id'";
		//echo "SQL:" . $sql . "<br>";
		return ejecutarConsultaSimpleFila($sql);
	}

	//Implementar un método para listar los registros
	public function listar()
	{
		$sql="SELECT  distinct codigo_plan, id, plan, contrato, canal, estado 
				FROM planes WHERE estado='A'";
		return ejecutarConsulta($sql);
	}
	//Implementar un método para listar los registros y mostrar en el select
	public function listarPlanes($codigo_canal)
	{
		$sql="SELECT  distinct codigo_plan, id, plan, contrato, canal, estado 
				FROM planes WHERE estado='A'";
		return ejecutarConsulta($sql);
	}
}

?>

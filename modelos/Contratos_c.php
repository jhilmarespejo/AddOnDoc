<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Contrato_c
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementamos un método para insertar registros
	public function insertar_plan_padre($cod_plan_padre,$nombre_plan_padre,$orden_aparicion,$precio_padre,$id_beneficiario,
	$codigo_plan_hijo1,$nombre_plan_hijo1,$cantidad_plan_hijo1,$precio_plan_hijo1,$vigencia,$id_canal,$genero_plan,
	$visible,$codigo_operador)
	{
		date_default_timezone_set('America/La_Paz');
		$ldate=date('Y-m-d H:i:s');

		$sql = "INSERT INTO plan_padre (codigo_plan_padre,descripcion_plan_padre,orden,cantidad_padre,precio_padre,beneficiario,
					codigo_plan_hijo,descripcion_plan_hijo,cantidad_hijo,precio_hijo,fecha_inicio,vigencia,codigo_canal,genero,
					visible,usuario_registro,fecha_registro) 
				VALUES('$cod_plan_padre','$nombre_plan_padre','$orden_aparicion','1','$precio_padre','$id_beneficiario','$codigo_plan_hijo1',
					'$nombre_plan_hijo1','$cantidad_plan_hijo1','$precio_plan_hijo1','$ldate','$vigencia','$id_canal','$genero_plan',
					'$visible','$codigo_operador','$ldate')";

		return ejecutarConsulta_retornarID($sql);

	}

	// Insertamos el contrato en tabla CONTRATOS
	public function insertarContrato($nombre_plan,$id_ciudad,$gestion,$numero_contrato,$contrato,$contrato_sm,$tipo_contrato,
	$valor_inicial,$valor_actual,$id_canal,$estado,$fecha_creacion,$usuario_creacion)
	{
		$numero_contrato = intval($numero_contrato);
		$sql = "INSERT INTO contratos (nombre_plan,id_ciudad,gestion,numero_contrato,contrato,contrato_sm,tipo_contrato,
					valor_inicial,valor_actual,id_canal,estado,fecha_creacion,usuario_creacion) 
				VALUES ('$nombre_plan','$id_ciudad','$gestion','$numero_contrato','$contrato','$contrato_sm','$tipo_contrato',
					'$valor_inicial','$valor_actual','$id_canal','$estado','$fecha_creacion','$usuario_creacion')";

        echo "SQL CONTRATO: " . $sql . "<br>";
		return ejecutarConsulta($sql);

	}


	// Generamos el siguiente código del Plan Padre
	public function getNextPlanPadre()
	{
		$sql = "SELECT codigo_plan_padre, orden FROM plan_padre ORDER by ID desc limit 1";

		return ejecutarConsultaSimpleFila($sql);

	}

	public function generaNumeroContrato($contrato,$ciudad_sm,$gestion)
	{
		$contratoA = $contrato.'-'.$ciudad_sm.'-'.$gestion.'%';
		$contratoB = $contrato.'-'.$ciudad_sm.'-'.$gestion;
		$sql = "SELECT * FROM contratos 
			WHERE contrato like '$contratoA'
			ORDER BY id DESC limit 1";

		$rows = ejecutarConsulta($sql);
		$rowcount=mysqli_num_rows($rows);

		if($rowcount){
			$row = mysqli_fetch_assoc($rows);
			$contrato = $row['contrato'];

			$numero = intval(substr($row['contrato'],15,4)) + 1;
			$str_length = 4;

			$nuevo_num = substr("0000{$numero}", -$str_length);
		}else{
			$nuevo_num = '0001';
		}

		$contrato = $contratoB . '-' .$nuevo_num;

		return $contrato;

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
	public function listar($codigo_canal)
	{
		$sql="SELECT k.id, k.nombre_plan, c.nombre_ciudad as ciudad, k.gestion, k.numero_contrato, k.estado, k.id_canal,
				k.contrato, t.nom_tip_contrato, x.nombre_canal as canal
			FROM contratos k, ciudades c, tipo_contrato t, canal x
			WHERE k.id_ciudad = c.id
			AND k.tipo_contrato = t.id
			AND k.id_canal = x.id_canal
			AND k.estado='1'
			AND k.id_canal = '$codigo_canal'";
		//echo "SQL:" . $sql . "<br>";
		return ejecutarConsulta($sql);
	}

}

?>

<?php
//Incluímos inicialmente la conexión a la base de datos
require "../config/ConexionVitalicia.php";

Class Consultas
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	public function ventasfecha($fecha_inicio,$fecha_fin,$codigo_canal)
        {

                $sql = "SELECT t.id, a.nombre_agencia as agencia, k.nombre_ciudad as ciudad,
                                        (SELECT DISTINCT descripcion_plan_padre FROM plan_padre WHERE codigo_plan_hijo=t.codigo_plan) as plan,
                                        t.precio, t.fecha_creacion as fechaInicio,
                                        CONCAT_WS(' ', c.nombre1, c.nombre2, c.ap_paterno, c.ap_materno) AS nombre, 
					c.tipo_documento as documento, c.num_documento as cedula,
                                        c.genero, c.telefono, t.estado, t.numPrestamo
                                FROM temps_vit t, clientes_vit c, usuario u, agencias a, ciudades k
                                WHERE t.id_contratante = c.id
                                AND t.id_usuario = u.idusuario
                                AND t.agencia_venta = a.codigo_agencia
                                AND a.codigo_ciudad = k.id
                                AND DATE(t.fecha_creacion) >= '$fecha_inicio'
                                AND DATE(t.fecha_creacion) <= '$fecha_fin'
                                AND t.codigo_canal = '$codigo_canal'
                                ORDER by t.fecha_creacion desc";

		//echo "SQL: " . $sql . "<br>";

                return ejecutarConsulta($sql);
        }


	public function ventasfechaagencia($fecha_inicio,$fecha_fin,$codigo_agencia){

		$sql = "SELECT t.id, a.codigo_agencia, a.nombre_agencia as agencia, k.nombre_ciudad as ciudad,
				  (SELECT DISTINCT descripcion_plan_padre FROM plan_padre WHERE codigo_plan_hijo=t.codigo_plan) as plan,
				  t.precio,t.fecha_creacion as fechaInicio, t.fecha_cobranzas as fechaCobranzas,
				  t.fecha_facturacion as fechaFacturacion,
				  CONCAT_WS(' ', c.nombre1, c.nombre2, c.ap_paterno, c.ap_materno) AS nombre, c.tipo_documento,
				  c.num_documento as cedula, c.genero, c.fecha_nacimiento,c.telefono,
				  u.nombre as usuario, t.estado,t.numPrestamo
			  FROM temps_vit t, clientes_vit c, usuario u, agencias a, ciudades k
			  WHERE t.id_contratante = c.id
			  AND t.id_usuario = u.idusuario
			  AND u.codigoAlmacen = a.codigo_agencia
			  AND a.codigo_ciudad = k.id
			  AND DATE(t.fecha_creacion) >= '$fecha_inicio'
			  AND DATE(t.fecha_creacion) <= '$fecha_fin'
			  AND t.agencia_venta = '$codigo_agencia'
			  ORDER by t.fecha_creacion desc";

		//echo "SQL: " . $sql . "<br>";
		return ejecutarConsulta($sql);
	}


}

?>

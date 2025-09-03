<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Consultas
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	public function ventasfecha($fecha_inicio,$fecha_fin,$codigo_canal)
	{

		$sql = "SELECT t.id, a.nombre_agencia as agencia, k.nombre_ciudad as ciudad,
					(SELECT DISTINCT descripcion_plan_padre FROM plan_padre WHERE codigo_plan_padre=t.codigo_plan) as plan,  
					t.precio, t.fecha_creacion as fechaInicio, 
					CONCAT(nombres, ' ',ap_paterno, ' ',ap_materno) cliente, c.tipo_documento as documento, c.num_documento as cedula, 
					c.genero, c.telefono, t.estado
				FROM temp t, clientes c, usuario u, agencias a, ciudades k
				WHERE t.id_contratante = c.id
				AND t.id_usuario = u.idusuario
				AND u.codigoAlmacen = a.codigo_agencia
				AND a.codigo_ciudad = k.id
				AND DATE(t.fecha_creacion) >= '$fecha_inicio' 
				AND DATE(t.fecha_creacion) <= '$fecha_fin'
				AND t.codigo_canal = '$codigo_canal' 
				ORDER by t.fecha_creacion desc";

		return ejecutarConsulta($sql);	
	}


	public function comprasfecha($fecha_inicio,$fecha_fin)
	{
		$sql="SELECT DATE(i.fecha_hora) as fecha,u.nombre as usuario, p.nombre as proveedor,i.tipo_comprobante,i.serie_comprobante,i.num_comprobante,i.total_compra,i.impuesto,i.estado FROM ingreso i INNER JOIN persona p ON i.idproveedor=p.idpersona INNER JOIN usuario u ON i.idusuario=u.idusuario WHERE DATE(i.fecha_hora)>='$fecha_inicio' AND DATE(i.fecha_hora)<='$fecha_fin'";
		return ejecutarConsulta($sql);
	}

	public function ventasfechacliente($fecha_inicio,$fecha_fin,$idcliente)
	{
		$sql="SELECT DATE(v.fecha_hora) as fecha,u.nombre as usuario, p.nombre as cliente,v.tipo_comprobante,v.serie_comprobante,v.num_comprobante,v.total_venta,v.impuesto,v.estado FROM venta v INNER JOIN persona p ON v.idcliente=p.idpersona INNER JOIN usuario u ON v.idusuario=u.idusuario WHERE DATE(v.fecha_hora)>='$fecha_inicio' AND DATE(v.fecha_hora)<='$fecha_fin' AND v.idcliente='$idcliente'";
		return ejecutarConsulta($sql);
	}



	public function ventasfechacanalagencia($fecha_inicio,$fecha_fin,$codigo_canal,$codigo_agencia){
		if($codigo_agencia == 'TODO'){

			$sql = "SELECT t.id, a.codigo_agencia, a.nombre_agencia as agencia, k.nombre_ciudad as ciudad,
					(SELECT DISTINCT descripcion_plan_padre FROM plan_padre WHERE codigo_plan_padre=t.codigo_plan) as plan,
					t.precio,t.fecha_creacion as fechaInicio,
					t.fecha_cobranzas as fechaCobranzas,
					t.fecha_facturacion as fechaFacturacion, CONCAT(nombres, ' ',ap_paterno, ' ',ap_materno) nombre,
					c.tipo_documento, c.num_documento as cedula,
					c.genero, c.fecha_nacimiento,c.telefono, u.nombre as usuario, t.estado
					FROM temp t, clientes c, usuario u, agencias a, ciudades k
					WHERE t.id_contratante = c.id
					AND t.id_usuario = u.idusuario
					AND u.codigoAlmacen = a.codigo_agencia
					AND a.codigo_ciudad = k.id
					AND DATE(t.fecha_creacion) >= '$fecha_inicio' AND DATE(t.fecha_creacion) <= '$fecha_fin'
					AND a.codigo_canal = '$codigo_canal'
					ORDER by t.fecha_creacion desc";


		}else{

			$sql = "SELECT t.id, a.codigo_agencia, a.nombre_agencia as agencia, k.nombre_ciudad as ciudad, 
					(SELECT DISTINCT descripcion_plan_padre FROM plan_padre WHERE codigo_plan_padre=t.codigo_plan) as plan, 
					t.precio,t.fecha_creacion as fechaInicio, 
					t.fecha_cobranzas as fechaCobranzas, 
					t.fecha_facturacion as fechaFacturacion, CONCAT(nombres, ' ',ap_paterno, ' ',ap_materno) nombre, 
					c.tipo_documento, c.num_documento as cedula, 
					c.genero, c.fecha_nacimiento,c.telefono, u.nombre as usuario, t.estado
					FROM temp t, clientes c, usuario u, agencias a, ciudades k 
					WHERE t.id_contratante = c.id 
					AND t.id_usuario = u.idusuario 
					AND u.codigoAlmacen = a.codigo_agencia 
					AND a.codigo_ciudad = k.id 
					AND DATE(t.fecha_creacion) >= '$fecha_inicio' AND DATE(t.fecha_creacion) <= '$fecha_fin' 
					AND a.codigo_agencia = '$codigo_agencia'
					AND a.codigo_canal = '$codigo_canal'
					ORDER by t.fecha_creacion desc";
		}
		//echo "SQL: " . $sql . "<br>";
		return ejecutarConsulta($sql);
	}

	public function ventasfechaagencia($fecha_inicio,$fecha_fin,$codigo_agencia){
		$sql = "SELECT t.id, a.codigo_agencia, a.nombre_agencia as agencia, k.nombre_ciudad as ciudad, 
					(SELECT DISTINCT descripcion_plan_padre FROM plan_padre WHERE codigo_plan_padre=t.codigo_plan) as plan, 
					t.precio,t.fecha_creacion as fechaInicio, 
					t.fecha_cobranzas as fechaCobranzas, 
					t.fecha_facturacion as fechaFacturacion, CONCAT(nombres, ' ',ap_paterno, ' ',ap_materno) nombre, 
					c.tipo_documento, c.num_documento as cedula, 
					c.genero, c.fecha_nacimiento,c.telefono, u.nombre as usuario, t.estado
					FROM temp t, clientes c, usuario u, agencias a, ciudades k 
					WHERE t.id_contratante = c.id 
					AND t.id_usuario = u.idusuario 
					AND u.codigoAlmacen = a.codigo_agencia 
					AND a.codigo_ciudad = k.id 
					AND DATE(t.fecha_creacion) >= '$fecha_inicio' AND DATE(t.fecha_creacion) <= '$fecha_fin' 
					AND t.agencia_venta = '$codigo_agencia'
					ORDER by t.fecha_creacion desc";

		return ejecutarConsulta($sql);
	}

	public function ventasfechacajero($fecha_inicio,$fecha_fin,$codigo_agencia,$id_usuario,$codigo_canal){
		$sql = "SELECT t.id, a.codigo_agencia, a.nombre_agencia as agencia, k.nombre_ciudad as ciudad, 
					(SELECT DISTINCT descripcion_plan_padre FROM plan_padre WHERE codigo_plan_padre=t.codigo_plan) as plan, 
					t.precio,t.fecha_creacion as fechaInicio, 
					t.fecha_cobranzas as fechaCobranzas, 
					t.fecha_facturacion as fechaFacturacion, CONCAT(nombres, ' ',ap_paterno, ' ',ap_materno) nombre, 
					c.tipo_documento, c.num_documento as cedula, 
					c.genero, c.fecha_nacimiento, u.nombre as usuario, t.estado
					FROM temp t, clientes c, usuario u, agencias a, ciudades k 
					WHERE t.id_contratante = c.id 
					AND t.id_usuario = u.idusuario 
					AND u.codigoAlmacen = a.codigo_agencia 
					AND a.codigo_ciudad = k.id 
					AND t.cobranza = 'COBRADO'
					AND DATE(t.fecha_cobranzas) >= '$fecha_inicio' AND DATE(t.fecha_cobranzas) <= '$fecha_fin' 
					AND u.codigoAlmacen = '$codigo_agencia'
					AND t.usuario_cobranza = '$id_usuario'
					AND t.codigo_canal = '$codigo_canal' 
					ORDER by t.fecha_cobranzas desc";
    //echo "SQL: " . $sql . "<br>";
		return ejecutarConsulta($sql);
	}

	public function consulta_clientes_antiguos($cedula){
/*

		$sql = "SELECT * FROM clientes_antiguos
			 WHERE cedula = '$cedula'
			UNION
			SELECT t.id, c.num_documento as cedula, CONCAT(IFNULL(nombres,''), ' ',IFNULL(ap_paterno,''), ' ',IFNULL(ap_materno,'')) nombre,
			       codigo_plan_hijo as codigo_plan,
			       descripcion_plan_hijo as plan,
			       DATE(DATE_ADD(t.fecha_cobranzas, INTERVAL 8 MONTH)) as fecha_fin,t.fecha_cobranzas as fecha_create, 
			       u.nombre as nombre_usuario, t.id_usuario usuario_create
			  FROM temp t, clientes c, usuario u, plan_padre p
			 WHERE t.id_contratante = c.id
			   AND t.id_usuario = u.idusuario
			   AND t.cobranza = 'COBRADO'
			   AND p.codigo_plan_padre=t.codigo_plan
			   AND (p.descripcion_plan_hijo like '%RENO%'
			    OR p.descripcion_plan_hijo like '%MUJER%'
			    OR p.descripcion_plan_hijo like '%HOMBRE%'
			    OR p.descripcion_plan_hijo like '%COMBO%')
			   AND c.num_documento='$cedula'";
*/
/*
                $sql = "SELECT * FROM clientes_antiguos
                                WHERE cedula = '$cedula'";
*/
		$sql = "SELECT z.*
  FROM 
      (SELECT * FROM clientes_antiguos
			 WHERE cedula = '$cedula'  
			UNION
			SELECT t.id, c.num_documento as cedula, CONCAT(IFNULL(nombres,''), ' ',IFNULL(ap_paterno,''), ' ',IFNULL(ap_materno,'')) nombre,
			       codigo_plan_hijo as codigo_plan,
			       descripcion_plan_hijo as plan,
			       DATE(DATE_ADD(t.fecha_cobranzas, INTERVAL 8 MONTH)) as fecha_fin,t.fecha_cobranzas as fecha_create, 
			       u.nombre as nombre_usuario, t.id_usuario usuario_create
			  FROM temp t, clientes c, usuario u, plan_padre p
			 WHERE t.id_contratante = c.id
			   AND t.id_usuario = u.idusuario
			   AND t.cobranza = 'COBRADO'
			   AND p.codigo_plan_padre=t.codigo_plan
			   AND (p.descripcion_plan_hijo like '%RENO%'
			    OR p.descripcion_plan_hijo like '%MUJER%'
			    OR p.descripcion_plan_hijo like '%HOMBRE%'
			    OR p.descripcion_plan_hijo like '%COMBO%')
			   AND c.num_documento='$cedula') z
  WHERE fecha_fin =(SELECT MAX(fecha_fin) FROM (SELECT * FROM clientes_antiguos
			 WHERE cedula = '$cedula'  
			UNION
			SELECT t.id, c.num_documento as cedula, CONCAT(IFNULL(nombres,''), ' ',IFNULL(ap_paterno,''), ' ',IFNULL(ap_materno,'')) nombre,
			       codigo_plan_hijo as codigo_plan,
			       descripcion_plan_hijo as plan,
			       DATE(DATE_ADD(t.fecha_cobranzas, INTERVAL 8 MONTH)) as fecha_fin,t.fecha_cobranzas as fecha_create, 
			       u.nombre as nombre_usuario, t.id_usuario usuario_create
			  FROM temp t, clientes c, usuario u, plan_padre p
			 WHERE t.id_contratante = c.id
			   AND t.id_usuario = u.idusuario
			   AND t.cobranza = 'COBRADO'
			   AND p.codigo_plan_padre=t.codigo_plan
			   AND (p.descripcion_plan_hijo like '%RENO%'
			    OR p.descripcion_plan_hijo like '%MUJER%'
			    OR p.descripcion_plan_hijo like '%HOMBRE%'
			    OR p.descripcion_plan_hijo like '%COMBO%')
			   AND c.num_documento='$cedula') w)";

                return ejecutarConsulta($sql);

        }

}

?>

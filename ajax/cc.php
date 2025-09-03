<?php 

	require "../config/Conexion.php";
	//require "../modelos/Usuario.php";
	//require "../modelos/Varios.php";


	$logina = 'admin';
	$clavea = 'Pablex@0258*1';


	$sql = "SELECT idusuario, u.nombre, u.num_documento, u.tipo_documento,u.telefono,u.id_role,u.codigo_canal,u.login, 
					u.clave, u.control as password,s.codigo_agencia as codigo_agencia, imagen,
					c.nombre_ciudad,s.nombre_agencia, k.nombre_canal, datos_asesor, u.id_condicion
                     FROM usuario u, agencias s, ciudades c, canal k
                     where u.clave = '$clave'
                     and u.login = '$login'
                     and u.codigoAlmacen = s.codigo_agencia
                     and u.codigo_ciudad = c.id
                     and u.codigo_canal = k.id_canal";

	echo "SQL: " . $sql;
	$rows =  ejecutarConsulta($sql);

	var_dump($rows);

        //$rows = mysqli_query($con, $sql);
	//	var_dump($rows);
        $i = 0;

        while ($row = mysqli_fetch_assoc($rows)) {
                $regresar[$i++] = $row;
        }

        //var_dump($regresar);
        //die();
        dep($regresar);


function dep($data){
        $format = print_r('<pre>');
        $format .= print_r($data);
        $format .= print_r('</pre>');
        return $format;
}

?>

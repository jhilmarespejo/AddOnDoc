<?php
require "config/Conexion.php";

date_default_timezone_set('America/La_Paz');// change according timezone
$currentTime = date('Y-m-d H:i:s');

$sql  = "SELECT * FROM usuario WHERE idusuario = 124";
$rows = mysqli_query($conexion,$sql);

$i = 1;
while($row = mysqli_fetch_assoc($rows)){

	$clave = md5($row['password']);
	$login = $row['login'];

	$sql = "SELECT idusuario, u.nombre, u.num_documento, u.tipo_documento,u.telefono,
					u.codigo_canal, s.codigo_agencia as codigo_agencia, imagen,
					c.nombre_ciudad,s.nombre_agencia, u.login, u.clave
				FROM usuario u, agencias s, ciudades c
				WHERE u.codigoAlmacen = s.codigo_agencia
				AND s.codigo_ciudad = c.id
				AND u.clave = '$clave' AND u.login = '$login'
				AND u.id_condicion = '2'";

	echo "SQL: " . $sql . "<br>";
	$res = mysqli_query($conexion,$sql);
	$row = mysqli_fetch_assoc($res);
	dep($row);
	if(!$res){
		echo "<br>No1: ". $i++ . ' - ' . "Nombre: " . $row['nombre']. 'Password: '. $clave .  "<br>";

		echo "ERROR: " . mysqli_error($conexion);
		die();
	}

	echo "<br>No2: ". $i++ . ' - ' . "Nombre: " . $row['nombre']. ' - Password: '. $clave .  "<br>";


}

function dep($data){
	$format = print_r('<pre>');
	$format .= print_r($data);
	$format .= print_r('</pre>');

	return $format;
}
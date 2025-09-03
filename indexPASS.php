<?php
require "config/Conexion.php";

date_default_timezone_set('America/La_Paz');// change according timezone
$currentTime = date('Y-m-d H:i:s');

$sql  = "SELECT * FROM usuario WHERE login = 'admin'";
$rows = mysqli_query($conexion,$sql);

$i = 1;
while($row = mysqli_fetch_assoc($rows)){

	echo "Nombre: " . $row['nombre']. "<br>";
	echo "Clave: " . $row['clave']. "<br>";
	echo "Psswd: " . $row['control'] . "<br>";
	echo "PWWD: " . md5($row['control']);

	//$clave = md5($row['password']);
	//$idusuario = $row['idusuario'];
	
	//$sql = "UPDATE usuario SET clave = '$clave'  WHERE idusuario = '$idusuario'";
	//$res = mysqli_query($conexion,$sql);
/*
	if(!$res){
		echo "ERROR: " . mysqli_error($conexion);
		die();
	}
*/
//	echo "No: ". $i++ . ' - ' . "Nombre: " . $row['nombre']. ' - Password: '. $clave .  "<br>";


}

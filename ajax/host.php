<?php 

$azure = '181.115.172.16';
$tipo = 'innovaapp2.innovasalud.bo';


$uip=$_SERVER['REMOTE_ADDR'];
echo "REM ADDR: " . $uip . "<br>";

if($uip == $azure){
	echo "Estoy en Azure!!!<br>";
}

$host=$_SERVER['HTTP_HOST'];
echo "HTTP HOST: " . $host . "<br>";

if($host == $tipo){
	echo "ESTOY EN TEST!!!<br>";
} 


$uri  = rtrim(dirname($_SERVER['PHP_SELF']),'/\\');
echo "PHP SEL: " . $uri . "<br>";

echo "MI DIRECTORIO ES: " . $uri . "<br>";

?>

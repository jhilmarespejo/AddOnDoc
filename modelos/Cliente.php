<?php 
//Incluímos inicialmente la conexión a la base de datos
require "../config/Conexion.php";

Class Cliente
{
	//Implementamos nuestro constructor
	public function __construct()
	{

	}

	//Implementar un método para ingresar al cliente
	public function insertar($tipo_documento,$num_documento,$extension,$ap_paterno,$ap_materno,
	$nombres,$fecha_nacimiento,$num_telefono,$genero,$cod_cli,$fecha_creacion)
	{
		if(is_null($ap_paterno)){
			$ap_paterno = $ap_materno;
			$ap_materno = '';
		}

		$sql="INSERT INTO clientes (tipo_documento,num_documento,extension,ap_paterno,ap_materno,nombres,
				fecha_nacimiento,cod_cli,genero,telefono,fecha_creacion)
		VALUES ('$tipo_documento','$num_documento','$extension','$ap_paterno','$ap_materno','$nombres',
				'$fecha_nacimiento','$cod_cli','$genero','$num_telefono','$fecha_creacion')";

		//echo "SQL: " . $sql . "<br>";
		//die();
		$idingresonew=ejecutarConsulta_retornarID($sql);

		return $idingresonew;
	}


	function guardaBeneficiario($tipo_documento_ben,$num_documento_ben,$extension_ben,
							$ap_paterno_ben,$ap_materno_ben,$nombres_ben,$fecha_nacimiento_ben,
							$genero_ben,$telefono_ben,$fecha_creacion_ben)
	{

		$sql="INSERT INTO clientes (tipo_documento,num_documento,extension,ap_paterno,ap_materno,nombres,
				fecha_nacimiento,genero,telefono,fecha_creacion)
		VALUES ('$tipo_documento_ben','$num_documento_ben','$extension_ben','$ap_paterno_ben','$ap_materno_ben',
					'$nombres_ben','$fecha_nacimiento_ben','$genero_ben','$telefono_ben','$fecha_creacion_ben')";

		//echo "SQL BEN: " . $sql . "<br>";
		$idingresonew=ejecutarConsulta_retornarID($sql);

		return $idingresonew;


	}

}

?>

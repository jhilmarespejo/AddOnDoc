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
	public function insertar(
    $id_usuario, $numero_prestamo, $codCanal, $codigo_agencia, $tipoBanca, $tipoDoc,
    $num_documento, $extension, $expedido, $ap_paterno, $ap_materno, $nombres,
    $fecha_nacimiento, $genero, $num_telefono, $codPlanElegido, $fechaInicio
	) {
			if (is_null($ap_paterno)) {
				$ap_paterno = $ap_materno;
				$ap_materno = NULL;
			}
		
			// División de nombres
			$pos = strpos($nombres, ' ');
			if ($pos !== false) {
				$nombre1 = substr($nombres, 0, $pos);
				$nombre2 = substr($nombres, $pos + 1);
			} else {
				$nombre1 = $nombres;
				$nombre2 = NULL;
			}

			$doc = limpiaCedula($num_documento);

			if ($extension == '') {
				$extension = $doc['ext'];
			}

			$documento = preg_replace('/\D/', '', $doc['ced']);
			if ($doc['exp']) {
				$expedido = $doc['exp'];
			}

			$pais = 'BOLIVIA';

			// Insertar
			$sql = "INSERT INTO vit_original 
				(user, numPrestamo, codCanal, codigoAgencia, tipoBanca, tipoDoc, documento, extension, expedido, paterno, materno, nombre1, nombre2, fechaNac, genero, celular, pais, codPlanElegido, fechaInicio)
				VALUES 
				('$id_usuario', '$numero_prestamo', '$codCanal', '$codigo_agencia', '$tipoBanca', '$tipoDoc', '$documento', '$extension', '$expedido', '$ap_paterno', '$ap_materno', '$nombre1', '$nombre2', '$fecha_nacimiento', '$genero', '$num_telefono', '$pais', '$codPlanElegido', '$fechaInicio')";
			$idingresonew = ejecutarConsulta_retornarID($sql);
			
			// Recuperar todos los datos insertados
			$sqlSelect = "SELECT * FROM vit_original WHERE id = $idingresonew LIMIT 1";
			$datosInsertados = ejecutarConsultaSimpleFila($sqlSelect);
			
			return $datosInsertados; // Devuelve array asociativo con todos los campos
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


	// JE: Toma los datos de la tabla vit_original y los inserta en clientes_vit y temps_vit
	function procesarRegistroVit($numPrestamo, $documento, $id_usuario, $registro) {
		
		
		// $sqlSelect = "SELECT 
		// 	(EXISTS (
		// 		SELECT 1 FROM temps_vit t 
		// 		WHERE t.numPrestamo = vo.numPrestamo
		// 	)
		// 	AND EXISTS (
		// 		SELECT 1 FROM clientes_vit c 
		// 		WHERE c.num_documento = vo.documento
		// 	)) AS existe_dato
		// FROM vit_original vo
		// WHERE vo.numPrestamo = '$numPrestamo'
		// AND vo.documento = '$documento'
		// LIMIT 1";

		// $verificacion = ejecutarConsultaSimpleFila($sqlSelect);
		// $existeEnAmbas = isset($verificacion['existe_dato']) ? (bool)$verificacion['existe_dato'] : false;

		// // Ejemplo de uso
		// if ($existeEnAmbas) {
		// 	// echo "El registro existe en temps_vit y clientes_vit";
		// 	// return [
		// 	// 	'success' => false,
		// 	// 	'message'=> 'verificar OV', 
		// 	// ];
		// } else {
			// echo "El registro NO existe en ambas tablas";
			try {
				$fechaNac = new DateTime($registro['fechaNac']);
				$fechaInicio = new DateTime($registro['fechaInicio']);
				$edad = $fechaInicio->diff($fechaNac)->y;
				
				// Limpiar celular
				$telefono = str_replace(' ', '', $registro['celular']);

				// Determinar código_cli, codigo_plan_hijo y codigo_tra según edad y género
				if ($registro['genero'] === 'F') {
					if ($edad >= 18 && $edad <= 35) {
						$codigo_cli = 1000000;
						$codigo_plan_hijo = 'PPCE0141';
						$codigo_tra = 1000000;
					} elseif ($edad >= 36 && $edad <= 50) {
						$codigo_cli = 2000000;
						$codigo_plan_hijo = 'PPCE0142';
						$codigo_tra = 2000000;
					} elseif ($edad >= 51) {
						$codigo_cli = 3000000;
						$codigo_plan_hijo = 'PPCE0143';
						$codigo_tra = 3000000;
					} else {
						throw new Exception("Edad fuera de rango válido ");
					}
				} else { // Masculino
					if ($edad >= 18 && $edad <= 35) {
						$codigo_cli = 4000000;
						$codigo_plan_hijo = 'PPCE0144';
						$codigo_tra = 4000000;
					} elseif ($edad >= 36 && $edad <= 50) {
						$codigo_cli = 5000000;
						$codigo_plan_hijo = 'PPCE0145';
						$codigo_tra = 5000000;
					} elseif ($edad >= 51) {
						$codigo_cli = 6000000;
						$codigo_plan_hijo = 'PPCE0146';
						$codigo_tra = 6000000;
					} else {
						throw new Exception("Edad fuera de rango válido ");
					}
				}

				// === Insertar en clientes_vit ===
				$sqlInsertCliente = "
					INSERT INTO clientes_vit (
						ap_materno, ap_paterno, canal, ciudad_nacimiento, cod_cli, correo,
						expedido, extension, fecha_creacion, fecha_nacimiento, fecha_update,
						genero, nombre1, nombre2, num_documento, num_documento_full,
						ocupacion, pais_nacimiento, telefono, tipo_documento
					) VALUES (
						'{$registro['materno']}', '{$registro['paterno']}', NULL, NULL, NULL, NULL,
						'{$registro['expedido']}', '{$registro['extension']}', NULL, '{$registro['fechaNac']}', NOW(),
						'{$registro['genero']}', '{$registro['nombre1']}', " . ($registro['nombre2'] ? "'{$registro['nombre2']}'" : "NULL") . ", 
						'{$registro['documento']}', NULL,
						NULL, NULL, '$telefono', '{$registro['tipoDoc']}'
					)";

				$id_contratante = ejecutarConsulta_retornarID($sqlInsertCliente);

				// === Insertar en temps_vit ===
				$procesadoValue = $registro['procesado'] ? "'{$registro['procesado']}'" : "NULL";
				
				$sqlInsertTemp = "
					INSERT INTO temps_vit (
						id_usuario, agencia_venta, cedula_asesor, cobranza, codigo_canal, codigo_cli,
						codigo_ope, codigo_plan, codigo_plan_hijo, codigo_tra, contrato,
						created_at, estado, factura, facturacion, fecha_anulacion,
						fecha_cobranzas, fecha_creacion, fecha_facturacion,
						id_beneficiario, id_contratante, indicador, modalidad,
						motivo_anulacion, numPago, numPrestamo, precio, procesado, tipo,
						updated_at, usuario_anulacion, usuario_cobranza
					) VALUES (
						$id_usuario, '{$registro['codigoAgencia']}', '{$registro['codigoAsesor']}', 'PENDIENTE', '{$registro['codCanal']}', '$codigo_cli',
						'6000004', '{$registro['codPlanElegido']}', '$codigo_plan_hijo', '$codigo_tra', 'Contrato_VITALICIA',
						'{$registro['fechaRegistro']}', 'C', NULL, 'PENDIENTE', NULL,
						'{$registro['fechaInicio']}', '{$registro['fechaRegistro']}', NULL,
						0, '$id_contratante', 'D', 'C',
						NULL, NULL, '$numPrestamo', 147, $procesadoValue, NULL,
						'{$registro['fechaRegistro']}', NULL, $id_usuario
					)";
				// dep($sqlInsertTemp);exit;
				ejecutarConsulta($sqlInsertTemp);

				return [
					'success' => true,
					'message' => 'Registro procesado exitosamente',
					'numPrestamo' => $numPrestamo,
					'documento' => $documento,
					'id_contratante' => $id_contratante,
					'edad_calculada' => $edad,
					'codigo_cli' => $codigo_cli,
					'codigo_plan_hijo' => $codigo_plan_hijo
				];

			} catch (Exception $e) {
				return [
					'success' => false,
					'message' => 'Error al procesar el registro: ' . $e->getMessage(),
					'numPrestamo' => $numPrestamo,
					'documento' => $documento,
					'error' => $e->getMessage()
				];
				
			}
		//}
		$registro = ejecutarConsultaSimpleFila($sqlSelect);
	}
	// JE: verifica si en numero de prestamo ya existe 
	public function verificarPrestamoExistente($numero_prestamo) {
		$sql = "SELECT documento, numPrestamo FROM vit_original WHERE numPrestamo = '$numero_prestamo'";
		return ejecutarConsultaSimpleFila($sql);
	}

	public function verificarRegistroSinPrestamo($num_documento, $ap_paterno, $ap_materno, $fecha_nacimiento) {
			$sqlDuplicadoSinPrestamo = "
				SELECT 
					CASE
						WHEN EXISTS (
							SELECT 1 
							FROM vit_original vo
							WHERE vo.documento = '$num_documento'
							AND vo.fechaNac = '$fecha_nacimiento'
							AND TRIM(LOWER(vo.paterno)) = TRIM(LOWER('$ap_paterno'))
							AND TRIM(LOWER(vo.materno)) = TRIM(LOWER('$ap_materno'))
							AND (vo.numPrestamo IS NULL OR TRIM(vo.numPrestamo) = '') -- sin préstamo
						) THEN 1  -- duplicado (ya existe en vit_original sin préstamo)

						WHEN EXISTS (
							SELECT 1 
							FROM vit_original vo
							WHERE vo.documento = '$num_documento'
							AND vo.fechaNac = '$fecha_nacimiento'
							AND TRIM(LOWER(vo.paterno)) = TRIM(LOWER('$ap_paterno'))
							AND TRIM(LOWER(vo.materno)) = TRIM(LOWER('$ap_materno'))
							AND (vo.numPrestamo IS NOT NULL AND TRIM(vo.numPrestamo) <> '') -- con préstamo
						) THEN 0  -- válido (ya existe en vit_original con préstamo, no se revisa más)

						WHEN EXISTS (
							SELECT 1 
							FROM clientes_vit c
							WHERE c.num_documento = '$num_documento'
							AND c.fecha_nacimiento = '$fecha_nacimiento'
							AND TRIM(LOWER(c.ap_paterno)) = TRIM(LOWER('$ap_paterno'))
							AND TRIM(LOWER(c.ap_materno)) = TRIM(LOWER('$ap_materno'))
							AND NOT EXISTS (
								SELECT 1 
								FROM temps_vit t
								WHERE t.codigo_cli = c.cod_cli
									AND TRIM(t.numPrestamo) <> '' -- con préstamo
							)
						) THEN 1 -- duplicado (está en clientes_vit sin préstamo en temps_vit)

						ELSE 0 -- no está en ninguna tabla, o tiene préstamo → válido
					END AS duplicado
				LIMIT 1;

			";

			//dep($sqlDuplicadoSinPrestamo);exit;

			return ejecutarConsultaSimpleFila($sqlDuplicadoSinPrestamo);
	}

}

?>

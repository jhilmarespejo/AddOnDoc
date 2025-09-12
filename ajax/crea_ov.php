
<?php
// ==================== NUEVO CÓDIGO ====================
// Verificar si se ejecuta desde el formulario

$numero_prestamo = $_POST['numero_prestamo'];

//  $mensaje = "OV creada correctamente para numPrestamo {$numero_prestamo}";
//         echo json_encode([
//             'status' => 'ok',
//             'message' => $mensaje
//         ]);



$connectDB = connect_DB();

// Leer todos los registros pendientes
$clientes = readTablaVitalicia($numero_prestamo);

// Verificar si hay registros
if (empty($clientes)) {
    
    echo "\n No hay registros para procesar.\n";
    $logData = [
        'error'   => 'No hay registros para procesar',
        'line'        => __LINE__,
    ];
    logSap($logData);
    //exit('No hay registros para procesar');
}

$host = "https://52.177.52.183";
$port = 50000;
$username = "GETSAP\\innova07";
$password = "MARCOlazarte#3872$";
$companyDB = "INNOVASALUD_TEST";

$authData = json_encode([
    "UserName" => $username,
    "Password" => $password,
    "CompanyDB" => $companyDB
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "{$host}:{$port}/b1s/v1/Login");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $authData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Content-Length: ' . strlen($authData)
]);

//PARA TIEMPO LIMITE 
set_time_limit(400); // 300 segundos = 5 minutos

$response = curl_exec($ch);

if (curl_errno($ch)) {
    $logData = [
        'error_type'   => 'Error de autenticación',
        'curl_errno'   => curl_errno($ch),
        'curl_error'   => curl_error($ch),
        'endpoint'     => $host . ':' . $port . '/b1s/v1/Login',
        'numPrestamo'  => $numPrestamo ?? 'No definido',
        'line'        => __LINE__,
    ];
    logSap($logData);
    echo "Error de autenticación: " . curl_error($ch) . "\n";
    curl_close($ch);
}

$authResponse = json_decode($response);
$sessionId = $authResponse->SessionId ?? null;

if (!$sessionId) {
    $logData = [
        'error_type'   => 'Fallo al iniciar sesión en SAP',
        'id_registro'  => $id_registro,
        'endpoint'     => $host . ':' . $port . '/b1s/v1/Login',
        'response'     => $response ?? 'Sin respuesta',
        'numPrestamo'  => $numPrestamo ?? 'No definido',
        'line'        => __LINE__,
    ];
    logSap($logData);
    echo "Fallo al iniciar sesión en SAP para ID $id_registro.\n";
    curl_close($ch);
    die();
    
} else {
    // echo "INICIO DE SESION EXITOSO .<br> \n";
}
$tot_cli = 0;
$maxProcesar = 25;

// dep($clientes); 
// exit();


$i=0;
date_default_timezone_set('America/La_Paz');
// Procesar todos los registros uno por uno
foreach ($clientes as $cliente) {
    $startTime = microtime(true);

    // echo "Numero prestamo -> ". $cliente->numPrestamo;
    
    try {
        $logData = [
            'title'   => 'Iniciando procesamiento de cliente',
            'numPrestamo' => $cliente->numPrestamo,
            'documento' => $cliente->num_documento,
            'memoria_inicio' => memory_get_usage()/1024/1024 . ' MB',
            'line'        => __LINE__,
        ];
        logSap($logData);
        
        // Iniciar temporizador
        $startcont = microtime(true);
        if ($tot_cli >= $maxProcesar) {
            break; // TERMINA PROCESO
        }
        // 1. Formatear datos del cliente según estructura SAP (sin actualizar BD aún)
        $dataBeneficiario = formatDataBenef($cliente);
        // dep($dataBeneficiario);

        // die();
        // Obtener datos clave del cliente
        $id_registro = $cliente->id;               // ID interno de la base local
        $cedula = $cliente->num_documento;          // Número de documento de identidad
        $numPrestamo = $cliente->numPrestamo;       // Número de préstamo asociado

        // 2. Consultar existencia en SAP por cédula y tipo Cliente (CardType 'C')
        $endpoint = "/b1s/v1/BusinessPartners?"
            . "\$filter=FederalTaxID%20eq%20'" . urlencode($cedula) . "'%20and%20CardType%20eq%20'C'"
            . "&\$select=CardCode,CardName"; // Seleccionar solo campos necesarios
        // VALIDAR POR CARNET DE IDENTIDAD Y LA DECHA DE NACIMIENTO TAMBIEN

        $url = "{$host}:{$port}{$endpoint}"; // Construir URL completa

        // Configurar solicitud GET
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            "Cookie: B1SESSION={$sessionId}" // Usar sesión activa
        ));
        // dep($endpoint);

        // Ejecutar consulta y para consultar si existe el cliente
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $logData = [
                'error_type' => 'Error al verificar existencia de cliente en SAP',
                'curl_errno' => curl_errno($ch),
                'curl_error' => curl_error($ch),
                'http_code' => $httpCode,
                'numPrestamo' => $numPrestamo,
                'endpoint' => $url,
                'request_data' => null,
                'response' => $response,
                'line'        => __LINE__,
            ];
            logSap($logData);
            // logSap($logData, $numPrestamo);
            
            error_log("Error CURL: " . curl_error($ch));
            ChangBDErr($numPrestamo, 'ERR_SAP_VER_CLI');
            continue;// Saltar al siguiente cliente
        } 

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            error_log("Error HTTP. Código: $httpCode");
            ChangBDErr($numPrestamo, 'ER_SAP_CLI_HTTP');
            continue;
        }

        $clientesSAP = json_decode($response, true);
        $cliente = $clientesSAP['value'];
        $cantidad = count($cliente); // Usar operador null coalescing
        // echo "   Cantidad Cliente: " . $cantidad . "<br> \n";

        // 3. Si no existe cliente con esa cédula en SAP
        if (!$cantidad) {
            $originalCardCode = $dataBeneficiario['CardCode']; // Código generado inicialmente
            $suffix = 0;       // Contador para sufijos
            $newCardCode = $originalCardCode; // Empezar con código original
            $maxAttempts = 100; // Límite de seguridad
            $exists = false;    // Bandera de existencia
            

            // 4. Bucle para encontrar código único
            do {
                // Escapar comillas simples para consulta SAP
                $encodedCardCode = str_replace("'", "''", $newCardCode);

                // Construir filtro de consulta
                $filter = "CardCode eq '{$encodedCardCode}'";
                $endpointCardCodeCheck = "/b1s/v1/BusinessPartners?\$filter=" . rawurlencode($filter);
                $urlCardCodeCheck = "{$host}:{$port}{$endpointCardCodeCheck}";

                // Configurar y ejecutar verificación de código
                curl_setopt($ch, CURLOPT_URL, $urlCardCodeCheck);
                $responseCardCodeCheck = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                
                if ($httpCode < 200 || $httpCode >= 300) {
                    $logData = [
                        'error_type' => 'Error al verificar CardCode en SAP',
                        'curl_errno' => curl_errno($ch),
                        'curl_error' => curl_error($ch),
                        'http_code' => $httpCode,
                        'numPrestamo' => $numPrestamo,
                        'endpoint' => $urlCreate,
                        'request_data' => $custData,
                        'response' => json_decode($response, true),
                        'line'        => __LINE__,
                    ];
                    logSap($logData);
                    ChangBDErr($numPrestamo, 'ERR_SAP_VER_COD');
                    continue;
                }

                $resultCardCodeCheck = json_decode($responseCardCodeCheck, true);

                // Verificar si el código existe
                $exists = !empty($resultCardCodeCheck['value']);

                // 5. Si existe, generar nuevo sufijo
                if ($exists) {
                    $suffix++;
                    // Formatear sufijo a 2 dígitos: 01, 02, etc.
                    $newCardCode = $originalCardCode . '-' . str_pad($suffix, 2, '0', STR_PAD_LEFT);

                    // Prevenir bucles infinitos
                    if ($suffix >= $maxAttempts) {
                        break;
                    }
                }
            } while ($exists && $suffix < $maxAttempts);

            // 6. Si después del bucle sigue existiendo, mostrar error
            if ($exists) {
                 $logData = [
                        'error_type' => 'CardCode duplicado después de',
                        'max_Attempts' => $maxAttempts,
                        'numPrestamo' => $numPrestamo,
                        'line'        => __LINE__,
                    ];
                logSap($logData);
                error_log("Error: CardCode duplicado después de {$maxAttempts} intentos");
                ChangBDErr($numPrestamo,'ER_CAR_COD_DUPLIC'); //
                continue;
            }
            // 7. Preparar datos para creación en SAP
            $custData = array(
                "CardCode" => $newCardCode,
                "CardName" => $dataBeneficiario['CardName'],
                "CardForeignName" => $dataBeneficiario['CardName'],
                "CardType" => $dataBeneficiario['CardType'],
                "Currency" => $dataBeneficiario['Currency'],
                "Cellular" => $dataBeneficiario['telefono'],
                "EmailAddress" => $dataBeneficiario['EmailAddr'],
                "GroupCode" => $dataBeneficiario['GroupCode'],
                "FederalTaxID" => $dataBeneficiario['Cedula'],
                "UnifiedFederalTaxID" => $dataBeneficiario['Cedula'],
                "U_TIPDOC" => $dataBeneficiario['U_TIPDOC'],
                "U_CEDULA" => $dataBeneficiario['Cedula'],
                "U_EXTENSION" => $dataBeneficiario['Extension'],
                "U_EXPEDICION" => $dataBeneficiario['Expedicion'],
                "U_FechaNac" => $dataBeneficiario['FechaNac'],
                "U_Genero" => $dataBeneficiario['Genero'],
                "U_DOCTYPE" => $dataBeneficiario['U_DOCTYPE'],
                "U_DOCNUM" => $dataBeneficiario['Cedula'],
                "U_CI" => $dataBeneficiario['Cedula'],
                "ContactEmployees" => array(
                    array(
                        "Name" => "pp NATURAL",
                        "FirstName" => $dataBeneficiario['PriNombre'],
                        "MiddleName" => $dataBeneficiario['SecNombre'],
                        "LastName" => $dataBeneficiario['ApPaterno'],
                        "Position"  => $dataBeneficiario['ApMaterno'],
                        "Phone1" => $dataBeneficiario['Cellular'],
                        "MobilePhone" => $dataBeneficiario['Cellular'],
                    )
                )
            );

            // 8. Configurar solicitud POST para creación
            $custJson = json_encode($custData);
            $endpointCreate = "/b1s/v1/BusinessPartners";
            $urlCreate = "{$host}:{$port}{$endpointCreate}";

            curl_setopt($ch, CURLOPT_URL, $urlCreate);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $custJson);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                "Cookie: B1SESSION={$sessionId}",
                'Content-Length: ' . strlen($custJson)
            ]);
            
            // 9. Ejecutar creación y obtener respuesta
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode < 200 || $httpCode >= 300) {
                // error_log("Error SAP ($httpCode): " . print_r(json_decode($response, true), true));
                ChangBDErr($numPrestamo, 'ER_HTTP_SAP_CRE_CLI');

                $error = json_decode($response, true)['error'] ?? null;
                $errorMessage = $error['message']['value'] ?? 'Mensaje de error no disponible';
                $errorCode = $error['code'] ?? 'Código no disponible';

                $logData = [
                    'error_type'   => 'Error al crear el dato en SAP',
                    'http_code'    => $httpCode,
                    'numPrestamo'  => $numPrestamo,
                    'error_code'   => $errorCode,
                    'error_message'=> $errorMessage,
                    'curl_errno'   => curl_errno($ch),
                    'curl_error'   => curl_error($ch),
                    'JSON' => $custJson,
                    'line'        => __LINE__,
                ];

                logSap($logData);
                continue; // Saltar al siguiente cliente
                //die();
            }

            // 10. Si creación fue exitosa (código 2xx)
            if ($httpCode >= 200 && $httpCode < 300) {
                $dataBeneficiario['CardCode'] = $newCardCode;
                // 11. Actualizar base de datos local
                try {
                    $connectDB = connect_DB(); // Obtener conexión
                    $fechaActual = date('Y-m-d H:i:s');
                    $sql = "UPDATE temps_vit 
                            SET procesado = 'SI', updated_at = ?
                            WHERE numPrestamo = ?";
                    $stmt = $connectDB->prepare($sql);
                    $stmt->bind_param("ss", $fechaActual, $numPrestamo);
                    $stmt->execute();

                    // Verificar filas afectadas
                    if ($stmt->affected_rows === 0) {
                        //error_log("Advertencia: No se actualizó registro $numPrestamo");
                        $logData = [
                            'error_type'   => 'Advertencia: No se actualizó registro',
                            'numPrestamo'  => $numPrestamo,
                            'line'        => __LINE__,
                        ];
                        logSap($logData);
                    } else {
                        // echo "Cliente NUEVO $cedula registrado en SAP<br> \n";
                    }
                } catch (Exception $e) {
                    $logData = [
                        'error_type'   => 'Error en la Base de Datos',
                        'numPrestamo'  => $numPrestamo,
                        'line'        => __LINE__,
                    ];
                    logSap($logData);
                    ChangBDErr($numPrestamo, 'ER_BD_ACT');
                    continue;
                }
            } else {
                // 12. Manejar errores de SAP
                $errorInfo = json_decode($response, true);
                // error_log("Error en la creacion de dato enSAP ($httpCode): " . print_r($errorInfo, true));
                $logData = [
                    'error_type'   => 'Error en la creacion de dato en SAP',
                    'http_code'    => $httpCode,
                    'error_infoe'   => $errorInfo,
                    'line'        => __LINE__,
                ];
                logSap($logData);
                ChangBDErr($numPrestamo, 'ER_SAP_CRE_CLI');
                continue;
            }
        } //END if (!$cantidad) no existe en SAP 
        else {
            // 13. Cliente ya existe en SAP - Obtener su CardCode REAL
            $dataBeneficiario['CardCode'] = $clientesSAP['value'][0]['CardCode'];  // ¡Clave para la OV!
            // echo "Cliente ANTIGUO $cedula ya registrado en SAP<br> \n";
            // Actualizar BD también en este caso
            try {
                $connectDB = connect_DB();
                $fechaActual = date('Y-m-d H:i:s');
                $sql = "UPDATE temps_vit 
                    SET procesado = 'SI', updated_at = ?
                    WHERE numPrestamo = ?";
                $stmt = $connectDB->prepare($sql);
                $stmt->bind_param("ss", $fechaActual, $numPrestamo);
                $stmt->execute();
            } catch (Exception $e) {
                error_log("Error BD: " . $e->getMessage());
            }
        }

        

        // AQUI EMPIEZA LA OV //
        // Fecha actual
        $DocDate = date('Y-m-d');
        //********* fecha_creacion de temps_vit*/

        // Obtener fechas
        $fechaInicio = $dataBeneficiario['fechaInicio'];
        $fechaFin = (new DateTime($fechaInicio))->modify('+1 year')->format('Y-m-d');

        // Datos básicos
        $canal = $dataBeneficiario['canal'];
        $cod_plan = $dataBeneficiario['cod_plan'];
        $NumAtCard = $dataBeneficiario['NumAtCard'];
        $CardName = $dataBeneficiario['CardName'];
        $CardCode = $dataBeneficiario['CardCode'];
        $cedula = $dataBeneficiario['Cedula'];
        $U_CONSULTORIO = $dataBeneficiario['U_CONSULTORIO'];

        $servicios = obtenemosServicios($canal, $cod_plan, $numPrestamo);
        // dep($servicios);
        $plan = $servicios[0]->plan ?? null;
        $precio_plan = $servicios[0]->precio ?? null;

        if (!$plan || !$precio_plan) {
            $error = [
                'error_type' => 'Datos de servicio incompletos',
                'error_message' => "Datos de servicio incompletos para el plan $cod_plan",
                'numPrestamo' => $numPrestamo,
                'servicios_data' => $servicios
            ];
            logSap($error);
            ChangBDErr($numPrestamo, 'ER_DAT_INCOMP');
            continue;
        }

        $plan        = $servicios[0]->plan;
        $precio_plan = $servicios[0]->precio;

        $contrato = isset($dataBeneficiario['contrato']) ? $dataBeneficiario['contrato'] : "";

        if (empty($contrato)) {
            $res = generaNumContrato($canal, $cod_plan, $numPrestamo); // sin $this porque es PHP procedural
            if (isset($res['estado']) && $res['estado'] === 'E') {
                $contrato = $res['contrato'];
            } else {
                // die("Error generando contrato: " . $res['mensaje']);
                    $error = [
                    'error_type' => 'Error generando contrato',
                    'error_message' => $res['mensaje'],
                    'numPrestamo' => $numPrestamo,
                ];
                logSap($error);
                ChangBDErr($numPrestamo,'ER_GEN_CONTR');
                continue;
            }
        }

        $datosCanal = obtenemosDatosCanal($canal);  

        //echo "Contrato: $contrato. <br> \n";

        $CardName  = $datosCanal[0]->razon_social;
        $CardCode  = $datosCanal[0]->CardCode;
        $U_NIT       = $datosCanal[0]->NIT;

        //return $datosCanal;

        if ($cod_plan == 'PPCE0141' || $cod_plan == 'PPCE0142') {
            $ordenVenta = array(
                "DocType" => "dDocument_Items",
                "DocDate" => $DocDate,  //"2024-12-05",   //----------
                "DocDueDate" => $DocDate, //"2024-12-05",  //----------
                "TaxDate" => $DocDate,  //"2024-12-05",   //----------
                "CardCode" => $CardCode,
                "CardName" => $CardName,
                "DocTotal" => $precio_plan,
                "DocCurrency" => "BS",
                "Comments" => $contrato,
                "NumAtCard" => $NumAtCard, //"CRS-PROVSP-240088",
                "U_NIT" => $U_NIT,
                "U_CONSULTORIO" => $U_CONSULTORIO,
                "U_RAZSOC" => $CardName,
                "U_CANAL" => $canal, //"C003",   //----------
                "U_ESTADOFC" => "V",
                "U_COMENTARIOS" => $contrato,   //"PPCE0001-LP-24-0001-0000088|20241205",  //----------
                "U_NumTransaccionExt" => $NumAtCard,
                "DocumentLines" => [
                    [
                        "LineNum" => 0,
                        "ItemCode" => $cod_plan, //"PPCE0001",  //----------
                        "ItemDescription" => $servicios[0]->plan,
                        "Quantity" => 1, //$servicios['cantidad'],
                        "PriceAfterVAT" => (float)$servicios[0]->precio,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 0,
                        "U_FECHAINI" => $fechaInicio,
                        "U_Contrato" => $contrato,
                        //"U_FECHAFIN" => "2025-12-11",
                        "U_FECHAFIN" => $fechaFin,
                    ],
                    [
                        "LineNum" => 1,
                        "ItemCode" => $servicios[0]->cod_servicio,
                        "ItemDescription" => $servicios[0]->servicio,
                        "Quantity" => (int)$servicios[0]->cantidad,  // 3,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,
                        "U_Contrato" => $contrato,
                        //"U_FECHAFIN" => "2025-12-11",
                        "U_FECHAFIN" => $fechaFin,
                    ],
                    [
                        "LineNum" => 2,
                        "ItemCode" => $servicios[1]->cod_servicio,
                        "ItemDescription" => $servicios[1]->servicio,
                        "Quantity" => (int)$servicios[1]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,
                        "U_Contrato" => $contrato,
                        //"U_FECHAFIN" => "2025-12-11",
                        "U_FECHAFIN" => $fechaFin,
                    ],
                    [
                        "LineNum" => 3,
                        "ItemCode" => $servicios[2]->cod_servicio,
                        "ItemDescription" => $servicios[2]->servicio,
                        "Quantity" => $servicios[2]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_Contrato" => $contrato,
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 4,
                        "ItemCode" => $servicios[3]->cod_servicio,
                        "ItemDescription" => $servicios[3]->servicio,
                        "Quantity" => $servicios[3]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 5,
                        "ItemCode" => $servicios[4]->cod_servicio,
                        "ItemDescription" => $servicios[4]->servicio,
                        "Quantity" => $servicios[4]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 6,
                        "ItemCode" => $servicios[5]->cod_servicio,
                        "ItemDescription" => $servicios[5]->servicio,
                        "Quantity" => $servicios[5]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 7,
                        "ItemCode" => $servicios[6]->cod_servicio,
                        "ItemDescription" => $servicios[6]->servicio,
                        "Quantity" => $servicios[6]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 8,
                        "ItemCode" => $servicios[7]->cod_servicio,
                        "ItemDescription" => $servicios[7]->servicio,
                        "Quantity" => $servicios[7]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                ]
            );
        }

        if ($cod_plan == 'PPCE0143') {
            $ordenVenta = array(
                "DocType" => "dDocument_Items",
                "DocDate" => $DocDate,  //"2024-12-05",   //----------
                "DocDueDate" => $DocDate, //"2024-12-05",  //----------
                "TaxDate" => $DocDate,  //"2024-12-05",   //----------
                "CardCode" => $CardCode,
                "CardName" => $CardName,
                "DocTotal" => $precio_plan,
                "DocCurrency" => "BS",
                "Comments" => $contrato,
                "NumAtCard" => $NumAtCard, //"CRS-PROVSP-240088",
                "U_NIT" => $U_NIT,
                "U_CONSULTORIO" => $U_CONSULTORIO,
                "U_RAZSOC" => $CardName,
                "U_CANAL" => $canal, //"C003",   //----------
                "U_ESTADOFC" => "V",
                "U_COMENTARIOS" => $contrato,   //"PPCE0001-LP-24-0001-0000088|20241205",  //----------
                "U_NumTransaccionExt" => $NumAtCard,
                "DocumentLines" => [
                    [
                        "LineNum" => 0,
                        "ItemCode" => $cod_plan, //"PPCE0001",  //----------
                        "ItemDescription" => $servicios[0]->plan,
                        "Quantity" => 1, //$servicios['cantidad'],
                        "PriceAfterVAT" => (float)$servicios[0]->precio,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 0,
                        "U_FECHAINI" => $fechaInicio,
                        "U_Contrato" => $contrato,
                        //"U_FECHAFIN" => "2025-12-11",
                        "U_FECHAFIN" => $fechaFin,
                    ],
                    [
                        "LineNum" => 1,
                        "ItemCode" => $servicios[0]->cod_servicio,
                        "ItemDescription" => $servicios[0]->servicio,
                        "Quantity" => (int)$servicios[0]->cantidad,  // 3,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,
                        "U_Contrato" => $contrato,
                        //"U_FECHAFIN" => "2025-12-11",
                        "U_FECHAFIN" => $fechaFin,
                    ],
                    [
                        "LineNum" => 2,
                        "ItemCode" => $servicios[1]->cod_servicio,
                        "ItemDescription" => $servicios[1]->servicio,
                        "Quantity" => (int)$servicios[1]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,
                        "U_Contrato" => $contrato,
                        //"U_FECHAFIN" => "2025-12-11",
                        "U_FECHAFIN" => $fechaFin,
                    ],
                    [
                        "LineNum" => 3,
                        "ItemCode" => $servicios[2]->cod_servicio,
                        "ItemDescription" => $servicios[2]->servicio,
                        "Quantity" => $servicios[2]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_Contrato" => $contrato,
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 4,
                        "ItemCode" => $servicios[3]->cod_servicio,
                        "ItemDescription" => $servicios[3]->servicio,
                        "Quantity" => $servicios[3]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 5,
                        "ItemCode" => $servicios[4]->cod_servicio,
                        "ItemDescription" => $servicios[4]->servicio,
                        "Quantity" => $servicios[4]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 6,
                        "ItemCode" => $servicios[5]->cod_servicio,
                        "ItemDescription" => $servicios[5]->servicio,
                        "Quantity" => $servicios[5]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 7,
                        "ItemCode" => $servicios[6]->cod_servicio,
                        "ItemDescription" => $servicios[6]->servicio,
                        "Quantity" => $servicios[6]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 8,
                        "ItemCode" => $servicios[7]->cod_servicio,
                        "ItemDescription" => $servicios[7]->servicio,
                        "Quantity" => $servicios[7]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 9,
                        "ItemCode" => $servicios[8]->cod_servicio,
                        "ItemDescription" => $servicios[8]->servicio,
                        "Quantity" => $servicios[8]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                ]
            );
        }

        if ($cod_plan == 'PPCE0144' || $cod_plan == 'PPCE0145') {
            $ordenVenta = array(
                "DocType" => "dDocument_Items",
                "DocDate" => $DocDate,  //"2024-12-05",   //----------
                "DocDueDate" => $DocDate, //"2024-12-05",  //----------
                "TaxDate" => $DocDate,  //"2024-12-05",   //----------
                "CardCode" => $CardCode,
                "CardName" => $CardName,
                "DocTotal" => $precio_plan,
                "DocCurrency" => "BS",
                "Comments" => $contrato,
                "NumAtCard" => $NumAtCard, //"CRS-PROVSP-240088",
                "U_NIT" => $U_NIT,
                "U_RAZSOC" => $CardName,
                "U_CONSULTORIO" => $U_CONSULTORIO,
                "U_CANAL" => $canal, //"C003",   //----------
                "U_ESTADOFC" => "V",
                "U_COMENTARIOS" => $contrato,   //"PPCE0001-LP-24-0001-0000088|20241205",  //----------
                "U_NumTransaccionExt" => $NumAtCard,
                "DocumentLines" => [
                    [
                        "LineNum" => 0,
                        "ItemCode" => $cod_plan, //"PPCE0001",  //----------
                        "ItemDescription" => $servicios[0]->plan,
                        "Quantity" => 1, //$servicios['cantidad'],
                        "PriceAfterVAT" => (float)$servicios[0]->precio,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 0,
                        "U_FECHAINI" => $fechaInicio,
                        "U_Contrato" => $contrato,
                        //"U_FECHAFIN" => "2025-12-11",
                        "U_FECHAFIN" => $fechaFin,
                    ],
                    [
                        "LineNum" => 1,
                        "ItemCode" => $servicios[0]->cod_servicio,
                        "ItemDescription" => $servicios[0]->servicio,
                        "Quantity" => (int)$servicios[0]->cantidad,  // 3,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,
                        "U_Contrato" => $contrato,
                        //"U_FECHAFIN" => "2025-12-11",
                        "U_FECHAFIN" => $fechaFin,
                    ],
                    [
                        "LineNum" => 2,
                        "ItemCode" => $servicios[1]->cod_servicio,
                        "ItemDescription" => $servicios[1]->servicio,
                        "Quantity" => (int)$servicios[1]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,
                        "U_Contrato" => $contrato,
                        //"U_FECHAFIN" => "2025-12-11",
                        "U_FECHAFIN" => $fechaFin,
                    ],
                    [
                        "LineNum" => 3,
                        "ItemCode" => $servicios[2]->cod_servicio,
                        "ItemDescription" => $servicios[2]->servicio,
                        "Quantity" => $servicios[2]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_Contrato" => $contrato,
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 4,
                        "ItemCode" => $servicios[3]->cod_servicio,
                        "ItemDescription" => $servicios[3]->servicio,
                        "Quantity" => $servicios[3]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 5,
                        "ItemCode" => $servicios[4]->cod_servicio,
                        "ItemDescription" => $servicios[4]->servicio,
                        "Quantity" => $servicios[4]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],

                ]
            );
        }

        if ($cod_plan == 'PPCE0146') {
            $ordenVenta = array(
                "DocType" => "dDocument_Items",
                "DocDate" => $DocDate,  //"2024-12-05",   //----------
                "DocDueDate" => $DocDate, //"2024-12-05",  //----------
                "TaxDate" => $DocDate,  //"2024-12-05",   //----------
                "CardCode" => $CardCode,
                "CardName" => $CardName,
                "DocTotal" => $precio_plan,
                "DocCurrency" => "BS",
                "Comments" => $contrato,
                "NumAtCard" => $NumAtCard, //"CRS-PROVSP-240088",
                "U_NIT" => $U_NIT,
                "U_RAZSOC" => $CardName,
                "U_CONSULTORIO" => $U_CONSULTORIO,
                "U_CANAL" => $canal, //"C003",   //----------
                "U_ESTADOFC" => "V",
                "U_COMENTARIOS" => $contrato,   //"PPCE0001-LP-24-0001-0000088|20241205",  //----------
                "U_NumTransaccionExt" => $NumAtCard,
                "DocumentLines" => [
                    [
                        "LineNum" => 0,
                        "ItemCode" => $cod_plan, //"PPCE0001",  //----------
                        "ItemDescription" => $servicios[0]->plan,
                        "Quantity" => 1, //$servicios['cantidad'],
                        "PriceAfterVAT" => (float)$servicios[0]->precio,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 0,
                        "U_FECHAINI" => $fechaInicio,
                        "U_Contrato" => $contrato,
                        //"U_FECHAFIN" => "2025-12-11",
                        "U_FECHAFIN" => $fechaFin,
                    ],
                    [
                        "LineNum" => 1,
                        "ItemCode" => $servicios[0]->cod_servicio,
                        "ItemDescription" => $servicios[0]->servicio,
                        "Quantity" => (int)$servicios[0]->cantidad,  // 3,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,
                        "U_Contrato" => $contrato,
                        //"U_FECHAFIN" => "2025-12-11",
                        "U_FECHAFIN" => $fechaFin,
                    ],
                    [
                        "LineNum" => 2,
                        "ItemCode" => $servicios[1]->cod_servicio,
                        "ItemDescription" => $servicios[1]->servicio,
                        "Quantity" => (int)$servicios[1]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,
                        "U_Contrato" => $contrato,
                        //"U_FECHAFIN" => "2025-12-11",
                        "U_FECHAFIN" => $fechaFin,
                    ],
                    [
                        "LineNum" => 3,
                        "ItemCode" => $servicios[2]->cod_servicio,
                        "ItemDescription" => $servicios[2]->servicio,
                        "Quantity" => $servicios[2]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_Contrato" => $contrato,
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 4,
                        "ItemCode" => $servicios[3]->cod_servicio,
                        "ItemDescription" => $servicios[3]->servicio,
                        "Quantity" => $servicios[3]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 5,
                        "ItemCode" => $servicios[4]->cod_servicio,
                        "ItemDescription" => $servicios[4]->servicio,
                        "Quantity" => $servicios[4]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],
                    [
                        "LineNum" => 6,
                        "ItemCode" => $servicios[5]->cod_servicio,
                        "ItemDescription" => $servicios[5]->servicio,
                        "Quantity" => $servicios[5]->cantidad,  //50.0,
                        "PriceAfterVAT" => 0,
                        "Currency" => "BS",
                        "WarehouseCode" => "LPZ-ON",
                        "TaxCode" => "IVA",
                        "U_RECETA" => 1,
                        "U_FECHAINI" => $fechaInicio,  //"2024-12-05",
                        "U_FECHAFIN" => substr($fechaFin, 0, 10), //"2025-12-05",
                    ],

                ]
            );
        }
        

        // --------------------------------------------- //
        // JE: VERIFICAR SI YA EXISTE LA ORDEN DE VENTA (OV) //
        // --------------------------------------------- //
        
        $endpointCheck = "/b1s/v1/Orders?\$filter=NumAtCard%20eq%20'" . urlencode($numPrestamo) . "'";
        $urlCheck = "{$host}:{$port}{$endpointCheck}";

        curl_setopt($ch, CURLOPT_URL, $urlCheck);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Cookie: B1SESSION={$sessionId}"
        ]);

        $responseCheck = curl_exec($ch);
        $httpCodeCheck = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $logData = [
                'error_type' => 'Error en la verificación de Orden existente',
                'curl_errno' => curl_errno($ch),
                'curl_error' => curl_error($ch),
                'http_code' => $httpCodeCheck,
                'numPrestamo' => $numPrestamo,
                'endpoint' => $urlCheck,
                'request_data' => null,
                'line'        => __LINE__,
            ];
            logSap($logData);
            
            // echo "Error al verificar orden : " . curl_error($ch) . "<br>\n";
            ChangBDErr($numPrestamo, 'ER_CURL_VER_ORD');
            continue;

            // DISTINTOS DE "A"
        }
            // Procesar la respuesta
        $responseData = json_decode($responseCheck, true);
        
        if ($httpCodeCheck >= 200 && $httpCodeCheck < 300) {
            if (!empty($responseData['value'])) {
                $responseExistente = $responseData['value'][0];
                // $estadoOV = $responseExistente['U_ESTADOFC'] ?? '';
                
                // echo "* YA EXISTE una Orden de Venta para numPrestamo: {$numPrestamo} con estado {$estadoOV}<br>\n";
                
                // if ($estadoOV === 'A') {
                //     // Crear nueva OV porque la existente está anulada
                //     $response = crearOrdenVentaSAP($ch, $sessionId, $ordenVenta, $numPrestamo);
                // } elseif ($estadoOV === 'V') {
                //     // No crear nueva OV porque ya existe una vigente
                //     $response = $responseExistente;
                // } else {
                //     // Estado desconocido, opcionalmente crear o registrar error
                //     $logData = [
                //         'error_type' => 'Estado OV desconocido',
                //         'http_code' => $httpCodeCheck,
                //         'numPrestamo' => $numPrestamo,
                //         'endpoint' => $urlCheck,
                //         'response' => $responseCheck,
                //         'linea' => __LINE__,
                //     ];
                //     logSap($logData);
                //     ChangBDErr($numPrestamo, 'ER_OV_DESC');
                //     continue;
                //     // $response = $responseExistente;
                // }
                $response = $responseExistente;
                return [
                    'success' => false,
                    'message' => 'El documento: '.$documento.' con Numero de Préstamo: '. $numPrestamo.' ya fué procesado',
                ];
            } else {
                // No existe OV, crear nueva
                //exit('Crear nueva OV??????');
                $response = crearOrdenVentaSAP($ch, $sessionId, $ordenVenta, $numPrestamo);
            }
        } else {
            // Error HTTP al verificar OV
            $logData = [
                'error_type' => 'Error HTTP al verificar orden existente',
                'http_code' => $httpCodeCheck,
                'numPrestamo' => $numPrestamo,
                'endpoint' => $urlCheck,
                'response' => $responseCheck,
                'linea' => __LINE__,
            ];
            logSap($logData);
            
            // echo "Error HTTP al verificar orden existente: {$httpCodeCheck}<br> La orden no existe\n";
            ChangBDErr($numPrestamo, 'ER_HTTP_VER_ORD');
            continue;
        }
        

        
        
        // \\-------------------------------------------------- //
        // 6. Verifica o Inserta Cabecera Certificado //
        // -------------------------------------------------- //

        $U_DOC_NUM = $response['DocNum'];
        $U_DocEntry = $response['DocEntry'];
        $U_NumAtCard = $NumAtCard;
        $U_Canal = $dataBeneficiario['canal'];
        
        // Verificar si ya existe CABECERA CERTIFICADO
        $filtrosCabecera = [
            'U_DocEntry' => $U_DocEntry,
            'U_DocType' => '17'
        ];
        $cabeceraExistente = verificarExistenciaRegistro(
            $ch, $sessionId, 'U_CAB_CERTIFICADO', 
            $filtrosCabecera, $numPrestamo
        );
        //+
        if ($cabeceraExistente) {
            // echo "* YA EXISTE Cabecera Certificado para DocEntry: {$U_DocEntry}<br>\n";
            $U_Certificado = $cabeceraExistente['Code'];
            // dep($cabeceraExistente);
        } else {
            // Crear nueva CABECERA CERTIFICADO
            //  echo "-- NO EXISTE Cabecera Certificado para DocEntry: {$U_DocEntry}<br>\n";
            $dataCabCertificado = array(
                "U_DOC_NUM" => $U_DOC_NUM,
                "U_NumAtCard" => $U_NumAtCard,
                "U_DocEntry" => $U_DocEntry,
                "U_Canal" => $U_Canal,
                "U_Tipo" => null,
                "U_FormaComercializacion" => "WS",
                "U_DocType" => "17"
            );

            // Convertir la información en formato JSON
            $cabCertificadoJson = json_encode($dataCabCertificado);

            // Construir la URL completa
            $endpoint = "/b1s/v1/U_CAB_CERTIFICADO";
            $url = "{$host}:{$port}{$endpoint}";

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $cabCertificadoJson);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                "Cookie: B1SESSION={$sessionId}",
                'Content-Length: ' . strlen($cabCertificadoJson)
            ));

            $response = curl_exec($ch);
            
            if (curl_errno($ch)) {
                // echo 'Error al insertar CAB CERTIFICADO: ' . curl_error($ch);
                $logData = [
                    'error_type'   => 'Error al insertar CAB CERTIFICADO',
                    'curl_errno'   => curl_errno($ch),
                    'curl_error'   => curl_error($ch),
                    'numPrestamo'  => $numPrestamo,
                    'line'        => __LINE__,
                ];
                logSap($logData);
                ChangBDErr($numPrestamo, 'ER_CUR_INS_CAB_CERT');
                continue;
            }
            
            // -------------------------------------------------- //
            // 6.1 Obtenemos el CODE de tabla Cabecera Certificado //
            // -------------------------------------------------- //
            $U_Certificado = obtenerCodeDespuesInsercion($ch, $sessionId, 'U_CAB_CERTIFICADO', [
                'U_DocEntry' => $U_DocEntry,
                'U_DocType' => '17'
            ], $numPrestamo);
            
            if (!$U_Certificado) {
                ChangBDErr($numPrestamo,'ER_OBT_COD_CAB_CERT');
                continue;
            }
        }
        
        // ---------------------------------------------- //
        // 7. Insertar el registro en Linea Certificado //
        // ---------------------------------------------- //
        $U_Certificado = $U_Certificado;
        $U_CardName    = $dataBeneficiario['CardName'];
        $U_CardCode    = $dataBeneficiario['CardCode'];
        $U_VatIdUnCmp  = $dataBeneficiario['Cedula'];
        $U_ItemCode    = $dataBeneficiario['cod_plan'];
        
        
        // Generar contrato
        $nuevo = generaNumContratoPPCE0125($numPrestamo);
        if ($nuevo['estado'] !== 'E') {
            $logData = [
                'error_type' => 'Error generando contrato ',
                'mensaje' => $nuevo['mensaje'] ?? 'Desconocido',
                'numPrestamo' => $numPrestamo,
                'line' => __LINE__,
            ];
            logSap($logData);
            ChangBDErr($numPrestamo, 'ER_GEN_CONTRATO');
            continue;
        }
        $U_Contrato = $nuevo['contratoLin'];

        // Verificar si ya existe LÍNEA CERTIFICADO
        $filtrosLinea = [
            'U_Certificado' => $U_Certificado,
            'U_ItemCode' => $U_ItemCode,
            'U_Contrato' => $U_Contrato
        ];
        
        // Verificar si ya existe LÍNEA CERTIFICADO
        $lineaExistente = verificarExistenciaLineaCertificado(
            $ch, $sessionId, $U_DocEntry, $U_ItemCode, $U_Contrato, $numPrestamo
        );
           //+
        if ($lineaExistente) {
            //echo "* YA EXISTE Línea Certificado para Contrato: {$U_Contrato}<br>\n";
            //print_r($lineaExistente);
            $llave_linea_certificado = $lineaExistente['U_LIN_CERTIFICADO']['Code'];
            //dep($llave_linea_certificado);
            //exit;
        } else {
            //echo "-- NO EXITE Línea Certificado \n";
            $dataLin = array(
                "U_CardCode" => $U_CardCode,
                "U_CardName" => $U_CardName,
                "U_VatIdUnCmp" => $U_VatIdUnCmp,
                "U_Certificado" => $U_Certificado,
                "U_Contrato" => $U_Contrato,
                "U_LINEA" => "0",
                "U_ItemCode" => $U_ItemCode,
                "U_FormaComercializacion" => "COLECTIVO"
            );

            $dataLinJson = json_encode($dataLin);

            // Construir la URL completa
            $endpoint = "/b1s/v1/U_LIN_CERTIFICADO";
            $url = "{$host}:{$port}{$endpoint}";

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataLinJson);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                "Cookie: B1SESSION={$sessionId}",
                'Content-Length: ' . strlen($dataLinJson)
            ));

            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                $logData = [
                    'error_type'   => 'Insert_LIN_CERTIFICADO',
                    'curl_errno'   => curl_errno($ch),
                    'curl_error'   => curl_error($ch),
                    'numPrestamo'  => $numPrestamo,
                    'line'        => __LINE__,
                ];
                logSap($logData);
                //echo 'Error al insertar LIN CERTIFICADO: ' . curl_error($ch);
                ChangBDErr($numPrestamo, 'ER_CUR_INS_LIN_CERT');
                continue;
            } else {
                //echo 'LÍNEA CERTIFICADO INSERTADA CORRECTAMENTE<br> \n';
            }
            
            // Obtener el CODE después de insertar
            $llave_linea_certificado = obtenerCodeDespuesInsercion($ch, $sessionId, 'U_LIN_CERTIFICADO', [
                'U_Certificado' => $U_Certificado,
                'U_ItemCode' => $U_ItemCode
            ], $numPrestamo);

            if (!$llave_linea_certificado) {
                ChangBDErr($numPrestamo, 'ER_OBT_COD_LIN_CERT');
                continue;
            }
        }
        
        
    // // -------------------------------------------------------------- //
    // // 9. Insertamos Titular y Beneficiarios en Cabecera Beneficiario //
    // // -------------------------------------------------------------- //
        //dep($lineaExistente);
        $DocEntry = $U_DocEntry;
        $CardCode = $dataBeneficiario['CardCode'];

        // Verificar si ya existe CABECERA BENEFICIARIO
        $filtrosBeneficiario = [
            'U_DocEntry' => $U_DocEntry,
            'U_Linea' => $llave_linea_certificado,
            'U_CardCode' => $CardCode,
            // 'U_Tipo' => '0' // 0: titular
        ];

        $beneficiarioExistente = verificarExistenciaRegistro(
            $ch, $sessionId, 'U_CAB_BENEFICIARIO', 
            $filtrosBeneficiario, $numPrestamo
        );

        if ($beneficiarioExistente) {
            //echo "* YA EXISTE Cabecera Beneficiario para CardCode: {$CardCode}<br>\n";
        } else {
            //echo "\n-- NO EXISTE Cabecera Beneficiario\n";
            $titData = array(
                "U_DocEntry" => $DocEntry,
                "U_CardCode" => $CardCode,
                "U_Tipo" => 0, // 0: titular
                "U_Linea" => $llave_linea_certificado,
                "Name" => 1
            );

            // Construir la URL completa
            $endpoint = "/b1s/v1/U_CAB_BENEFICIARIO";
            $url = "{$host}:{$port}{$endpoint}";

            $titularJson = json_encode($titData);

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $titularJson);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                "Cookie: B1SESSION={$sessionId}",
                'Content-Length: ' . strlen($titularJson)
            ));

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                $logData = [
                    'error_type'   => 'Error al insertar Titular en Cabecera Beneficiario',
                    'curl_errno'   => curl_errno($ch),
                    'curl_error'   => curl_error($ch),
                    'numPrestamo'  => $numPrestamo,
                    'line'        => __LINE__,
                ];
                logSap($logData);
                //echo 'Error al guardar Titular en tabla Cabecera Beneficiario: ' . curl_error($ch);
                ChangBDErr($numPrestamo, 'ER_CUR_INS_CAB_BEN');
                continue;
            }
        }

        // echo "OV creada correctamente para numPrestamo: " . $numPrestamo . "<br> \n";
        
        
        //$ret['estado'] = 'E';
        $mensaje = "Orden de Venta creada correctamente para Número de Préstamo {$numero_prestamo}";
        echo json_encode([
            'status' => 'ok',
            'message' => $mensaje
        ]);
        //$ret['id'] = $id_registro;
        
    } catch (Exception $e) {
        
        $logData = [
            'error_type'   => 'Error procesando cliente',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'line' => $e->getLine(),
            'memoria' => memory_get_usage()/1024/1024 . ' MB',
            'numPrestamo' => $numPrestamo ?? 'Desconocido',
            'line'        => __LINE__,
        ];
        logSap($logData);
        ChangBDErr($numPrestamo, 'ER_CLI');

        echo json_encode([
            'status' => 'error',
            'message' => 'Error al crear la OV'
        ]);
        continue;
    }
    
    // $ret['estado'] = 'E';
    ChangBDErr($numPrestamo, 'COBRADO');
    $tot_cli++;
    $elapsed = microtime(true) - $startTime;    
    logSap("numPrestamo {$numPrestamo} en " . number_format($elapsed, 3) . " segundos");

} // END FOREACH CLIENTES



// echo "CLIENTES TOTALES: " . $tot_cli;
// return $ret;
//exit();
//---------------------------------------------

//------------------------------------------------------------------------------------------------------------------------
//----------------------------------------------------------------------------------------------------------------FUNCIONES

function connect_DB()
{
    $mysqli = new mysqli("localhost", "root", "", "addoninnova");
    //$mysqli = new mysqli("20.242.113.194", "innovasa_AddOnCJN", "Add0n#CJN$2024", "innovasa_AddOnCJN");
    if ($mysqli->connect_error) {
        // die("Fallo al conectar a MySQL: " . $mysqli->connect_error);
         $logData = [
            'error_type'   => 'Fallo al conectar a MySQL',
            'error' =>  $mysqli->connect_error,
            'line'        => __LINE__,
        ];
        logSap($logData);
        //continue;
        
    }
	$mysqli->set_charset('utf8'); // 👈 clave para leer bien Ñ, tildes, etc. MALY 18/07/2025
    return $mysqli;
}
//--------------------------------------------------------------
//EN CASO DE ERROR EN CREACION O BUSQUEDA DE USUARIO
//--------------------------------------------------------------

// function ChangBDErr($numPrestamo, $err)
// {
//     if($err == null){
//         $err = 'ER';
//     }
//     try {
//         $connectDB = connect_DB();
//         $fechaActual = date('Y-m-d H:i:s');
//         $sql = "UPDATE temps_vit SET procesado = $err, updated_at = ? WHERE numPrestamo = ?";
//         $stmt = $connectDB->prepare($sql);
//         $stmt->bind_param("ss", $fechaActual, $numPrestamo);
//         $stmt->execute();
//     } catch (Exception $e) {
//         error_log("Error actualizando a ER: " . $e->getMessage());
//     }
// }


function ChangBDErr($numPrestamo, $err)
{
    if ($err === null || $err === '') {
        $err = 'ER';
    }

    try {
        $connectDB = connect_DB();
        $fechaActual = date('Y-m-d H:i:s');

        if ($err === 'COBRADO') {
            $sql = "UPDATE temps_vit SET cobranza = ?, updated_at = ? WHERE numPrestamo = ?";
            $stmt = $connectDB->prepare($sql);
            $stmt->bind_param("sss", $err, $fechaActual, $numPrestamo);
        } else {
            $sql = "UPDATE temps_vit SET procesado = ?, updated_at = ? WHERE numPrestamo = ?";
            $stmt = $connectDB->prepare($sql);
            $stmt->bind_param("sss", $err, $fechaActual, $numPrestamo);
        }

        $stmt->execute();
        $stmt->close();
        $connectDB->close();

    } catch (Exception $e) {
        error_log("Error actualizando estado '{$err}' para préstamo {$numPrestamo}: " . $e->getMessage());
    }
}

// ---------------------------------------------
// Función para leer la tabla vitalicia
// ---------------------------------------------
function readTablaVitalicia($numero_prestamo)
{
    $connectDB = connect_DB();
    $clientes = [];
    

    $query = "SELECT * FROM addoninnova.temps_vit t JOIN addoninnova.clientes_vit c ON t.id_contratante = c.id 
                 WHERE t.numPrestamo = '". $numero_prestamo . "'";
    $resultado = $connectDB->query($query);

    if ($resultado) {
        while ($row = $resultado->fetch_object()) {
            $clientes[] = $row;
        }
    }
    return $clientes;
}

// ---------------------------------------------
// Función para formatear los datos del beneficiario
// ---------------------------------------------
function formatDataBenef($data)
{
    date_default_timezone_set('America/La_Paz');

    // Generar componentes del CardCode
    $fechaNac = substr($data->fecha_nacimiento, 2, 2)  // Año (YY)
        . substr($data->fecha_nacimiento, 5, 2)  // Mes (MM)
        . substr($data->fecha_nacimiento, 8, 2); // Día (DD)

    // Iniciales: Primera letra de cada nombre/apellido
    $ApPat = !empty($data->ap_paterno) ? substr($data->ap_paterno, 0, 1) : '';
    $ApMat = !empty($data->ap_materno) ? substr($data->ap_materno, 0, 1) : '';
    $PriNomb = !empty($data->nombre1) ? substr($data->nombre1, 0, 1) : '';
    $SecNomb = !empty($data->nombre2) ? substr($data->nombre2, 0, 1) : '';

    // CardCode base (será modificado si hay duplicados)
    $CardCode = $fechaNac . "-" . $ApPat . $ApMat . $PriNomb . $SecNomb;

    // Mapeo tipo documento (del segundo código)
    switch ($data->tipo_documento) {
        case 'C':
            $U_TIPDOC = 'CI';
            $U_DOCTYPE = 'Carnet de Identidad';
            break;
        case 'E':
            $U_TIPDOC = 'CEX';
            $U_DOCTYPE = 'Carnet de Extranjero';
            break;
        case 'P':
            $U_TIPDOC = 'PAS';
            $U_DOCTYPE = 'Pasaporte';
            break;
        default:
            $U_TIPDOC = 'OTRO';
            $U_DOCTYPE = 'OTRO';
            break;
    }

    // Retornar TODOS los campos necesarios del segundo código
    return [
        // Campos para contacto
        'PriNombre' => $data->nombre1,
        'SecNombre' => $data->nombre2 ?? '',
        'ApPaterno' => $data->ap_paterno,
        'ApMaterno' => $data->ap_materno ?? '',
        'CardCode' => $CardCode,
		'CardName' => implode(' ', array_filter([
			$data->nombre1 ?? '',
			$data->nombre2 ?? '',
			$data->ap_paterno ?? '',
			$data->ap_materno ?? ''
		])),

        //'CardName' => trim($data->nombre1 . ' ' . $data->nombre2 . ' ' . $data->ap_paterno . ' ' . $data->ap_materno), MALY 18/07/2025
        'CardType' => 'C', //$data->tipo_documento,  // CardType fijo como Cliente (del primer código)
        'Currency' => "Bs",
        'Cellular' => $data->telefono,
        'EmailAddr' => $data->correo ?? '',
        'Cedula' => $data->num_documento,
        'Extension' => $data->extension ?? '',
        'Expedicion' => $data->expedido ?? '',
        'FechaNac' => $data->fecha_nacimiento,
        'Genero' => $data->genero ?? '',
        'GroupCode' => "100",
        'U_TIPDOC' => $U_TIPDOC,
        'U_DOCTYPE' => $U_DOCTYPE,
        'U_CONSULTORIO' => $data->agencia_venta ?? '',//<<
        'U_FechaNac' => $data->fecha_nacimiento,
        'NumAtCard' => $data->numPrestamo,
        'cod_plan' => $data->codigo_plan_hijo,
        'canal' => $data->codigo_canal,
        'numTransaccion' => $data->codigo_tra,
        'fechaInicio' => $data->fecha_cobranzas,
        'num_ope' => $data->codigo_ope,
        'telefono'       => $data->telefono,
    ];
}


function obtenemosServicios($canal, $cod_plan, $numPrestamo)
{
    $connectDB = connect_DB();

    $stmt = $connectDB->prepare("SELECT * FROM planes_ov WHERE cod_plan = ? AND canal = ?");
    
    if (!$stmt) {
        $error = [
            'error_type' => 'Database_Prepare',
            'error_message' => "Error en la preparación de la consulta: " . $connectDB->error,
            'cod_plan' => $cod_plan,
            'canal' => $canal,
            'numPrestamo' => $numPrestamo
        ];
        logSap($error);
        //continue; 
        //die("Error en la preparación de la consulta");
    }
    
    $stmt->bind_param("ss", $cod_plan, $canal);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    $datos = [];
    while ($fila = $resultado->fetch_object()) {
        $datos[] = $fila; // Como en Laravel -> objetos
    }
    
    if (empty($datos)) {
        $error = [
            'error_type' => 'Services_Not_Found',
            'error_message' => "No se encontraron servicios para el plan $cod_plan y canal $canal",
            'cod_plan' => $cod_plan,
            'canal' => $canal,
            'numPrestamo' => $numPrestamo,
            'query' => "SELECT * FROM planes_ov WHERE cod_plan = '$cod_plan' AND canal = '$canal'"
        ];
        logSap($error);
    }
   
    return $datos;
}

function generaNumContrato($canal, $cod_plan, $numPrestamo)
{
    $connectDB = connect_DB();

    // echo "CANAL: " . $canal . "<br> \n";
    // echo "PLAN: " . $cod_plan . "<br> \n";

    // Buscar contrato actual
    $stmt = $connectDB->prepare("SELECT valor_actual, contrato FROM contratos WHERE contrato_sm = ? AND id_canal = ?");
    if (!$stmt) {
        // die("Error preparando la consulta: " . $connectDB->error);
         $error = [
            'error_type' => 'Error preparando la consulta',
            'error_message' => "Error en la preparación de la consulta: " . $connectDB->error,
            'cod_plan' => $cod_plan,
            'canal' => $canal,
            'numPrestamo' => $numPrestamo
        ];
        logSap($error);
        ChangBDErr($numPrestamo, 'ER_DB_PREP_CONTRATO');
        //continue;
    }

    $stmt->bind_param("ss", $cod_plan, $canal);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $rowcount = $resultado->num_rows;
    //echo "CONTRATO ROW COUNT: " . $rowcount . "<br> \n";

    if ($rowcount > 0) {
        $row = $resultado->fetch_object();

        $valor = (int)$row->valor_actual;
        // echo "VAL ANT: " . $valor . "<br> \n";
        $valor_actual = $valor + 1;
        // echo "VAL NEW: " . $valor_actual . "<br> \n";

        $valor_actual_str = str_pad($valor_actual, 7, "0", STR_PAD_LEFT);
        $contrato = $row->contrato . "-" . $valor_actual_str;

        // Actualizar valor_actual en la tabla contratos
        $updateStmt = $connectDB->prepare("UPDATE contratos SET valor_actual = ? WHERE contrato_sm = ? AND id_canal = ?");
        $updateStmt->bind_param("iss", $valor_actual, $cod_plan, $canal);
        $updateStmt->execute();

        if ($updateStmt->affected_rows > 0) {
            $ans['estado'] = 'E';
            $ans['contrato'] = $contrato;
        } else {
            $ans['estado'] = 'X';
            $ans['mensaje'] = "Error al actualizar tabla CONTRATOS";
             $error = [
                'error_type' => 'Error al actualizar tabla CONTRATOS',
                'error_message' => $connectDB->error,
                'numPrestamo' => $numPrestamo,
                'line' => __LINE__,
            ];
            logSap($error);
            ChangBDErr($numPrestamo, 'ER_DB_UPD_CONTRATO');
            //continue;
        }

        $updateStmt->close();
    } else {
        $ans['estado'] = 'X';
        $ans['mensaje'] = "Error al buscar PLAN en tabla CONTRATOS";
        ChangBDErr($numPrestamo, 'ER_DB_NO_PLAN_CONT');
        $error = [
                'error_type' => 'Error al buscar PLAN en tabla CONTRATOS',
                'error_message' => $connectDB->error,
                'numPrestamo' => $numPrestamo,
                'line' => __LINE__,
            ];
            logSap($error);
        //continue;
    }

    return $ans;
}

//PARA LINEA CERTIFICADO
function generaNumContratoPPCE0125($numPrestamo) 
{
    // Conectar a la base de datos
    $connectDB = connect_DB();
    $cod_plan = 'PPCE0125'; // Código de plan fijo para este caso

    //echo "PLAN: " . $cod_plan . "<br> \n";

    // 1. Buscar el contrato actual en la base de datos
    $stmt = $connectDB->prepare("SELECT valor_actual, contrato FROM contratos WHERE contrato_sm = ?");
    if (!$stmt) {
        echo("Error preparando la consulta: " . $connectDB->error);
        $error = [
            'error_type' => 'Error preparando la consulta',
            'error_message' => " " . $connectDB->error,
            'cod_plan' => $cod_plan,
            'numPrestamo' => $numPrestamo,
            'line' => __LINE__,
        ];
        logSap($error);
        
        ChangBDErr($numPrestamo, 'ER_PPCE0125');
        //continue;//die();
    }

    $stmt->bind_param("s", $cod_plan);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $rowcount = $resultado->num_rows;
    //echo "REGISTROS ENCONTRADOS: " . $rowcount . "<br> \n";

    // 2. Procesar el resultado
    if ($rowcount > 0) {
        $row = $resultado->fetch_object();

        // Obtener y aumentar el valor actual
        $valor = (int)$row->valor_actual;
        //echo "VALOR ACTUAL: " . $valor . "<br> \n";
        $valor_actual = $valor + 1;
        //echo "NUEVO VALOR: " . $valor_actual . "<br> \n";

        // Formatear el número de contrato (aquí cambiamos el nombre de la variable)
        $valor_actual_str = str_pad($valor_actual, 7, "0", STR_PAD_LEFT);
        $contratoLin = $row->contrato . "-" . $valor_actual_str; // Cambiado de $contrato a $contratoLin

        // 3. Actualizar el valor en la base de datos
        $updateStmt = $connectDB->prepare("UPDATE contratos SET valor_actual = ? WHERE contrato_sm = ?");
        $updateStmt->bind_param("is", $valor_actual, $cod_plan);
        $updateStmt->execute();

        if ($updateStmt->affected_rows > 0) {
            $ans['estado'] = 'E'; // Éxito
            $ans['contratoLin'] = $contratoLin; // Usamos la nueva variable
        } else {
            $ans['estado'] = 'X'; // Error
            $ans['mensaje'] = "Error al actualizar tabla CONTRATOS";
            ChangBDErr($numPrestamo, 'ER_UPD_PPCE0125');
            $error = [
                'error_type' => 'Error al actualizar tabla CONTRATOS',
                'numPrestamo' => $numPrestamo,
                'line' => __LINE__,
            ];
            logSap($error);
        }

        $updateStmt->close();
    } else {
        $ans['estado'] = 'X'; // Error
        $ans['mensaje'] = "Error: No se encontró el PLAN en la tabla CONTRATOS";
        $error = [
            'error_type' => 'No se encontró el PLAN en la tabla CONTRATOS',
            'error_message' => $connectDB->error,
            'numPrestamo' => $numPrestamo,
            'line' => __LINE__,
        ];
        logSap($error);
        ChangBDErr($numPrestamo, 'ER_NO_PPCE0125');
        // die();
    }

    return $ans;
}

function obtenemosDatosCanal($canal)
{
    $connectDB = connect_DB();

    $stmt = $connectDB->prepare("SELECT * FROM canal WHERE id_canal = ?");
    if (!$stmt) {
        // die("Error preparando consulta: " . $connectDB->error);
        $error = [
            'error_type' => 'Error preparando consulta:',
            'error_message' => $connectDB->error,
            'canal' => $canal,
            'line' => __LINE__,
        ];
        logSap($error);
    }

    $stmt->bind_param("s", $canal);
    $stmt->execute();

    $resultado = $stmt->get_result();

    $datos = [];
    while ($fila = $resultado->fetch_object()) {
        $datos[] = $fila;
    }
    return $datos;
}


function logSap($logData) {
    date_default_timezone_set('America/La_Paz');
    // Configuración del log
    $logDir = __DIR__ . '/logs_test';
    $logFile = $logDir . '/log_validado_test.log';

    // Crear directorio si no existe
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }

    // Formatear la fecha
    //$date = date('2025-09-04 16:24:00');
    $date = date('Y-m-d H:i:s');

    // Obtener IP del servidor
    $logMessage ='';
    // solo imprime la IP por cada iteracion
    if (is_array($logData)){
        $serverIp = $_SERVER['SERVER_ADDR'] ?? '127.0.0.1';
        $logMessage = "[{$date}] [Server: {$serverIp}]" . PHP_EOL;
    } 

    // Agregar dinámicamente los campos disponibles
    $campos = [
        'error_type'   => 'Error Type',
        'curl_errno'   => 'cURL Error Code',
        'curl_error'   => 'cURL Error Message',
        'http_code'    => 'HTTP Code',
        'numPrestamo'  => 'Prestamo ID',
        'endpoint'     => 'Endpoint',
    ];

    foreach ($campos as $key => $label) {
        if (!empty($logData[$key])) {
            $logMessage .= "{$label}: {$logData[$key]}" . PHP_EOL;
        }
    }

    // Agregar request_data si existe
    if (!empty($logData['request_data'])) {
        $logMessage .= "Request Data:" . PHP_EOL;
        $logMessage .= json_encode($logData['request_data'], JSON_PRETTY_PRINT) . PHP_EOL;
    }

    // Agregar response si existe
    if (!empty($logData['response'])) {
        $logMessage .= "Response:" . PHP_EOL;
        $logMessage .= json_encode($logData['response'], JSON_PRETTY_PRINT) . PHP_EOL;
    }

    // Si quieres registrar todo el array como respaldo
    // $logMessage .= PHP_EOL;
    $logMessage .= json_encode($logData, JSON_PRETTY_PRINT) . PHP_EOL;

   
    if (is_array($logData)){
        $logMessage .= PHP_EOL;
    } else{
        $logMessage .= str_repeat("-", 80) . PHP_EOL ;
    }

    // Escribir en el archivo de log
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}


/**
 * Crea una Orden de Venta en SAP B1 y retorna la respuesta completa
 * @param CurlHandle|resource $ch         Recurso cURL (compatible con PHP 7 y 8)
 * @param resource $ch Recurso cURL inicializado
 * @param string $sessionId ID de sesión de SAP
 * @param array $ordenVenta Datos de la orden en formato array
 * @param string $numPrestamo Número de préstamo asociado
 * @return array Array con:
 *               - success: boolean indicando éxito
 *               - response: array con respuesta completa de SAP
 *               - DocNum: número de documento (si éxito)
 *               - DocEntry: entrada de documento (si éxito)
 *               - error: mensaje de error (si fallo)
 */
function crearOrdenVentaSAP($ch, $sessionId, $ordenVenta, $numPrestamo) {
    // Configuración de endpoint
    $host = "https://52.177.52.183";
    $port = 50000;
    $endpoint = "/b1s/v1/Orders";
    $url = "{$host}:{$port}{$endpoint}";

    // Configurar solicitud cURL
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($ordenVenta),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            "Cookie: B1SESSION={$sessionId}",
            'Content-Length: ' . strlen(json_encode($ordenVenta))
        ]
    ]);
    
    // Ejecutar llamada a API
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    // Manejo de errores cURL
    if (curl_errno($ch)) {
        $logData = [
            'error_type' => 'Error de conexión con SAP',
            'curl_errno' => curl_errno($ch),
            'curl_error' => curl_error($ch),
            'http_code' => $httpCode,
            'numPrestamo' => $numPrestamo,
            'endpoint' => $url,
            'orden de venta'=>$ordenVenta,
            'line'        => __LINE__,
        ];
        
        logSap($logData);
        ChangBDErr($numPrestamo, 'ER_CONEX_SAP');
        
        return [
            'success' => false,
        ];
    }
    
    // Verificar respuesta vacía
    if (empty($response)) {
        $logData = [
            'error_type' => 'Respuesta vacía de SAP',
            'http_code' => $httpCode,
            'numPrestamo' => $numPrestamo,
            'endpoint' => $url,
            'JSON'=>$ordenVenta,
            'line'        => __LINE__,
        ];
        
        logSap($logData);
        ChangBDErr($numPrestamo, 'ER_RES_VACIA');
        
        return [
            'success' => false,
        ];
    }
    
    // Decodificar respuesta
    $responseData = json_decode($response, true);
    
    // Manejar errores HTTP
    if ($httpCode < 200 || $httpCode >= 300) {
        $logData = [
            'error_type' => 'Error en servicio SAP (código HTTP)',
            'http_code' => $httpCode,
            'numPrestamo' => $numPrestamo,
            'endpoint' => $url,
            'response' => $responseData,
            'JSON'=>$ordenVenta,
            'line'        => __LINE__,
        ];
        
        logSap($logData);
        ChangBDErr($numPrestamo, 'ER_HTTP_SAP');
        
        return [
            'success' => false,
        ];
    }
    
    // Verificar estructura mínima de respuesta
    if (!isset($responseData['DocNum'], $responseData['DocEntry'])) {
        $logData = [
            'error_type' => 'Estructura de datos incorrecta',
            'http_code' => $httpCode,
            'numPrestamo' => $numPrestamo,
            'endpoint' => $url,
            'response' => $responseData,
            'JSON'=>$ordenVenta,
            'line'        => __LINE__,
        ];
        
        logSap($logData);
        ChangBDErr($numPrestamo, 'ER_DATA_SAP');
        
        return [
            'success' => false,
        ];
    }
    
    // Éxito - retornar todos los datos relevantes
    return [
        'success' => true,
        'response' => $responseData,
        'DocNum' => $responseData['DocNum'],
        'DocEntry' => $responseData['DocEntry']
    ];
}

    /**
     * Verifica si un registro ya existe en una tabla SAP
     * @param string $sessionId ID de sesión
     * @param string $tabla Nombre de la tabla SAP
     * @param array $filtros Filtros para la consulta
     * @param string $numPrestamo Número de préstamo para logging
     * @return array|false Datos del registro existente o false si no existe
     * @param mixed $ch Conexión cURL (resource en PHP 7.4, CurlHandle en PHP 8+)
     */
    function verificarExistenciaRegistro($ch, $sessionId, $tabla, $filtros, $numPrestamo) {
        $host = "https://52.177.52.183";
        $port = 50000;
        
        // Construir filtro OData
        $filterParts = [];
        

        foreach ($filtros as $campo => $valor) {
            // Escapar comillas simples correctamente para OData
            $valorEscapado = str_replace("'", "''", $valor);
            $filterParts[] = "{$campo} eq '{$valorEscapado}'";
        }
        $filter = implode(' and ', $filterParts);
        
        $endpoint = "/b1s/v1/{$tabla}?\$filter=" . rawurlencode($filter);
        $url = "{$host}:{$port}{$endpoint}";

        // if($tabla == 'U_CAB_BENEFICIARIO'){
        //     dep($filtros);
        //     dep($url);//exit;
        // }

        
        // Configurar cURL (el error de Intelephense se puede ignorar en PHP 7.4)
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Cookie: B1SESSION={$sessionId}"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $logData = [
                'error_type' => "{$tabla}_Check",
                'curl_errno' => curl_errno($ch),
                'curl_error' => curl_error($ch),
                'http_code' => $httpCode,
                'numPrestamo' => $numPrestamo,
                'endpoint' => $url,
                'line' => __LINE__,
            ];
            logSap($logData);
            return false;
        }
        
        if ($httpCode >= 200 && $httpCode < 300) {
            $responseData = json_decode($response, true);
            return !empty($responseData['value']) ? $responseData['value'][0] : false;
        }
        
        return false;
    }

    function verificarExistenciaLineaCertificado($ch, $sessionId, $U_DocEntry, $U_ItemCode, $numPrestamo) {
        $host = "https://52.177.52.183";
        $port = 50000;

        // Construir el filtro con comillas para U_DocEntry (es string)
        $filter = "U_CAB_CERTIFICADO/Code eq U_LIN_CERTIFICADO/U_Certificado";
        $filter .= " and U_CAB_CERTIFICADO/U_DocEntry eq '" . $U_DocEntry . "'"; // COMILLAS AÑADIDAS
        $filter .= " and U_LIN_CERTIFICADO/U_ItemCode eq '" . $U_ItemCode . "'";

        // Construir el endpoint SIN urlencode en el expand (usa la sintaxis literal)
        $endpoint = "/b1s/v1/\$crossjoin(U_CAB_CERTIFICADO,U_LIN_CERTIFICADO)";
        $endpoint .= "?\$expand=U_CAB_CERTIFICADO(\$select=Code),U_LIN_CERTIFICADO(\$select=Code,U_Certificado,U_Contrato,U_ItemCode)";
        $endpoint .= "&\$filter=" . urlencode($filter); // Solo encodeamos el filter

        $url = "{$host}:{$port}{$endpoint}";
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Cookie: B1SESSION={$sessionId}"
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // dep($url); // Descomenta para debug

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $logData = [
                'error_type' => 'CURL_Error',
                'curl_error' => curl_error($ch),
                'numPrestamo' => $numPrestamo,
                'endpoint' => $url,
                'line' => __LINE__,
            ];
            logSap($logData);
            return false;
        }
        
        if ($httpCode < 200 || $httpCode >= 300) {
            $logData = [
                'error_type' => 'LIN_CERTIFICADO_Query_Error',
                'http_code' => $httpCode,
                'response_body' => $response,
                'numPrestamo' => $numPrestamo,
                'endpoint' => $url,
                'line' => __LINE__,
            ];
            logSap($logData);
            return false;
        }

        $responseData = json_decode($response, true);
        return !empty($responseData['value']) ? $responseData['value'][0] : false;

    }


    /**
     * Obtiene el CODE de un registro después de insertarlo
     */
    function obtenerCodeDespuesInsercion($ch, $sessionId, $tabla, $filtros, $numPrestamo) {
        $intentos = 0;
        $maxIntentos = 5;
        
        while ($intentos < $maxIntentos) {
            sleep(1); // Esperar 1 segundo para que SAP procese la inserción
            
            $registro = verificarExistenciaRegistro($ch, $sessionId, $tabla, $filtros, $numPrestamo);
            
            if ($registro && isset($registro['Code'])) {
                return $registro['Code'];
            }
            
            $intentos++;
        }
        
        // Log de error si no se encuentra el registro después de insertar
        $logData = [
            'error_type' => "Error obteniendo CODE de {$tabla}",
            'numPrestamo' => $numPrestamo,
            'filtros' => $filtros,
            'line' => __LINE__,
        ];
        logSap($logData);
        
        return null;
    }
?>



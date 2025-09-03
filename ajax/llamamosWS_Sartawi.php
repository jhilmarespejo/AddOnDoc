<?php
//require_once "/var/www/html/AddOnInnova/modelos/Varios.php";
require_once "../modelos/Varios.php";


function llamamosWS_Sartawi($data){

    $varios = new Varios();

    //$url = 'http://104.209.250.175/wsqa/api/TransfDatosCliente';

    $url = 'http://104.209.250.175/wstest/api/TransfDatosCliente';

    date_default_timezone_set('America/La_Paz');
    $fecha_facturacion = date('YmdHis');
    //$fecha_inicio = date('Ymd');

    //echo "FEC NAC1: " . $data['fecha_nacimiento'] . "<br>";
    $fecha_nacimiento = substr($data['fecha_nacimiento'],0,4).substr($data['fecha_nacimiento'],5,2).substr($data['fecha_nacimiento'],8,2);
    //echo "FEC NAC2: " . $fecha_nacimiento . "<br>";
    //die();

    //-------------------------------------------//
    // Buscamos al usuario para el envío del WS  //
    //-------------------------------------------//
    $codAgencia = $data['codigo_agencia'];
    $res = $varios->buscaUsuarioWS($codAgencia);

    $usuarioWS = $res['usuario'];
    $claveWS   = $res['clave'];
    //dep($res);

    //die();

    $user = $usuarioWS;
    $psw  = $claveWS;


    // Datos del cliente
    $codigoCliente = $data['cod_cli'];
    $operacionId = $data['cod_ope'];
    $numTransaccion = $data['cod_tra'];

    $tipoDoc = $data['tipo_documento'];
    //$tipoDoc = 'C';

    $numDoc = $data['cedula'];
    $extensionDoc = $data['extension'];

    $expedidoDoc = $data['expedido'];
    //$expedidoDoc = 'CB';

    $apPaterno = $data['ap_paterno'];
    $apMaterno = $data['ap_materno'];
    $nombres = $data['nombres'];
    $razonSocial = $data['razon_social'];
    $genero = $data['genero'];
    $telefono = $data['telefono'];
    $email = "";
    $direccion = "N-A";
    $fecNacimiento = $fecha_nacimiento;
    $cuidad = "COCHABAMBA";
    $pais = "BOLIVIA";
    $ocupacion = "";
    $indicador = "D";

    $planElegido = $data['codigo_plan'];
    //$planElegido = "PPCE0038";

    $monto = $data['precio'];
    $fecha = $fecha_facturacion;

    $numeroPago = 0;
    $codAgencia = $data['codigo_agencia'];
    $codigoAsesor = "";
    $modalidad = "E";

    $canal = $data['canal'];
    //$canal = 'C006';

    $fechaInicio = $data['fecha_inicio'];

    // Datos del beneficiario
    $tipoBen = "1";
    $tipoDocBen = $data['tipo_documento'];
    $tipoDocBen = 'C';
    $numDocBen = $data['cedula'];
    $extensionBen = $data['extension'];
    $expedidoBen = $data['expedido'];
    $apellidoPaternoBen = $data['ap_paterno'];
    $apellidoMaternoBen = $data['ap_materno'];
    $nombresBen = $data['nombres'];
    $fechaNacimientoBen = $fecha_nacimiento;
    $generoBen = $data['genero'];
    $telefonoBen = $data['telefono'];
    $emailBen = "";
    $direccionBen = "N-A";
    $ciudadBen = "COCHABAMBA";
    $paisBen = "BOLIVIA";
    $parentescoBen = "Titular";
    $docIdentidadTitular = 0;

    // Construir el array $cliente
    $cliente = array(
        "user" => $user,
        "psw" => $psw,
        "codigoCliente" => $codigoCliente,
        "operacionId" => $operacionId,
        "numTransaccion" => $numTransaccion,
        "tipoDoc" => $tipoDoc,
        "numDoc" => $numDoc,
        "extensionDoc" => $extensionDoc,
        "expedidoDoc" => $expedidoDoc,
        "apPaterno" => $apPaterno,
        "apMaterno" => $apMaterno,
        "nombres" => $nombres,
        "razonSocial" => $razonSocial,
        "genero" => $genero,
        "telefono" => $telefono,
        "email" => $email,
        "direccion" => $direccion,
        "fecNacimiento" => $fecNacimiento,
        "cuidad" => $cuidad,
        "pais" => $pais,
        "ocupacion" => "",  //$ocupacion,
        "indicador" => $indicador,
        "planElegido" => $planElegido,
        "monto" => $monto,
        "fecha" => $fecha,
        "numeroPago" => $numeroPago,
        "codAgencia" => $codAgencia,
        "codigoAsesor" => $codigoAsesor,
        "modalidad" => $modalidad,
        "fechaInicio" => $fechaInicio,
        "canal" => $canal, // Agregado manualmente, ya que no está en tus datos originales
        "beneficiarios" => array(
            array(
                "tipoBen" => $tipoBen,
                "tipoDocBen" => $tipoDocBen,
                "numDocBen" => $numDocBen,
                "extensionBen" => $extensionBen,
                "expedidoBen" => $expedidoBen,
                "apellidoPaternoBen" => $apellidoPaternoBen,
                "apellidoMaternoBen" => $apellidoMaternoBen,
                "nombresBen" => $nombresBen,
                "fechaNacimientoBen" => $fechaNacimientoBen,
                "generoBen" => $generoBen,
                "telefonoBen" => $telefonoBen,
                "emailBen" => $emailBen,
                "direccionBen" => $direccionBen,
                "ciudadBen" => $ciudadBen,
                "paisBen" => $paisBen,
                "parentescoBen" => $parentescoBen,
                "docIdentidadTitular" => $docIdentidadTitular,
            )
        )
    );


    //dep($cliente);
    //die();


    // Encoded as a json string
    $data_string = json_encode($cliente);

    $ch=curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_POST  => true,
        CURLOPT_POSTFIELDS  => $data_string,
        CURLOPT_HEADER  => true,
        CURLOPT_HTTPHEADER  => array('Content-Type:application/json', 'Content-Length: ' . strlen($data_string)),
        CURLOPT_RETURNTRANSFER  => true
        )
    );


    $ret_val = array();

    // Aqui devuelvo el resultado
    $result = curl_exec($ch);

    if ($result === false) {
        //echo 'Error cURL: ' . curl_error($ch);
        $estado = curl_error($ch);
        $ret_val['status_fact']  = $estado;
        $ret_val['mensaje_fact'] = 'Error al ejecutar la funcion: "curl_error($ch)"';
    } else {
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $headerSize);
        $body = substr($result, $headerSize);
        $json_response = json_decode($result, true);

        //echo 'Código HTTP: ' . $httpCode . PHP_EOL;
        //echo 'Encabezado de respuesta: ' . $header . PHP_EOL;
        //echo 'Cuerpo de respuesta: ' . $body . PHP_EOL;
        $responseData = json_decode($body, true); // true para obtener un array asociativo
        if ($responseData !== null) {
            // Procesar $responseData según tus necesidades
            $estado = $responseData['Estado'];
            $mensaje = isset($responseData['Mensaje']) ? $responseData['Mensaje']: "";
            //echo 'Estado: ' . $estado . PHP_EOL;
            //echo 'Mensaje: ' . $mensaje . PHP_EOL;        
        } else {
            //echo 'Error al decodificar la respuesta JSON.';
        }

        $ret_val['status_fact']  = $estado;
        $ret_val['mensaje_fact'] = $mensaje;

    }

    curl_close($ch);

    //$ret_val['status_fact']  = 'OK';
    //$ret_val['mensaje_fact'] = 'BIEN!!';

    return $ret_val;



}


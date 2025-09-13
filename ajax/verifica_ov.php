
<?php
$numPrestamo = $_POST['numeroPrestamo'];

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
// set_time_limit(400); // 300 segundos = 5 minutos

$response = curl_exec($ch);

$authResponse = json_decode($response);
$sessionId = $authResponse->SessionId ?? null;

header('Content-Type: application/json');
if (!$sessionId) {
    curl_close($ch);
    echo json_encode([
        'success' => false,
        'message' => 'Fallo en SAP, intente en unos minutos'
    ]);
    exit;
} else {
        // JE: VERIFICAR SI YA EXISTE LA ORDEN DE VENTA (OV) //
        
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
               // Procesar la respuesta
        $responseData = json_decode($responseCheck, true);
        
        if ($httpCodeCheck >= 200 && $httpCodeCheck < 300 && $responseData) {
            // var_dump($responseData);
            if (!empty($responseData['value'])) {
                // exit('la ov si existe');
                 echo json_encode([
                    'success' => false,
                    'message' => 'El documento con Número de Préstamo: ' . $numPrestamo . ' ya esta registrado'
                ]);
            } else {
                // exit('la ov no existe');
                 echo json_encode([
                    'success' => true,
                    'message' => 'No existe OV para el préstamo, puede continuar'
                ]);
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error HTTP al verificar OV'
            ]);
        }
        exit;
}



 
        



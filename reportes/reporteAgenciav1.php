<?php

require '../config/global.php';
require '../Classes/PHPExcel.php';

// Cargar PHPExcel
/*
require 'vendor/autoload.php';
use PHPExcel_IOFactory;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
*/

$con = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
mysqli_set_charset($con, 'utf8');
// Verificar la conexión
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

//if (isset($_POST['exportar'])) {
    

    /* $f_ini = $_POST['fecha_inicio'];
    $f_fin = $_POST['fecha_fin'];
    $codigo_agencia = $_POST['codigo_agencia'];
    $nombre_supervisor = $_POST['nombre_supervisor'];
    $nombre_agencia = $_POST['nombre_agencia'];
    */
    $f_ini = '2024-03-11'; 
    $f_fin = '2024-03-11';
    $codigo_agencia = 'PTS-SR';
    $nombre_supervisor = 'Juan Perez';
    $Nombre_agencia = 'San Roque';

    // Crear un nuevo objeto PHPExcel
    $objPHPExcel = new PHPExcel();

    // Configurar propiedades del documento
    $objPHPExcel->getProperties()->setCreator("Nombre del Creador")
                                 ->setLastModifiedBy("Nombre del Modificador")
                                 ->setTitle("Título del Documento")
                                 ->setSubject("Asunto del Documento")
                                 ->setDescription("Descripción del Documento")
                                 ->setKeywords("phpexcel")
                                 ->setCategory("Test result file");

    // Establecer el título
    $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', "REPORTE CONSOLIDADO POR AGENCIA")
                ->mergeCells("A1:Q1");

    // Establecer estilos
    $objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->getFont()->setSize(16)->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    // Establecer anchos de columnas
    // $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
    // Establecer más anchos aquí...

    // Obtener la fecha actual
    date_default_timezone_set('America/La_Paz');
    $fecha = date('Y-m-d H:i:s');

    // Agregar información
    $objPHPExcel->getActiveSheet()->setCellValue('A2', 'Usuario: ' . $nombre_supervisor);
    $objPHPExcel->getActiveSheet()->setCellValue('A3', 'Agencia: ' . $nombre_agencia);
    $objPHPExcel->getActiveSheet()->setCellValue('O2', 'Fecha de Inicio: ' . $f_ini);
    $objPHPExcel->getActiveSheet()->setCellValue('O3', 'Fecha de Fin: ' . $f_fin);
    $objPHPExcel->getActiveSheet()->setCellValue('O4', 'Fecha & Hora Impresión: ' . $fecha);

    // Encabezados de las columnas
    $objPHPExcel->getActiveSheet()->setCellValue('A5', "ID")
                                  ->setCellValue('B5', "Cod Ag.")
                                  ->setCellValue('C5', "Agencia")
				  ->setCellValue('D5', "Ciudad")
                                  // Agregar más encabezados aquí...
                                  ->setCellValue('Q5', "Estado");

    // Establecer estilos para los encabezados
    $objPHPExcel->getActiveSheet()->getStyle('A5:Q5')->applyFromArray([
        'font' => [
            'color' => ['rgb' => 'FFFFFF'],
            'bold' => true,
            'size' => 11
        ],
        'fill' => [
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => ['rgb' => '538ED5']
        ]
    ]);

    // Obtener datos de la base de datos
    $rows = getData($con, $f_ini, $f_fin, $codigo_agencia);

    $row = 6;
    $i = 1;
    while ($student = mysqli_fetch_assoc($rows)) {

        $objPHPExcel->getActiveSheet()
                    ->setCellValue('A' . $row, $student['id'])
                    ->setCellValue('B' . $row, $student['codigo_agencia'])
                    ->setCellValue('C' . $row, $student['agencia'])
		    ->setCellValue('D' . $row, $student['ciudad'])
                    // Agregar más valores aquí...
                    ->setCellValue('Q' . $row, $student['estado']);

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('K' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $row++;
        $i++;
    }

    // Encabezado para descargar el archivo
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="result.xlsx"');
    header('Cache-Control: max-age=0');

    // Escribir el archivo
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
// }

function dep($data) {
    $format = print_r('<pre>');
    $format .= print_r($data);
    $format .= print_r('</pre>');
    return $format;
}

function getData($db, $f_ini, $f_fin, $codigo_agencia) {

    $sql = "SELECT t.id, a.codigo_agencia, a.nombre_agencia as agencia, k.nombre_ciudad as ciudad,
                    (SELECT DISTINCT descripcion_plan_padre FROM plan_padre WHERE codigo_plan_padre=t.codigo_plan) as plan,
                    t.precio,t.fecha_creacion as fechaInicio, t.fecha_cobranzas as fechaCobranzas, 
                    t.fecha_facturacion as fechaFacturacion,
                    CONCAT(nombres, ' ',ap_paterno, ' ',ap_materno) nombre, c.tipo_documento, c.num_documento as cedula, 
                    c.genero, c.fecha_nacimiento,c.telefono, u.nombre as usuario, 
                    CASE 
                        WHEN t.estado = 'V' THEN 'VALIDA'
                        WHEN t.estado = 'A' THEN 'ANULADA'
                        WHEN t.estado = 'P' THEN 'PENDIENTE'
                        WHEN t.estado = 'C' THEN 'COBRADA'
                        WHEN t.estado = 'F' THEN 'COBRADA'
                    END as estado,
                    t.cedula_asesor
                FROM temp t, clientes c, usuario u, agencias a, ciudades k
                WHERE t.id_contratante = c.id
                AND t.id_usuario = u.idusuario
                AND u.codigoAlmacen = a.codigo_agencia
                AND a.codigo_ciudad = k.id
                AND DATE(t.fecha_creacion) >= '$f_ini' AND DATE(t.fecha_creacion) <= '$f_fin'
                AND a.codigo_agencia = '$codigo_agencia'
                ORDER BY t.fecha_creacion DESC";

    //echo "SQL: " . $sql . "<br>";
    
    $res = mysqli_query($db, $sql);
    
    return $res;
}


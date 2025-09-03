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

if (isset($_POST['exportar'])) {


    $f_ini = $_POST['fecha_inicio'];
    $f_fin = $_POST['fecha_fin'];
    $codigo_agencia = $_POST['codigo_agencia'];
    $nombre_usuario = $_POST['nombre_usuario'];
    $nombre_agencia = $_POST['nombre_agencia'];
    /*
    $f_ini = '2024-03-15'; 
    $f_fin = '2024-03-16';
    $codigo_agencia = 'SCZ-LG';
    $nombre_supervisor = 'Juan Perez';
    $nombre_agencia = 'LA GUARDIA';
    */
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
                ->setCellValue('A1', "INNOVASALUD                                                                                REPORTE DE AGENCIA                                                                     PROMUJER")
                ->mergeCells("A1:J1");

    // Establecer estilos
    $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFont()->setSize(16)->setBold(true);
    $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    // Obtener la fecha actual
    // date_deafult_timezone_set('America/La_Paz');
    // $fecha = date('Y-m-d H:i:s');

    // $nombre_hoja='Reporte_Agencia_'.$nombre_agencia.'_'.$fecha;
    // $objPHPExcel->getActiveSheet()->setTitle('Reporte_Agencia');

    // Establecer anchos de columnas
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(35);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
    // $objPHPExcel->gerActiveSheet()->getColumnDimension('E')->setWidth(8);
    $objPHPExcel->getactiveSheet()->getColumnDimension('F')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(35);
    $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(10);

    // Obtener la fecha actual
    date_default_timezone_set('America/La_Paz');
    $fecha = date('Y-m-d H:i:s');

    // Agregar información
    $objPHPExcel->getActiveSheet()->setCellValue('A2', 'Usuario: ' . $nombre_usuario);
    $objPHPExcel->getActiveSheet()->setCellValue('A3', 'Agencia: ' . $nombre_agencia);
    $objPHPExcel->getActiveSheet()->setCellValue('I2', 'Fecha de Inicio: ' . $f_ini);
    $objPHPExcel->getActiveSheet()->setCellValue('I3', 'Fecha de Fin: ' . $f_fin);
    $objPHPExcel->getActiveSheet()->setCellValue('I4', 'Fecha & Hora Impresión: ' . $fecha);

    // Encabezados de las columnas
    $objPHPExcel->getActiveSheet()->setCellValue('A5', "#")
                                  ->setCellValue('B5', "NOMBRE")
                                  ->setCellValue('C5', "CEDULA")
                                  ->setCellValue('D5', "PLAN")
                                  ->setCellValue('E5', "PRECIO")
                                  ->setCellValue('F5', "FEC REGISTRO")
                                  ->setCellValue('G5', "FEC COBRANZA")
                                  ->setCellValue('H5', "CI ASESOR")
                                  ->setCellValue('I5', "VENDEDOR")
                                  ->setCellValue('J5', "ESTADO");

    // Establecer estilos para los encabezados
    $objPHPExcel->getActiveSheet()->getStyle('A5:J5')->applyFromArray([
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
	$total = 0;
    while ($student = mysqli_fetch_assoc($rows)) {

		$estado = $student['estado'];
		if($estado =='COBRADA'){
			$precio = $student['precio'];
			$total += $precio;
		}else{
			$precio = 0;
		}
		// $precio = number_format($precio,2,",",".");
        $objPHPExcel->getActiveSheet()
                    ->setCellValue('A' . $row, $i)
                    ->setCellValue('B' . $row, $student['nombre'])
                    ->setCellValue('C' . $row, $student['cedula'])
                    ->setCellValue('D' . $row, $student['plan'])
                    ->setCellValue('E' . $row, $precio)
                    ->setCellValue('F' . $row, $student['fechaInicio'])
                    ->setCellValue('G' . $row, $student['fechaCobranzas'])
                    ->setCellValue('H' . $row, $student['cedula_asesor'])
                    ->setCellValue('I' . $row, $student['usuario'])
                    ->setCellValue('J' . $row, $student['estado']);

        $objPHPExcel->getActiveSheet()->getStyle('A' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle('J' . $row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

        $row++;
        $i++;
    }

    // Encabezado para descargar el archivo
    $nombre_archivo='Reporte_Agencia_'.$nombre_agencia.'_'.$fecha.'.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename='.$nombre_archivo);
    header('Cache-Control: max-age=0');

    // Escribir el archivo
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
}

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


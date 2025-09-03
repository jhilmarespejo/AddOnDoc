<?php
// Incluir la librería PHPExcel
//require 'PHPExcel/Classes/PHPExcel.php';
require '../Classes/PHPExcel.php';

// Crear un nuevo objeto de PHPExcel
$objPHPExcel = new PHPExcel();

// Establecer propiedades del documento
$objPHPExcel->getProperties()->setCreator("Usuario")
                             ->setLastModifiedBy("Usuario")
                             ->setTitle("Ejemplo de Excel")
                             ->setSubject("Ejemplo")
                             ->setDescription("Este es un ejemplo de archivo de Excel.")
                             ->setKeywords("excel phpexcel ejemplo")
                             ->setCategory("Ejemplo");

// Agregar datos al archivo Excel
$objPHPExcel->setActiveSheetIndex(0)
            ->setCellValue('A1', 'ID')
            ->setCellValue('B1', 'Nombre')
            ->setCellValue('C1', 'Apellido');

// Ejemplo de datos (puedes obtener estos datos de tu base de datos)
$data = array(
    array(1, 'John', 'Doe'),
    array(2, 'Jane', 'Smith'),
    array(3, 'Alice', 'Johnson')
);

// Agregar los datos a las celdas correspondientes
$row = 2;
foreach ($data as $row_data) {
    $col = 'A';
    foreach ($row_data as $cell_data) {
        $objPHPExcel->getActiveSheet()->setCellValue($col . $row, $cell_data);
        $col++;
    }
    $row++;
}

// Establecer el nombre de la hoja
$objPHPExcel->getActiveSheet()->setTitle('Datos');

// Configurar el encabezado para descargar el archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ejemplo.xlsx"');
header('Cache-Control: max-age=0');

// Escribir el archivo Excel en formato xlsx
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;

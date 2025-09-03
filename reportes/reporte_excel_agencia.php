<?php

require '../config/global.php';
require '../Classes/PHPExcel.php';

$con = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
mysqli_set_charset($con, 'utf8');
// Verificar la conexión
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}



//if(isset($_POST["exportar"])){

	$f_ini = $_POST['fecha_inicio'];
    	$f_fin = $_POST['fecha_fin'];
    	$codigo_agencia = $_POST['codigo_agencia'];
    	$nombre_usuario = $_POST['nombre_usuario'];
    	$nombre_agencia = $_POST['nombre_agencia'];


    // dep($_POST);
    // echo "<br><br>";
    // die();

/*
    $f_ini = '2024-03-10';
    $f_fin = '2024-03-16';
    $codigo_agencia = 'SCZ-LG';
    $nombre_supervisor = 'Juan Perez';
    $nombre_agencia = 'LA GUARDIA';
*/

	date_default_timezone_set('America/La_Paz');
	$fecha = date( 'Y-m-d H:i:s');


    //require_once 'Classes/PHPExcel.php';

	//$gdImage = imagecreatefrompng('LogoSM.png')

	// Crea un nuevo objeto PHPExcel
    $objPHPExcel = new PHPExcel();


	$objDrawing = new PHPExcel_Worksheet_MemoryDrawing();
	$objDrawing->setName('LogoSM');
	$objDrawing->setDescription('LogoSM');
	$objDrawing->setImageResource($gdImage);
	$objDrawing->setRenderingFunction(PHPExcel_Worksheet_MemoryDrawing::RENDERING_PNG);
	$objDrawing->setMimeType(PHPExcel_Worksheet_MemoryDrawing::MIMETYPE_DEFAULT);
	$objDrawing->setHeight(70);
	$objDrawing->setCoordinates('A1');
	$objDrawing->setWorksheet($objPHPExcel->getActiveSheet());


	// Establecer propiedades
    $objPHPExcel->getProperties()
    ->setCreator("INNOVASALUD")
    ->setLastModifiedBy("INNOVASALUD")
    ->setTitle("Reporte Ventas Agencia")
    ->setSubject("Documento Excel")
    ->setDescription("Reporte Ventas Agencia")
    ->setKeywords("Excel Office 2007 openxml php")
    ->setCategory("EXPORT EXCEL");

	$estiloTituloReporte1 = array(
    'font' => array(
	'name'      => 'Arial',
	'bold'      => true,
	'italic'    => false,
	'strike'    => false,
	'size' =>14
    ),
    'fill' => array(
	'type'  => PHPExcel_Style_Fill::FILL_SOLID
	),
    'borders' => array(
	'allborders' => array(
	'style' => PHPExcel_Style_Border::BORDER_NONE
	)
    ),
    'alignment' => array(
	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
    )
	);

	$estiloTituloReporte2 = array(
    'font' => array(
	'name'      => 'Arial',
	'bold'      => true,
	'italic'    => false,
	'strike'    => false,
	'size' =>11
    ),
    'fill' => array(
	'type'  => PHPExcel_Style_Fill::FILL_SOLID
	),
    'borders' => array(
	'allborders' => array(
	'style' => PHPExcel_Style_Border::BORDER_NONE
	)
    ),
    'alignment' => array(
	'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
	'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER
    )
	);

	$estiloTituloColumnas = array(
    'font' => array(
	'name'  => 'Arial',
	'bold'  => true,
	'size' =>9,
	'color' => array(
	'rgb' => '000000' 
	)
    ),
    'fill' => array(
	'type' => PHPExcel_Style_Fill::FILL_SOLID,
	'color' => array('rgb' => '538ED5')
    ),
    'borders' => array(
	'allborders' => array(
	'style' => PHPExcel_Style_Border::BORDER_THIN
	)
    ),
    'alignment' =>  array(
	'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER
    )
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray( array(
    'font' => array(
	'name'  => 'Arial',
	'size' =>9,
	'color' => array(
	'rgb' => '000000'
	)
    ),
    'fill' => array(
	'type'  => PHPExcel_Style_Fill::FILL_SOLID
	),
    'borders' => array(
	'allborders' => array(
	'style' => PHPExcel_Style_Border::BORDER_THIN
	)
    ),
	'alignment' =>  array(
	'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER
    )
	));


	$estiloTituloColumnasX = array(
    'font' => array(
	'name'  => 'Arial',
	'bold'  => false,
	'size' =>9,
	'color' => array(
	'rgb' => '000000' 
	)
    ),
    'fill' => array(
	'type' => PHPExcel_Style_Fill::FILL_SOLID,
	'color' => array('rgb' => 'FFFFFF') 
    ),
    'borders' => array(
	'allborders' => array(
	'style' => PHPExcel_Style_Border::BORDER_THIN
	)
    ),
    'alignment' =>  array(
	'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER
    )
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray( array(
    'font' => array(
	'name'  => 'Arial',
	'size' =>9,
	'color' => array(
	'rgb' => '000000'
	)
    ),
    'fill' => array(
	'type'  => PHPExcel_Style_Fill::FILL_SOLID
	),
    'borders' => array(
	'allborders' => array(
	'style' => PHPExcel_Style_Border::BORDER_THIN
	)
    ),
	'alignment' =>  array(
	'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER
    )
	));

    $delta = 7;
    $alto = (int)$delta + (int)$ln;
    $var = 'A7:I'.$alto;
	$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->applyFromArray($estiloTituloReporte2);
	$objPHPExcel->getActiveSheet()->getStyle('A2:K2')->applyFromArray($estiloTituloReporte1);
	$objPHPExcel->getActiveSheet()->getStyle('A3:K6')->applyFromArray($estiloTituloReporte2);
	$objPHPExcel->getActiveSheet()->getStyle('A7:K7')->applyFromArray($estiloTituloColumnas);
	//$objPHPExcel->getActiveSheet()->getStyle($var)->applyFromArray($estiloTituloColumnasX);


    $mMes  = substr($idmes,5,2);
    $mAnio = substr($idmes,0,4);
    $idFechaIni = $mAnio . '-' . $mMes . '-01';

    $ldate = date('Y-m-d H:i:s');
    $mensaje = "FECHA IMPRESION: " . $ldate;



	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', 'PROMUJER')
    ->setCellValue('J1', 'INNOVASALUD')
    ->setCellValue('E2', 'REPORTE DE VENTAS POR AGENCIA')
    ->setCellValue('A3', 'Usuario: ' . $nombre_usuario)
    ->setCellValue('A4', 'Agencia: ' . $nombre_agencia)
    ->setCellValue('J3', 'Fecha Inicio: ' . $f_ini)
    ->setCellValue('J4', 'Fecha Fin    : ' . $f_fin)
    ->setCellValue('J5', $mensaje);



    $objPHPExcel->getActiveSheet()->getStyle('A6:Z999')
    ->getAlignment()->setWrapText(true); 

	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
	$objPHPExcel->getActiveSheet()->setCellValue('A7', 'No');

	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(45);
	$objPHPExcel->getActiveSheet()->setCellValue('B7', 'NOMBRE');

	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(12);
	$objPHPExcel->getActiveSheet()->setCellValue('C7', 'CEDULA');

	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
	$objPHPExcel->getActiveSheet()->setCellValue('D7', 'PLAN');

	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
	$objPHPExcel->getActiveSheet()->setCellValue('E7', 'PRECIO');

	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->setCellValue('F7', 'COBRANZA');

	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
	$objPHPExcel->getActiveSheet()->setCellValue('G7', 'FEC REGISTRO');

	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(20);
	$objPHPExcel->getActiveSheet()->setCellValue('H7', 'FEC COBRANZA');

	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(12);
	$objPHPExcel->getActiveSheet()->setCellValue('I7', 'CI ASESOR');

	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
	$objPHPExcel->getActiveSheet()->setCellValue('J7', 'VENDEDOR');

	$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(12);
	$objPHPExcel->getActiveSheet()->setCellValue('K7', 'ESTADO');

	$rows = getData($con, $f_ini, $f_fin, $codigo_agencia);


    $i = 1;
    $fila = 8;

    while($row = mysqli_fetch_assoc($rows)){

	$cobranza = 0;
	$estado = $row['estado'];
	$precio = $row['precio'];
	if($estado =='COBRADA'){ 
		$cobranza = $precio;
	}

	$total += $cobranza;

        //var_dump($row);
        //die();
	//$precio = number_format($precio,2,".",",");
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$fila, $i++);
        $objPHPExcel->getActiveSheet()->setCellValue('B'.$fila, $row['nombre']);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$fila, $row['cedula']);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$fila, $row['plan']);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$fila, $precio);
	$objPHPExcel->getActiveSheet()->setCellValue('F'.$fila, $cobranza);

	// Get the alignment style for the cell
	$cell = 'E'.$fila;
	$alignment = $objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment();

	// Set the horizontal alignment to right (justify)
	$alignment->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

	// Get the alignment style for the cell
        $cell = 'F'.$fila;
        $alignment = $objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment();

        // Set the horizontal alignment to right (justify)
        $alignment->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);



        $objPHPExcel->getActiveSheet()->setCellValue('G'.$fila, $row['fechaInicio']);
        $objPHPExcel->getActiveSheet()->setCellValue('H'.$fila, $row['fechaCobranzas']);
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$fila, $row['cedula_asesor']);
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$fila, $row['usuario']);
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$fila, $row['estado']);


        $fila++;
    }

	$fila++;
	$total = number_format($total,2,".",",");
	$mTotal = "Total Bs.: " . $total;
	$objPHPExcel->getActiveSheet()->setCellValue('F'.$fila, $mTotal);


	// Set font size for a specific cell (e.g., A1:J1)
	$objPHPExcel->getActiveSheet()->getStyle('F'.$fila.':F'.$fila)->applyFromArray($estiloTituloReporte2);


	// Get the alignment style for the cell
        $cell = 'F'.$fila;
        $alignment = $objPHPExcel->getActiveSheet()->getStyle($cell)->getAlignment();


	// Set the horizontal alignment to right (justify)
        $alignment->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);




	$objPHPExcel->getActiveSheet()->setTitle('Reporte Agencia');

    // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
    $objPHPExcel->setActiveSheetIndex(0);

    // Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
	$nombre_archivo='Reporte_Agencia_'.$nombre_agencia.'_'.$fecha.'.xlsx';
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename='.$nombre_archivo);
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
//}

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
				AND t.agencia_venta = '$codigo_agencia'
				ORDER BY t.fecha_creacion DESC";

    //echo "SQL: " . $sql . "<br>";

    $res = mysqli_query($db, $sql);

    return $res;
}


function dep($data){
        $format = print_r('<pre>');
        $format .= print_r($data);
        $format .= print_r('</pre>');
        return $format;
}

?>


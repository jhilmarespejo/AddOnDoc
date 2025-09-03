<?php
ob_start();
if (strlen(session_id()) < 1){
	session_start();//Validamos si existe o no la sesión
}
require '../config/global.php';
require '../Classes/PHPExcel.php';

$con = mysqli_connect(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
mysqli_set_charset($con, 'utf8');
// Verificar la conexión
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$nombre_usuario = $_SESSION['nombre'];
$codigo_canal = $_SESSION['codigo_canal'];

//if(isset($_POST["exportar"])){

/*
	$f_ini = $_POST['fecha_inicio'];
    	$f_fin = $_POST['fecha_fin'];
    	$codigo_agencia = $_POST['codigo_agencia'];
    	$nombre_usuario = $_POST['nombre_usuario'];
    	$nombre_agencia = $_POST['nombre_agencia'];
*/

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
	//$codigo_canal = 'C001';

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
    ->setTitle("Reporte Usuarios")
    ->setSubject("Documento Excel")
    ->setDescription("Reporte Usuarios")
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

    $delta = 6;
    $alto = (int)$delta + (int)$ln;
    $var = 'A6:I'.$alto;
	$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->applyFromArray($estiloTituloReporte2);
	$objPHPExcel->getActiveSheet()->getStyle('A2:L2')->applyFromArray($estiloTituloReporte1);
	$objPHPExcel->getActiveSheet()->getStyle('A3:L5')->applyFromArray($estiloTituloReporte2);
	$objPHPExcel->getActiveSheet()->getStyle('A6:L6')->applyFromArray($estiloTituloColumnas);
	//$objPHPExcel->getActiveSheet()->getStyle($var)->applyFromArray($estiloTituloColumnasX);


    $mMes  = substr($idmes,5,2);
    $mAnio = substr($idmes,0,4);
    $idFechaIni = $mAnio . '-' . $mMes . '-01';

    $ldate = date('Y-m-d H:i:s');
    $mensaje = "FECHA IMPRESION: " . $ldate;



	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', 'PROMUJER')
    ->setCellValue('J1', 'INNOVASALUD')
    ->setCellValue('D2', 'REPORTE DE USUARIOS')
    ->setCellValue('A4', 'Usuario: ' . $nombre_usuario)
    ->setCellValue('J4', $mensaje);



    $objPHPExcel->getActiveSheet()->getStyle('A6:Z999')
    ->getAlignment()->setWrapText(true); 

	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
	$objPHPExcel->getActiveSheet()->setCellValue('A6', 'No');

	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
	$objPHPExcel->getActiveSheet()->setCellValue('B6', 'NOMBRE');

	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
	$objPHPExcel->getActiveSheet()->setCellValue('C6', 'ROL');

	$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
	$objPHPExcel->getActiveSheet()->setCellValue('D6', 'AGENCIA');

	$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
	$objPHPExcel->getActiveSheet()->setCellValue('E6', 'TIP DOC');

	$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
	$objPHPExcel->getActiveSheet()->setCellValue('F6', 'NUM DOC');

	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14);
	$objPHPExcel->getActiveSheet()->setCellValue('G6', 'COMPLEMENTO');

	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(12);
	$objPHPExcel->getActiveSheet()->setCellValue('H6', 'EXPEDIDO');

	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
	$objPHPExcel->getActiveSheet()->setCellValue('I6', 'LOGIN');

	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(35);
	$objPHPExcel->getActiveSheet()->setCellValue('J6', 'CORREO');

	$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(12);
        $objPHPExcel->getActiveSheet()->setCellValue('K6', 'TELEFONO');

	$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(10);
        $objPHPExcel->getActiveSheet()->setCellValue('L6', 'ESTADO');



	$rows = getData($con,$codigo_canal);


    $i = 1;
    $fila = 7;

    while($row = mysqli_fetch_assoc($rows)){


        //die();
        $objPHPExcel->getActiveSheet()->setCellValue('A'.$fila, $i++);
	$objPHPExcel->getActiveSheet()->setCellValue('B'.$fila, $row['nombre']);
        $objPHPExcel->getActiveSheet()->setCellValue('C'.$fila, $row['role']);
        $objPHPExcel->getActiveSheet()->setCellValue('D'.$fila, $row['agencia']);
        $objPHPExcel->getActiveSheet()->setCellValue('E'.$fila, $row['tip_doc']);
        $objPHPExcel->getActiveSheet()->setCellValue('F'.$fila, $row['num_documento']);
        $objPHPExcel->getActiveSheet()->setCellValue('G'.$fila, $row['complemento']);
        $objPHPExcel->getActiveSheet()->setCellValue('H'.$fila, $row['expedido']);
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$fila, $row['login']);
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$fila, $row['correo']);
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$fila, $row['telefono']);
		$objPHPExcel->getActiveSheet()->setCellValue('L'.$fila, $row['estado']);


        $fila++;
    }




	$objPHPExcel->getActiveSheet()->setTitle('Reporte Usuarios');

    // Establecer la hoja activa, para que cuando se abra el documento se muestre primero.
    $objPHPExcel->setActiveSheetIndex(0);

    // Se modifican los encabezados del HTTP para indicar que se envia un archivo de Excel.
	$nombre_archivo='Reporte_Usuarios_'.$fecha.'.xlsx';
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename='.$nombre_archivo);
    header('Cache-Control: max-age=0');

    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
//}

function getData($db, $codigo_canal) {

    $sql = "SELECT nombre, role, codigoAlmacen, a.nombre_agencia as agencia,
  		CASE
    			WHEN u.tipo_documento = 'C' THEN 'CEDULA'
	    		WHEN u.tipo_documento = 'P' THEN 'PASAPORTE'
    			WHEN u.tipo_documento = 'O' THEN 'OTRO'
    			WHEN u.tipo_documento = 'E' THEN 'CARNET EXTRANJERO'
	  	END as tip_doc,
  		u.num_documento, u.extension as complemento, u.expedido, u.login, u.correo, u.telefono,
	  	CASE
    			WHEN u.id_condicion = '1' THEN 'BAJA'
    			WHEN u.id_condicion = '2' THEN 'ALTA'
	  	END as estado
		FROM usuario u, agencias a
		WHERE u.codigoAlmacen = a.codigo_agencia
		AND u.codigo_canal = '$codigo_canal'
		AND u.idusuario NOT IN('140','139','147')";


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


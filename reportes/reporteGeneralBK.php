<?php

require '../config/global.php';

//call the autoload
require 'vendor/autoload.php';

//load phpspreadsheet class using namespaces
use PhpOffice\PhpSpreadsheet\Spreadsheet;

//call iofactory instead of xlsx writer
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;



$con = mysqli_connect(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
mysqli_set_charset($con, 'utf8');
// Check connection
if (mysqli_connect_errno())
{
 echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

//if(isset($_POST['exportar'])){
if(true){

	
	$f_ini = $_GET['fecha_inicio'];
	$f_fin = $_GET['fecha_fin'];
	$codigo_agencia = $_GET['codigo_agencia'];
	$nombre_supervisor = $_GET['nombre_supervisor'];
	$nombre_agencia = $_GET['nombre_agencia'];
	$codigo_canal = $_GET['codigo_canal'];
	


/* 
	$f_ini='2024-02-01';
	$f_fin='2024-02-25';
	$codigo_agencia='chimba';
	$nombre_supervisor='JUAN';
	$nombre_agencia='obrajes';
	$codigo_canal='C011';

 */	//styling arrays
	//table head style
	$tableHead = [
		'font'=>[
			'color'=>[
				'rgb'=>'FFFFFF'
			],
			'bold'=>true,
			'size'=>11
		],
		'fill'=>[
			'fillType' => Fill::FILL_SOLID,
			'startColor' => [
				'rgb' => '538ED5'
			]
		],
	];
	//even row
	$evenRow = [
		'fill'=>[
			'fillType' => Fill::FILL_SOLID,
			'startColor' => [
				'rgb' => '00BDFF'
			]
		]
	];
	//odd row
	$oddRow = [
		'fill'=>[
			'fillType' => Fill::FILL_SOLID,
			'startColor' => [
				'rgb' => '00EAFF'
			]
		]
	];

	//styling arrays end
	//make a new spreadsheet object
	$spreadsheet = new Spreadsheet();
	//get current active sheet (first sheet)
	$sheet = $spreadsheet->getActiveSheet();

	//set default font
	$spreadsheet->getDefaultStyle()
		->getFont()
		->setName('Arial')
		->setSize(10);

	//heading
	$spreadsheet->getActiveSheet()
		->setCellValue('A1',"REPORTE CONSOLIDADO GENERAL");

	//merge heading
	$spreadsheet->getActiveSheet()->mergeCells("A1:Q1");

	// set font style
	$spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(16)->setBold(true);

	// set cell alignment
	$spreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

	//setting column width
	$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(5);
	$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(10);
	$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(30);
	$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
	$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(45);
	$spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(10);
	$spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
	$spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
	$spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(45);
	$spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(10);
	$spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(15);
	$spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(10);
	$spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(15);
	$spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(15);
	$spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(40);
	$spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(15);
	//$spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(10);

	$spreadsheet->getActiveSheet()->getStyle('A1')->getFont()->setSize(16);
	
	date_default_timezone_set('America/La_Paz');
	$fecha = date('Y-m-d H:i:s');
	
	
	$spreadsheet->getActiveSheet()
		->setCellValue('A2','Usuario: ' . $nombre_supervisor);
	$spreadsheet->getActiveSheet()->getStyle('A2')->getFont()->setSize(11)->setBold(true);
	
	$spreadsheet->getActiveSheet()
		->setCellValue('A3','Fecha & Hora Impresión: ' . $fecha);
	$spreadsheet->getActiveSheet()->getStyle('A3')->getFont()->setSize(11)->setBold(true);	
	
	
	$spreadsheet->getActiveSheet()
		->setCellValue('N2','Fecha de Inicio: ' . $f_ini);
	$spreadsheet->getActiveSheet()->getStyle('N2')->getFont()->setSize(11)->setBold(true);
	
	$spreadsheet->getActiveSheet()
		->setCellValue('N3','Fecha de Fin: ' . $f_fin);
	$spreadsheet->getActiveSheet()->getStyle('N3')->getFont()->setSize(11)->setBold(true);
	


			
	//header text
	$spreadsheet->getActiveSheet()
		->setCellValue('A5',"ID")
		->setCellValue('B5',"Cod Ag.")
		->setCellValue('C5',"Agencia")
		->setCellValue('D5',"Ciudad")
		->setCellValue('E5',"Plan")
		->setCellValue('F5',"Precio")
		->setCellValue('G5',"Fec Registro")
		->setCellValue('H5',"Fec Cobranza")
		->setCellValue('I5',"Nombre")
		->setCellValue('J5',"Tip Doc")
		->setCellValue('K5',"Cedula")
		->setCellValue('L5',"Genero")
		->setCellValue('M5',"Fec Nac")
		->setCellValue('N5',"Telefono")
		->setCellValue('O5',"Vendedor")
		->setCellValue('P5',"Estado");


	//set font style and background color
	$spreadsheet->getActiveSheet()->getStyle('A5:P5')->applyFromArray($tableHead);


	$rows = getData($con,$f_ini, $f_fin, $codigo_canal);

	$student = mysqli_fetch_assoc($rows);
	//dep($student);


	$row = 6;
	$i = 1;
	$student['id'] = $i;
	while ($student = mysqli_fetch_assoc($rows)){

		$spreadsheet->getActiveSheet()
			->setCellValue('A'.$row , $student['id'])
			->setCellValue('B'.$row , $student['codigo_agencia'])
			->setCellValue('C'.$row , $student['agencia'])
			->setCellValue('D'.$row , $student['ciudad'])
			->setCellValue('E'.$row , $student['plan'])
			->setCellValue('F'.$row , $student['precio'])
			->setCellValue('G'.$row , $student['fechaInicio'])
			->setCellValue('H'.$row , $student['fechaCobranzas'])
			->setCellValue('I'.$row , $student['nombre'])
			->setCellValue('J'.$row , $student['tipo_documento'])
			->setCellValue('K'.$row , $student['cedula'])
			->setCellValue('L'.$row , $student['genero'])
			->setCellValue('M'.$row , $student['fecha_nacimiento'])
			->setCellValue('N'.$row , $student['telefono'])
			->setCellValue('O'.$row , $student['usuario']);
			
			$spreadsheet->getActiveSheet()
				->getStyle('A'.$row)->getAlignment()
				->setHorizontal(Alignment::HORIZONTAL_CENTER);
			$spreadsheet->getActiveSheet()
				->getStyle('K'.$row)->getAlignment()
				->setHorizontal(Alignment::HORIZONTAL_CENTER);
			
		
		$row++;
		$i++;
	}

	
	//set the header first, so the result will be treated as an xlsx file.
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

	//make it an attachment so we can define filename
	header('Content-Disposition: attachment;filename="result.xlsx"');

	//create IOFactory object
	$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
	//save into php output
	$writer->save('php://output');

}

function dep($data){
	$format = print_r('<pre>');
	$format .= print_r($data);
	$format .= print_r('</pre>');
	return $format;
}

function getData($db, $f_ini, $f_fin, $codigo_canal){

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
					END as estado
				FROM temp t, clientes c, usuario u, agencias a, ciudades k
				WHERE t.id_contratante = c.id
				AND t.id_usuario = u.idusuario
				AND u.codigoAlmacen = a.codigo_agencia
				AND a.codigo_ciudad = k.id
				AND DATE(t.fecha_creacion) >= '$f_ini' AND DATE(t.fecha_creacion) <= '$f_fin'
				AND t.codigo_canal = '$codigo_canal'";
	
	$res = mysqli_query($db,$sql);
	
	return $res;
	
}


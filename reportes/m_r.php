<?php
require '../config/global.php';
require('../fpdf185/fpdf.php');

session_start();

$con = mysqli_connect(DB_HOST,DB_USERNAME,DB_PASSWORD,DB_NAME);
mysqli_set_charset($con, 'utf8');
// Check connection
if (mysqli_connect_errno())
{
 echo "Failed to connect to MySQL: " . mysqli_connect_error();
}


class PDF extends FPDF
{
	// Cabecera de página
	function Header()
	{
		// Logo
		//$this->Cell(50);
		$this->Cell(440);
		$this->Image('../files/logo/LogoInnovaLG.jpg',143,10,40);
		// Arial bold 15
		$this->SetFont('Arial','B',15);

		// SET text in BLUE
		$this->SetTextColor(31,78,121);

		// Movernos a la derecha
		$this->Ln(2);
		$this->Cell(21);
		// Título
		//$this->Cell(70,10,'Reporte de Productos',1,0,'C');
		$this->Cell(52,10,'COMPROBANTE DE PAGO',0,0,'C');

		$this->SetDrawColor(31,78,121);
		$this->SetLineWidth(0.5);
		$this->Line(23.00,20.00, 91.00, 20.00, null);


	}

	// Pie de página
	function Footer(){

		//----------------------------------------//
		// Establece Conexion a la base de Datos
		//----------------------------------------//
		// $con = mysqli_connect(DB_SERVER,DB_USER,DB_PASS,DB_NAME);

		// Check connection
		// if (mysqli_connect_errno()){
			// echo "Failed to connect to MySQL: " . mysqli_connect_error();
			// die();
		// }


		// Posición: a 4,5 cm del final




	}
}

function dep($data){
	$format = print_r('<pre>');
	$format .= print_r($data);
	$format .= print_r('</pre>');

	return $format;
}
function obtenerDatosRegistro($db, $id_registro){

	$sql = "SELECT CONCAT(c.nombres, ' ',c.ap_paterno,' ',c.ap_materno) nombre, t.codigo_plan, t.precio, p.descripcion_plan_padre, c.num_documento cedula
	FROM temp t, clientes c, plan_padre p
	WHERE t.id_contratante = c.id
	AND t.codigo_plan = p.codigo_plan_padre
	AND t.id ='$id_registro'";

	$result = mysqli_query($db, $sql);
	$row = mysqli_fetch_assoc($result);

	return $row;
}

	//dep($_SESSION);
	$id_registro = $_SESSION['id_registro_a_facturar'];
	//$id_registro = '145';
	//$id_paciente = $_GET['id_paciente'];

	$id_registro = 632;
	$row = obtenerDatosRegistro($con, $id_registro);


	date_default_timezone_set('America/La_Paz');
	$ldate=date('Y-m-d');

	//$pdf = new PDF('P','mm','letter');
	$pdf = new PDF('P','mm','letter');
	$pdf->AliasNbPages();
	$pdf->AddPage();


	// Definimos el color AZUL
	$pdf->SetTextColor(31,78,121);
	$pdf->SetDrawColor(31,78,121);
	$pdf->SetFont('Arial','B',8);

    // Dibujasmos el borde principal
	$pdf->Rect(12,10,191,110);


	$pdf->Ln(11);
	$pdf->Cell(12);
	$pdf->SetFont('Arial','',12);  // Aqui imprime normal
	$pdf->Cell(35,7,'Fecha: ' . $ldate , 0, 0, 'L',0);
	
	$pdf->Ln(8);
	$pdf->Cell(12);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(35,7,'DATOS CLIENTE' , 0, 0, 'L',0);


	// NOMBRE PACIENTE PAGINA 1 //
	$pdf->Ln(7);
	$pdf->Cell(12);
	
	$pdf->SetFont('Arial','',12);
	$paciente = "Juan Carlos Perez";
	$paciente = utf8_decode($row['nombre']);
	//$paciente = utf8_decode($paciente);
	$pdf->Cell(35,7,'Nombre: ' . $paciente, 0, 0, 'L',0);
	$pdf->Ln(7);

	$pdf->Cell(12);
	$edad = "35";
	$cedula = '821755';
	$cedula = $row['cedula'];
	$carnet = utf8_decode('Cédula N°: ') . $cedula;
	$pdf->Cell(30,7,$carnet, 0, 0, 'L',0);
	$pdf->Ln(9);

	$plan = utf8_decode($row['descripcion_plan_padre']);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(11);
	$pdf->Cell(35,7,'PLAN ADQUIRIDO', 0, 0, 'L',0);

	$pdf->Ln(-2);
	$pdf->Cell(128);
	$pdf->Cell(52,10,'PRECIO Bs.',0,0,'C');
	
	$pdf->Ln(8);
	$pdf->SetFont('Arial','',12);
	$pdf->Cell(12);
	$pdf->Cell(39,7,' - '.$plan ,0,0,'L');
	
	$pdf->Ln(-2);
	$pdf->Cell(128);
	$precio = $row['precio'];
	$pdf->Cell(52,10,$precio,0,1,'C');
	
	//$pdf->Ln(5);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(11);
	$pdf->Cell(70,10,'Estimado Cliente',0,1);

	$pdf->SetFont('Arial','',10);
	$pdf->Ln(-3);
	$pdf->Cell(11);
	$mensaje = 'Descarga de facturas 24 horas despues de la compra ingresando el número de carnet y fecha';
	$pdf->Cell(70,10,utf8_decode($mensaje),0,1);
	$pdf->Ln(-5);
	$pdf->Cell(11);
	$mensaje = 'de nacimiento registrados desde la siguiente dirección: ';
	$pdf->Cell(90,10,utf8_decode($mensaje),0,1);


	$pdf->Ln(-3);
	$pdf->Cell(17);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(80,10,'- www.innovasalud.bo/facturas',0,1);

	

	//$pdf->Ln(5);
	$pdf->Cell(11);
	$msg = utf8_decode('Contáctanos por WhatsApp');
	$pdf->Cell(52,10,$msg,0,0,'C');
	
	$pdf->Ln(1);
	$pdf->Cell(128);
	$msg = utf8_decode('Visítanos');
	$pdf->Cell(52,10,$msg,0,0,'C');
	
	
	$pdf->Ln(6);
	$pdf->Cell(10);
	$pdf->SetFont('Arial','B',14);
	$pdf->Cell(52,10,'+591 765 03333',0,0,'C');
	
	//$pdf->Ln(-1);
	$pdf->Cell(68);
	$pdf->SetFont('Arial','B',15);
	$pdf->Cell(52,10,'www.innovasalud.bo',0,0,'C');


    // Dibujasmos el borde del TALON
	$pdf->Rect(12,140,191,42);
	
	$pdf->Ln(36);
	$pdf->Cell(68);
	$pdf->SetFont('Arial','B',12);
	$pdf->Cell(52,10,'TALON DE CAJA',0,0,'C');
	
	$pdf->SetLineWidth(0.5);
	$pdf->Line(87.00,148.00, 121.00, 148.00, null);
	
	$pdf->Ln(9);
	$pdf->Cell(135);
	$pdf->SetFont('Arial','',10);  // Aqui imprime normal
	$pdf->Cell(35,7,'Fecha: ' . $ldate , 0, 0, 'L',0);
	
	$pdf->Ln(1);
	$pdf->Cell(12);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(35,7,'DATOS CLIENTE' , 0, 1, 'L',0);

	$pdf->SetFont('Arial','',10);
	
	$pdf->Ln(-1);
	$pdf->Cell(12);
	$paciente = utf8_decode($row['nombre']);
	//$paciente = utf8_decode($paciente);
	$pdf->Cell(35,7,'Nombre: ' . $paciente, 0, 0, 'L',0);
	$pdf->Ln(5);

	$pdf->Cell(12);
	$edad = "35";
	$cedula = '821755';
	$cedula = $row['cedula'];
	$carnet = utf8_decode('Cédula N°: ') . $cedula;
	$pdf->Cell(30,7,$carnet, 0, 0, 'L',0);
	$pdf->Ln(7);

	$plan = utf8_decode($row['descripcion_plan_padre']);
	$pdf->SetFont('Arial','B',10);
	$pdf->Cell(11);
	$pdf->Cell(35,7,'PLAN ADQUIRIDO', 0, 0, 'L',0);

	$pdf->Ln(-2);
	$pdf->Cell(128);
	$pdf->Cell(52,10,'PRECIO Bs.',0,0,'C');
	
	$pdf->Ln(8);
	$pdf->SetFont('Arial','',10);
	$pdf->Cell(12);
	$pdf->Cell(39,7,' - '.$plan ,0,0,'L');
	
	$pdf->Ln(-2);
	$pdf->Cell(128);
	$precio = $row['precio'];
	$pdf->Cell(52,10,$precio,0,1,'C');

	$pdf->Output();


?>

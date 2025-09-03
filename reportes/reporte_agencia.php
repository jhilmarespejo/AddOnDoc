<?php
require '../config/global.php';
require '../config/Conexion.php';
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
		//$this->Cell(440);
		//$this->Image('../files/logo/LogoInnovaLG.jpg',143,10,40);
		// Arial bold 15
		$this->SetFont('Arial','B',10);

		// SET text in BLUE
		$this->SetTextColor(31,78,121);
		
		// Movernos a la derecha
		$this->Cell(-6);
		$this->Cell(52,10,'PROMUJER',0,0,'C');
		$this->Cell(147);
		$this->Cell(52,10,'INNOVASALUD',0,0,'C');

		$this->SetFont('Arial','B',15);
		$this->Ln(5);
		$this->Cell(71);
		// Título
		//$this->Cell(70,10,'Reporte de Productos',1,0,'C');
		$this->Cell(110,10,'REPORTE DE AGENCIA',0,0,'C');
		
		$this->SetDrawColor(31,78,121);
		$this->SetLineWidth(0.5);
		$this->Line(106.00,23.00, 166.00, 23.00, null);

		$this->Ln(40);
		//$this->Cell(10);
		$this->SetFont('Arial','B',8);
		$this->Cell(6,7, '#', 1, 0, 'C',0);
		$this->Cell(47,7, 'NOMBRE', 1, 0, 'C',0);
		$this->Cell(15,7, ' CEDULA', 1, 0, 'L',0);
		$this->Cell(50,7, ' PLAN', 1, 0, 'C',0);
		$this->Cell(15,7, 'PRECIO', 1, 0, 'C',0);
		$this->Cell(25,7, 'FEC REGISTRO', 1, 0, 'C',0);
		$this->Cell(25,7, 'FEC COBRANZA', 1, 0, 'C',0);
		$this->Cell(17,7, 'CI ASESOR', 1, 0, 'C',0);
		$this->Cell(35,7, 'VENDEDOR', 1, 0, 'C',0);
		$this->Cell(15,7, 'ESTADO', 1, 1, 'R',0);
	

	}

	// Pie de página
	function Footer(){

	// Posición: a 1,5 cm del final
    $this->SetY(-15);
    // Arial italic 8
    $this->SetFont('Arial','I',8);
    // Número de página
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
		
	}
}

function dep($data){
	$format = print_r('<pre>');
	$format .= print_r($data);
	$format .= print_r('</pre>');

	return $format;
}
function getData($db, $f_ini, $f_fin, $codigo_agencia){

	$sql = "SELECT t.id, a.codigo_agencia, a.nombre_agencia as agencia, k.nombre_ciudad as ciudad,
					(SELECT DISTINCT descripcion_plan_padre FROM plan_padre WHERE codigo_plan_padre=t.codigo_plan) as plan, 
					t.precio,t.fecha_creacion as fechaInicio, t.fecha_cobranzas as fechaCobranzas, 
					t.fecha_facturacion as fechaFacturacion,
					CONCAT(nombres, ' ',ap_paterno, ' ',ap_materno) nombre, c.tipo_documento, c.num_documento as cedula, 
					c.genero, c.fecha_nacimiento,c.telefono, u.nombre as usuario, 
					CASE
						WHEN t.estado = 'C' THEN 'COBRADO' 
						WHEN t.estado = 'V' THEN 'VALIDA'
						WHEN t.estado = 'A' THEN 'ANULADA'
						WHEN t.estado = 'P' THEN 'PENDIENTE'

					END as estado
				FROM temp t, clientes c, usuario u, agencias a, ciudades k
				WHERE t.id_contratante = c.id
				AND t.id_usuario = u.idusuario
				AND u.codigoAlmacen = a.codigo_agencia
				AND a.codigo_ciudad = k.id
				AND t.cobranza = 'COBRADO'
				AND DATE(t.fecha_cobranzas) >= '$f_ini' AND DATE(t.fecha_cobranzas) <= '$f_fin'
				AND a.codigo_agencia = '$codigo_agencia'
				ORDER BY t.fecha_cobranzas DESC";

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

	$res = mysqli_query($db,$sql);
	
	return $res;
	
}

if(isset($_POST)){

	$usuario = $_POST['nombre_supervisor'];
	$fec_ini = $_POST['fecha_inicio'];
	$fec_fin = $_POST['fecha_fin'];
	$nombre_agencia = $_POST['nombre_agencia'];
	$codigo_agencia = $_POST['codigo_agencia'];
	$nombre_ciudad  = $_SESSION['nombre_ciudad'];
		


/* 
	$id_registro = 6;
	$fec_ini = '2024-02-01';
	$fec_fin = '2024-02-29';
	$codigo_agencia = 'ELT-JP';
	$cajero = 'Juan Perez';
 */	
	$rows = getData($conexion, $fec_ini, $fec_fin, $codigo_agencia);


	date_default_timezone_set('America/La_Paz');
	$ldate=date('Y-m-d H:i:s');

	
	/* $fname = 'ReporteCaja'. '-' . $codigo_agencia . '-' .substr($ldate,0,10).'.pdf';
	echo "FILE: " . $fname . "<br>";	
	die(); */


	//$pdf = new PDF('P','mm','letter');
	$pdf = new PDF('L','mm','letter');
	$pdf->AliasNbPages();
	$pdf->AddPage();
	
		
	// Definimos el color AZUL
	$pdf->SetTextColor(31,78,121);
	$pdf->SetDrawColor(31,78,121);
	$pdf->SetFont('Arial','',10);

	// DIBUJAMOS EL RECUADRO EXTERNO
	// $pdf->Rect(12,10,191,110);


	$pdf->Ln(-35);
	//$pdf->Cell(5);
	$pdf->Cell(5);
	$pdf->Cell(35,7,'Cajero: ' . $usuario , 0, 0, 'L',0);
	$pdf->Cell(155);
	$pdf->Cell(35,7,'Fec Ini: ' . $fec_ini , 0, 1, 'L',0);

	$pdf->Ln(-1);
	$pdf->Cell(5);
	$nombre_agencia = utf8_decode($nombre_agencia);
	$pdf->Cell(35,7,'Agencia: ' . $nombre_agencia , 0, 0, 'L',0);
	$pdf->Cell(155);
	$pdf->Cell(35,7,'Fec Fin: ' . $fec_fin , 0, 1, 'L',0);

	$pdf->Ln(-1);
	$pdf->Cell(5);
	$pdf->Cell(35,7,'Ciudad: ' . $nombre_ciudad , 0, 0, 'L',0);
	$pdf->Cell(155);
	$pdf->Cell(35,7,'Fec Imp: ' . $ldate , 0, 1, 'L',0);

	$pdf->Ln(17);
	$pdf->SetFont('Arial','',7);
	$i = 1;
	$total = 0;
	while($row = mysqli_fetch_assoc($rows)){
		$pdf->Cell(6,7, $i++, 1, 0, 'R',0);
		//$pdf->Cell(15,7, $nombre_agencia, 1, 0, 'C',0);
		//$nombre = utf8_decode($row['ciudad']);
		//$pdf->Cell(15,7, $nombre, 1, 0, 'L',0);
		$pdf->Cell(47,7, substr($row['nombre'],0,30), 1, 0, 'L',0);
		$pdf->Cell(15,7, $row['cedula'], 1, 0, 'R',0);
		$plan = utf8_decode($row['plan']);
		$pdf->Cell(50,7, $plan, 1, 0, 'L',0);
		$estado = $row['estado'];
		if($estado =='COBRADA'){
			$precio = $row['precio'];
			$total += $precio;

		}else{
			$precio = 0;
		}
		$precio = number_format($precio,2,",",".");

		$pdf->Cell(15,7, $precio, 1, 0, 'R',0);
		$pdf->Cell(25,7, $row['fechaInicio'], 1, 0, 'L',0);
		$pdf->Cell(25,7, $row['fechaCobranzas'], 1, 0, 'R',0);
		$pdf->Cell(17,7, $row['cedula_asesor'], 1, 0, 'R',0);
		$pdf->Cell(35,7, substr($row['usuario'],0,20), 1, 0, 'R',0);
		$pdf->Cell(15,7, substr($row['estado'],0,20), 1, 1, 'R',0);


	}
	$pdf->Ln(1);
	$pdf->SetFont('Arial','B',9);
	$pdf->Cell(97);
	$total = number_format($total,2,",",".");
	$pdf->Cell(36,7, 'TOTAL Bs.: '.$total, 1, 1, 'R',0);

	$fname = 'ReporteCaja'. '-' . $codigo_agencia . '-' .substr($ldate,0,10).'.pdf';
	$pdf->Output('D',$fname);
	//$pdf->Output();
}

?>

<?php
	session_start();
	include_once(dirname(__FILE__)."/../fpdf17/fpdf.php");
	include_once(dirname(__FILE__)."/../Clases/ClassPostgresBD.php");
	

	$PDF_connect = new PostgresDB();
	$mes    	= $_GET['Mes'];
	$anno   	= $_GET['Anno'];
	$Inforuta 	= json_decode($_GET['Ruta'],true);
	$PDF_connect->OpenPostgres();

	$arrayInfoRuta	= $Inforuta['Ruta'][0]['ruta'];
	$_ruta = explode("-", $arrayInfoRuta);	//Ciclo-Municipio-Ruta

	class PDF extends FPDF{	
		function Header(){	
			$this->Image('../imagenes/logo_sypelc.png',45,10,55);
			$this->SetFont('Arial','B',12);
			$this->Cell(0,0,'SYPELC SAS',0,0,'C');		
			$this->Ln(8);
			$this->Cell(0,0,'CERTIFICACION GENERAL',0,0,'C');
			$this->Ln(5);
			$this->Cell(0,0,'ENTREGA DE FACTURAS',0,0,'C');
			$this->Ln(10);
		}
	}
	
	//$TamañoHoja = array(445.9,490.4);				//configuracion del tamaño de la hoja
	$pdf=new PDF('L','mm','A4');		//configuracion de orientacion y unidades de medida
	
	
	$pdf->AddPage();
	$pdf->SetMargins(7,10,5);					//configuracion de margenes
	$pdf->SetFont('Arial','B',8);					//configuracion del tamaño de la letra
	$pdf->Cell(0,0,'RUTA: '.$_ruta[2].' - CICLO: '.$_ruta[0].' - MUNICIPIO: '.$_ruta[1].'',0,0,'C');
	$pdf->Ln(10);
	$pdf->Cell(0,0,'REALIZO: ___________________________________________________________',0,0,'L');
	$pdf->Ln(6);
	$pdf->Cell(0,0,'FECHA: '.$anno.' / '.str_pad($mes, 2, "0", STR_PAD_LEFT),0,0,'L');
	$pdf->Ln(5);

	$pdf->Cell(8,5,'ID',1,0,'C');	
	$pdf->Cell(25,5,'CUENTA',1,0,'C');
	$pdf->Cell(20,5,'RUTA',1,0,'C');
	$pdf->Cell(20,5,'SEC. IMP',1,0,'C');
	$pdf->Cell(80,5,'DIRECCION',1,0,'C');
	$pdf->Cell(90,5,'NOMBRE',1,0,'C');
	$pdf->Cell(40,5,'TELEFONO',1,0,'C');
	$pdf->Ln();
	$pdf->SetFont('Arial','',6);

	/************************ Consulta de la informacion detallado de los usuarios **************************/	
	$query = $PDF_connect->PostgresSelectWhereOrder("maestro.log_ciclo_muni_ruta_cuentas ",
											 	"cuenta,codigo_ruta,secuencia_imp,direccion",
											 	"mes=".$mes." AND anno=".$anno." AND ciclo=".$_ruta[0]." AND ruta='".$_ruta[2]."' AND municipio='".$_ruta[1]."' AND certificar=1", "secuencia_imp");
	$item = 1;
	while($rtaQuery = pg_fetch_assoc($query)){

		$pdf->Cell(8,6,$item,1,0,'C');	
		$pdf->Cell(25,6,$rtaQuery['cuenta'],1,0,'C');
		$pdf->Cell(20,6,$_ruta[2],1,0,'C');
		$pdf->Cell(20,6,$rtaQuery['secuencia_imp'],1,0,'C');		
		//$pdf->SetFont('Arial','',6);
		$pdf->Cell(80,6,substr($rtaQuery['direccion'],0,55),1,0,'L');
		//$pdf->SetFont('Arial','',7);
		$pdf->Cell(90,6,'',1,0,'C');
		$pdf->Cell(40,6,'',1,0,'C');
		$pdf->Ln();
		$item++;
	}	

	$PDF_connect->ClosePostgres();
	$pdf->Output('Reporte.pdf', 'I');
?>

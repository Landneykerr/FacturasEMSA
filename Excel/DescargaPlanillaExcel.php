<?php
	session_start();
	include_once(dirname(__FILE__)."/../Clases/ClassPostgresBD.php");
	include_once(dirname(__FILE__)."/../Excel/PHPExcel.php");


	$_myDB =  new PostgresDB();
	$mes    	= $_GET['Mes'];
	$anno   	= $_GET['Anno'];
	$Inforuta 		= json_decode($_GET['Ruta'],true);
	$_myDB->OpenPostgres();	

	$objPHPExcel = new PHPExcel();

	
	$campos = array("N°","CODIGO","RUTA","SEC.IMP","DIRECCION","NOMBRE","TELEFONO");	
  	
	$arrayInfoRuta	= $Inforuta['Ruta'][0]['ruta'];
	$_ruta = explode("-", $arrayInfoRuta);

	$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$objPHPExcel->getProperties()->setCreator("Grupo Desarrollo Sypelc") // Nombre del autor
	    ->setLastModifiedBy("Grupo Desarrollo Sypelc SA"); //Ultimo usuario que lo modificó

	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(20);

	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A1',$campos[0])
		->setCellValue('B1',$campos[1])
		->setCellValue('C1',$campos[2])
		->setCellValue('D1',$campos[3])
		->setCellValue('E1',$campos[4])
		->setCellValue('F1',$campos[5])
		->setCellValue('G1',$campos[6]);
	
	
	$query = $_myDB->PostgresSelectWhereOrder("maestro.log_ciclo_muni_ruta_cuentas ",
											 	"cuenta,codigo_ruta,secuencia_imp,direccion",
											 	"mes=".$mes." AND anno=".$anno." AND ciclo=".$_ruta[0]." AND ruta='".$_ruta[2]."' AND municipio='".$_ruta[1]."' AND certificar=1", "secuencia_imp");

	$cuentas = pg_num_rows($query);
	
	for ($cont=2; $cont<$cuentas+2; $cont++) { 
		$row 		= pg_fetch_assoc($query, $cont-2);
		$codigo		= $row['cuenta'];
		$ruta 		= $row['codigo_ruta'];
		$secuencia 	= $row['secuencia_imp'];
		$direccion 	= $row['direccion'];
		
		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue("A".$cont, $cont-1)
			->setCellValue("B".$cont, $codigo)
			->setCellValue('C'.$cont, $ruta)
			->setCellValue('D'.$cont, $secuencia)
			->setCellValue('E'.$cont, $direccion);
		$objPHPExcel->getActiveSheet()->getRowDimension($cont)->setRowHeight(20);
	}			

	for($i='A'; $i<='E'; $i++){
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}
	
	$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('E')->setWidth(60);
	$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('F')->setWidth(60);
	$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('G')->setWidth(30);
	
	
	$align_col=$cuentas+1;

	$objPHPExcel->getActiveSheet()->getStyle('A2:A'.$align_col)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	
	$objPHPExcel->getActiveSheet()->getStyle('B2:B'.$align_col)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$objPHPExcel->getActiveSheet()->getStyle('C2:C'.$align_col)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$objPHPExcel->getActiveSheet()->getStyle('D2:D'.$align_col)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

	$objPHPExcel->getActiveSheet()->getStyle('E2:E'.$align_col)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);


	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Listado Zona');
	 
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);

	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Certificar Facturas.xlsx"');
	header('Cache-Control: max-age=0');
	 
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>
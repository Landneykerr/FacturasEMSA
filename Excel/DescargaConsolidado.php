<?php
	session_start();
	include_once(dirname(__FILE__)."/../Clases/ClassPostgresBD.php");
	include_once(dirname(__FILE__)."/../Excel/PHPExcel.php");


	$_myDataBase =  new PostgresDB();
	$mes    	= $_GET['Mes'];
	$anno       = $_GET['Anno'];
	//$ciclo  	= $_GET['Ciclo'];
	$nombreArchivo = "Consolidado_".$mes."_".$anno.".xlsx";
	

	ini_set("memory_limit", "512M");
	$objPHPExcel = new PHPExcel(); 
	//Alinear casillas
	$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getProperties()->setCreator("Grupo Desarrollo Sypelc") // Nombre del autor
		    ->setLastModifiedBy("Grupo Desarrollo Sypelc"); //Ultimo usuario que lo modificó
	
	for($i='A'; $i<='G'; $i++){
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}
	$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension('H')->setWidth(50);

	
	$fila = 1;
	$campos = array("CUENTA","CICLO","FECHA","LONGITUD","LATITUD","DISTANCIA","INSPECTOR","MENSAJE");	
	for($i=0; $i<count($campos); $i++){
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow($i,1,$campos[$i]); 
	}  
	$fila++;

	$_myDataBase->OpenPostgres();	
	$_query = $_myDataBase->PostgresFunctionCamposTable("cuenta, ciclo, fecha_entrega, longitud, latitud, distancia, nombre, mensaje","toma.toma_consolidado(".$mes.",".$anno.")"); 

	while($RtaRow = pg_fetch_array($_query, $fila-2)){
		for($i=0; $i<count($RtaRow); $i++){
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueExplicitByColumnAndRow($i, $fila, $RtaRow[$i], PHPExcel_Cell_DataType::TYPE_STRING);
		}
		$fila++;
	}
	$_myDataBase->ClosePostgres();

	$objPHPExcel->getActiveSheet()->setTitle("Consolidado"); 
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007'); 
	$objWriter->save($nombreArchivo);   

	header( "Content-Type: application/force-download"); 
	header( "Content-Length: ".filesize($nombreArchivo)); 
	header( "Content-Disposition: attachment; filename=".basename($nombreArchivo)); 
	readfile($nombreArchivo);	
	unlink($nombreArchivo);
?>
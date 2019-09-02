<?php
	session_start();
	include_once(dirname(__FILE__)."/../Clases/ClassPostgresBDFotos.php");
	include_once(dirname(__FILE__)."/../Excel/PHPExcel.php");


	$_myDataBase =  new PostgresBDFotos();
	$_mes    	 = $_GET['Mes'];
	$_anno       = $_GET['Anno'];
	$_ciclo      = $_GET['Ciclo'];
	
	ini_set("memory_limit", "512M");
	$prueba = new PHPExcel(); 
	
	$campos = array("CUENTA","NOMBRE FOTO","INSPECTOR","FECHA TOMA","FECHA DESCARGA","ESTADO","USUARIO");
		
	$prueba = new PHPExcel(); 
	for($i=0; $i<count($campos); $i++){
		$prueba->setActiveSheetIndex(0)->setCellValueByColumnAndRow($i,1,$campos[$i]); 
	}    

	$_myDataBase->OpenPostgres();	
			
    $_query = $_myDataBase->PostgresSelectJoinWhereOrder("registro.cuentas AS a", 
														 "a.cuenta,b.nombre_foto,a.inspector,b.fecha_toma::date,b.fecha_recepcion::date,a.estado,a.usuario", 
														 "registro.fotos AS b", 
														 "a.id_serial = b.id_cuenta",
														 "mes =".$_mes." AND anno = ".$_anno." AND id_ciclo = ".$_ciclo, 
														 "cuenta");	

	$fila = 2;
	while($RtaRow = pg_fetch_array($_query)){
		for($i=0; $i<count($RtaRow); $i++){
			$prueba->setActiveSheetIndex(0)->setCellValueExplicitByColumnAndRow($i, $fila, $RtaRow[$i], PHPExcel_Cell_DataType::TYPE_STRING); 
		}
		$fila++;
	}
	$_myDataBase->ClosePostgres();

	$prueba->getActiveSheet()->setTitle("Relacionfotos"); 
	$objWriter = PHPExcel_IOFactory::createWriter($prueba, 'Excel2007'); 
	$objWriter->save("Cronologico_".$_ciclo."_".$_mes."_".$_anno.".xlsx");   

	header( "Content-Type: application/force-download"); 
	header( "Content-Length: ".filesize("Cronologico_".$_ciclo."_".$_mes."_".$_anno.".xlsx")); 
	header( "Content-Disposition: attachment; filename=".basename("Cronologico_".$_ciclo."_".$_mes."_".$_anno.".xlsx")); 
	readfile("Cronologico_".$_ciclo."_".$_mes."_".$_anno.".xlsx");	
	unlink("Cronologico_".$_ciclo."_".$_mes."_".$_anno.".xlsx");
?>
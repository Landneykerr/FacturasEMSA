<?php
	
  include_once(dirname(__FILE__)."/../Clases/ClassPostgresBD.php");

	switch($_POST['Peticion'])
	{
		case 'UploadTrabajo':       
			UploadTrabajo($_POST['informacion'],$_POST['origen']);                    
			break;
	};


	function UploadTrabajo($json,$origen_lectura)
	{
		$stringInfoInsertados="";
		$fotosRecibidas="";
		$postgresWS = new PostgresDB();
		$postgresWS->OpenPostgres();
			
		$request = json_decode($json,true);
		$file = fopen("uploads/".str_replace(":","_",$origen_lectura).".txt", "a") or die("No se pudo generar el archivo");	


		for($i=0;$i<count($request['informacion']);$i++)
		{   
			$queryRespuesta = $postgresWS->PostgresFunction("maestro.recibir_toma_factura(".$request['informacion'][$i]['id'].",".$request['informacion'][$i]['id_programacion'].",".$request['informacion'][$i]['cuenta'].",'".$request['informacion'][$i]['mensaje']."','".$request['informacion'][$i]['longitud']."','".$request['informacion'][$i]['latitud']."','".$request['informacion'][$i]['fecha_entrega']."',".$request['informacion'][$i]['id_inspector'].",'".$origen_lectura."',".$request['informacion'][$i]['distancia'].")");
	        
				if($queryRespuesta == $request['informacion'][$i]['id'])
				{
																	
					if(count($request['informacion'][$i]['fotos'])>0)
					{
						$postgresWS->setConexion("fotos");
						$postgresWS->OpenPostgres();			

						for($j=0;$j<count($request['informacion'][$i]['fotos']);$j++){


		                	$imagen = base64_to_jpeg($request['informacion'][$i]['fotos'][$j]['foto'],$request['informacion'][$i]['fotos'][$j]['nombre_foto']); 

		                	$im = file_get_contents($imagen);
		                	$im = new Imagick();
		                	$dibujo = new ImagickDraw();
		                	$im->readimage($imagen);
		                	$im->thumbnailImage(620,430,true);

		                	$dibujo->setFillColor('orange');
							$dibujo->setFont('Bookman-DemiItalic');
		                	$dibujo->setFontSize(20);
							$im->annotateImage($dibujo,340,405,0,$request['informacion'][$i]['fecha_entrega']);
							$im->setImageFormat("jpg");
		                	$imdata = base64_encode($im); 
		                								
							$respuesta = $postgresWS->PostgresFunction("registro.recibir_foto(".$request['informacion'][$i]['ciclo'].",'".$request['informacion'][$i]['cuenta']."',".$request['informacion'][$i]['mes'].",".$request['informacion'][$i]['anno'].",".$request['informacion'][$i]['id_inspector'].",'".$request['informacion'][$i]['fecha_entrega']."','".$imdata."','".$request['informacion'][$i]['fotos'][$j]['nombre_foto']."')");	

							if($respuesta=="TRUE"){
								$fotosRecibidas  = $request['informacion'][$i]['fotos'][$j]['nombre_foto']."|".	$fotosRecibidas;
							}
						
							unlink($request['informacion'][$i]['fotos'][$j]['nombre_foto']);							

						}
						
						$postgresWS->setDefaultConexion();      
						$postgresWS->OpenPostgres();									
					}											
					
					$stringInfoInsertados=$request['informacion'][$i]['id']."|".$stringInfoInsertados;	
																
				}else{
					$stringInfoInsertados = "-".$request['informacion'][$i]['id']."|".$stringInfoInsertados; 	
				}  
		}	

		$stringRespuesta = $stringInfoInsertados."-".$fotosRecibidas;

		$postgresWS->ClosePostgres();
		echo $stringInfoInsertados;
	}


	function base64_to_jpeg($base64_string, $output_file) {
	    $ifp = fopen($output_file, "wb"); 
	    fwrite($ifp, base64_decode(str_replace(" ", "+", $base64_string))); 
	    fclose($ifp); 

	    return $output_file; 
	}


 ?>
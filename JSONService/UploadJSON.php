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
		
		$postgresWS = new PostgresDB();
		$postgresWS->OpenPostgres();
			
		$request = json_decode($json,true);
		$file = fopen("uploads/".str_replace(":","_",$origen_lectura).".txt", "a") or die("No se pudo generar el archivo");	

		for($i=0;$i<count($request['informacion']);$i++)
		{   

			fputs($file,$request['informacion'][$i]['id']);
			fputs($file,"\n");

			$queryRespuesta = $postgresWS->PostgresFunction("maestro.recibir_toma_factura(".$request['informacion'][$i]['id'].",".$request['informacion'][$i]['id_programacion'].",".$request['informacion'][$i]['cuenta'].",'".$request['informacion'][$i]['mensaje']."','".$request['informacion'][$i]['longitud']."','".$request['informacion'][$i]['latitud']."','".$request['informacion'][$i]['fecha_entrega']."',".$request['informacion'][$i]['id_inspector'].",'".$origen_lectura."',".$request['informacion'][$i]['distancia'].")");
	        
				if($queryRespuesta == $request['informacion'][$i]['id'])
				{																		
					$stringInfoInsertados=$request['informacion'][$i]['id']."|".$stringInfoInsertados;	
																
				}else{
					$stringInfoInsertados = "-".$request['informacion'][$i]['id']."|".$stringInfoInsertados; 	
				}  
		}	
		
		$postgresWS->ClosePostgres();
		echo $stringInfoInsertados;
	}

 ?>
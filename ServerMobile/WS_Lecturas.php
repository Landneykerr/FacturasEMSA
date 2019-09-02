<?php


/************************************Metodos y servicios disponibles para sistema de rendimientos y mejoras al sistema estadistico***************************************/
/************************Estos servicios estan disponibles para el AmData v8.5.3.2, la cual esta en visual 2008 y la base de datos en SQLCE 3.5**************************/ 

//require_once('../WS lib/nusoap.php');
//require_once('../BaseDatos/ConsultasBD.php');

include_once(dirname(__FILE__)."/../WS lib/nusoap.php");
include_once(dirname(__FILE__)."/../Clases/ClassPostgresBD.php");
include_once(dirname(__FILE__)."/../PHP/FuncionesArchivos.php");


$server = new soap_server();
$server->configureWSDL('JavaWS', 'urn:JavaWS');	//configuracion para crear el .asmx y el .wsdl


/*****************************************************************************************************************************
*********************Registro del metodo para la transferencia de parametros desde el servidor al movil***********************
*****************************************************************************************************************************/
$server->register(	'DownLoadParametros',
					array('id_interno'=>'xsd:String'),
					array('Informacion' => 'xsd:String'),
					'xsd:JavaWS');


function DownLoadParametros($id_interno){
	$archivo = "";
	$postgresWS = new PostgresDB();
	$postgresWS->OpenPostgres();
		

	/**
		Consulta de los datos de inspectores
	**/
	$queryArchivo = $postgresWS->PostgresSelectDistinctWhereOrder(	"parametros.inspectores", 
																	"id_inspector,nombre,cedula,tipo_inspector", 
																	"estado = TRUE", 
																	"id_inspector");

	while($rtaQuery = pg_fetch_assoc($queryArchivo)){
		$archivo .= "Inspector|".$rtaQuery["id_inspector"]."|".$rtaQuery["nombre"]."|".$rtaQuery["cedula"]."|".$rtaQuery["tipo_inspector"]."\n";
	}


	/**
		Consulta de los nombre de municipios y sus respectivos codigos
	**/
	$queryArchivo = $postgresWS->PostgresSelectDistinctWhereOrder(	"parametros.municipios", 
																	"id_municipio,nombre_municipio", 
																	"id_serial IS NOT NULL", 
																	"nombre_municipio");

	while($rtaQuery = pg_fetch_assoc($queryArchivo)){
		$archivo .= "Municipio|".$rtaQuery["id_municipio"]."|".$rtaQuery["nombre_municipio"]."\n";
	}

	/**
		Consulta de las anomalias y sus casos de aplicacion
	**/
	$queryArchivo = $postgresWS->PostgresSelectDistinctWhereOrder(	"parametros.anomalias", 
																	"id_anomalia,descripcion,aplica_residencial,aplica_no_residencial,lectura,mensaje,foto", 
																	"id_serial IS NOT NULL", 
																	"id_anomalia");

	while($rtaQuery = pg_fetch_assoc($queryArchivo)){
		$archivo .= "Anomalia|".$rtaQuery["id_anomalia"]."|".$rtaQuery["descripcion"]."|".$rtaQuery["aplica_residencial"]."|".$rtaQuery["aplica_no_residencial"]."|".$rtaQuery["lectura"]."|".$rtaQuery["mensaje"]."|".$rtaQuery["foto"]."\n";
	}


	/**
		Consulta de los rangos de critica y su descripcion
	**/
	$queryArchivo = $postgresWS->PostgresSelectDistinctWhereOrder(	"parametros.critica", 
																	"rango_minimo,rango_maximo,descripcion,mensaje", 
																	"id_serial IS NOT NULL", 
																	"rango_minimo");

	while($rtaQuery = pg_fetch_assoc($queryArchivo)){
		$archivo .= "Critica|".$rtaQuery["rango_minimo"]."|".$rtaQuery["rango_maximo"]."|".$rtaQuery["descripcion"]."|".$rtaQuery["mensaje"]."\n";
	}

	/**
	Consulta de los Tipos de Uso y su descripcion
	**/

	$queryArchivo = $postgresWS->PostgresSelectDistinctWhereOrder(	"parametros.tipo_usos", 
																	"id_uso,descripcion", 
																	"id_uso IS NOT NULL", 
																	"id_uso");

	while($rtaQuery = pg_fetch_assoc($queryArchivo)){
		$archivo .= "Uso|".$rtaQuery["id_uso"]."|".$rtaQuery["descripcion"]."\n";
	}


	/**
	Consulta de los Mensajes codificados y la respectiva descripcion
	**/

	$queryArchivo = $postgresWS->PostgresSelectDistinctWhereOrder(	"parametros.codigos_mensajes", 
																	"codigo,descripcion", 
																	"codigo IS NOT NULL", 
																	"codigo");

	while($rtaQuery = pg_fetch_assoc($queryArchivo)){
		$archivo .= "Msj|".$rtaQuery["codigo"]."|".$rtaQuery["descripcion"]."\n";
	}


	$postgresWS->ClosePostgres();
		
	if (!$handle = fopen("temp_descarga_".$id_interno.".txt", "w")){   
		return "Cannot open file";  
		exit;  
	}else if (fwrite($handle, utf8_decode($archivo)) === FALSE){   
		return "Cannot write to file";  
		exit;  
	}else{									//Si se ha creado correctamente el archivo se procede a realizar los insert en la base de datos de postgres
		fclose($handle);
	}	
	    
	$picture = fread(fopen("temp_descarga_".$id_interno.".txt","rb", 0),filesize("temp_descarga_".$id_interno.".txt")); 
	$base64 = chunk_split(base64_encode($picture)); 	    
	return $base64;
}


/*****************************************************************************************************************************
***Funcion que recibe el Codigo del Inspector y le devuelve el trabajo asignado***
*****************************************************************************************************************************/
$server->register(	'DownLoadTrabajoSync',
					array('id_interno'=>'xsd:String','rutas_cargadas'=>'xsd:String','fecha_movil'=>'xsd:String','hora_movil'=>'xsd:String'),
					array('Informacion' => 'xsd:String'),
					'xsd:JavaWS');


//function DownLoadTrabajoSync($id_interno, $rutas_cargadas, $fecha_movil, $hora_movil){	
function DownLoadTrabajoSync($id_interno, $rutas_cargadas, $fecha_movil){	
	/*$seg_movil	= explode(":",$hora_movil);
	$seg_server	= explode(":",date('H:i:s'));
	$dif = ((int)$seg_server[0]*3600 + (int)$seg_server[1]*60 + (int)$seg_server[2]) - ((int)$seg_movil[0]*3600 + (int)$seg_movil[1]*60 + (int)$seg_movil[2]);
	*/
	
	/*if($fecha_movil != date('d/m/Y')){
		$archivo = "-1\n";
	}else if(abs($dif) > 300){
		$archivo = "-2\n";
	}else{*/
		$archivo = "";
		$postgresWS = new PostgresDB();
		$postgresWS->OpenPostgres();
		$archivo = "1\n";


		$queryVerificador = $postgresWS->PostgresSelectWhereOrder(	"parametros.inspectores", 
																	"tipo_inspector", 
																	"id_inspector=".$id_interno, 
																	"tipo_inspector");

		$rtaVerificador = pg_fetch_assoc($queryVerificador);

		if($rtaVerificador['tipo_inspector'] == '1'){		//Cuando el inspector que se registra es de tipo inspector
			//Se carga la informacion general de las rutas pendientes por cargar
			if($rutas_cargadas == ""){			//Si no se tienen rutas precargadas
				$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.Programacion_Maestro_Rutas(".$id_interno.")");
			}else{
				$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.Programacion_Maestro_Rutas(".$id_interno.") WHERE CAST(id_ciclo AS TEXT)||'-'||CAST(id_municipio AS TEXT)||'-'||ruta NOT IN (".$rutas_cargadas.")");
			}
			while($rtaQuery = pg_fetch_assoc($queryRespuesta)){
				$archivo .= "MaestroRutas|".$rtaQuery["id_inspector"]."|".$rtaQuery["id_ciclo"]."|".$rtaQuery["id_municipio"]."|".$rtaQuery["ruta"]."|".$rtaQuery["mes"]."|".$rtaQuery["anno"]."\n";
				//$archivo .= "MaestroRutas|".$rtaQuery["id_inspector"]."|".$rtaQuery["id_ciclo"]."|".$rtaQuery["id_municipio"]."|".$rtaQuery["ruta"]."\n";
			}

			//Se carga la informacion detallada de los clientes segun las rutas pendientes por cargar
			
			if($rutas_cargadas == ""){			//Si no se tienen rutas precargadas
				$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.programacion_by_agrupacion(".$id_interno.")");
			}else{								//Si se tienen rutas precargadas
				$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.programacion_by_agrupacion(".$id_interno.") WHERE CAST(id_ciclo AS TEXT)||'-'||CAST(id_municipio AS TEXT)||'-'||ruta NOT IN (".$rutas_cargadas.")");	
			} 
			while($rtaQuery = pg_fetch_assoc($queryRespuesta)){
				$archivo .= "MaestroClientes|".$rtaQuery["id"]."|".$rtaQuery["id_ciclo"]."|".$rtaQuery["mes"]."|".$rtaQuery["anno"]."|".$rtaQuery["ruta"]."|".$rtaQuery["cuenta"]."|".$rtaQuery["medidor"]."|".$rtaQuery["serie"]."|".$rtaQuery["digitos"]."|".$rtaQuery["nombre"]."|".$rtaQuery["direccion"]."|".$rtaQuery["factor"]."|".$rtaQuery["tipo_uso"]."|".$rtaQuery["id_serial_1"]."|".$rtaQuery["lectura_1"]."|".$rtaQuery["tipo_energia_1"]."|".$rtaQuery["anomalia_1"]."|".$rtaQuery["promedio_1"]."|".$rtaQuery["id_serial_2"]."|".$rtaQuery["lectura_2"]."|".$rtaQuery["tipo_energia_2"]."|".$rtaQuery["anomalia_2"]."|".$rtaQuery["promedio_2"]."|".$rtaQuery["id_serial_3"]."|".$rtaQuery["lectura_3"]."|".$rtaQuery["tipo_energia_3"]."|".$rtaQuery["anomalia_3"]."|".$rtaQuery["promedio_3"]."|".$rtaQuery["id_municipio"]."|".$rtaQuery["estado_lectura"]."\n";	
				//$archivo .= "MaestroClientes|".$rtaQuery["id"]."|".$rtaQuery["id_ciclo"]."|".$rtaQuery["ruta"]."|".$rtaQuery["cuenta"]."|".$rtaQuery["medidor"]."|".$rtaQuery["serie"]."|".$rtaQuery["digitos"]."|".$rtaQuery["nombre"]."|".$rtaQuery["direccion"]."|".$rtaQuery["factor"]."|".$rtaQuery["tipo_uso"]."|".$rtaQuery["id_serial_1"]."|".$rtaQuery["lectura_1"]."|".$rtaQuery["tipo_energia_1"]."|".$rtaQuery["anomalia_1"]."|".$rtaQuery["promedio_1"]."|".$rtaQuery["id_serial_2"]."|".$rtaQuery["lectura_2"]."|".$rtaQuery["tipo_energia_2"]."|".$rtaQuery["anomalia_2"]."|".$rtaQuery["promedio_2"]."|".$rtaQuery["id_serial_3"]."|".$rtaQuery["lectura_3"]."|".$rtaQuery["tipo_energia_3"]."|".$rtaQuery["anomalia_3"]."|".$rtaQuery["promedio_3"]."|".$rtaQuery["id_municipio"]."|".$rtaQuery["estado_lectura"]."\n";	
			}


			//Se carga las rutas que estan asignadas el inspector y que ya estan en el servidor en estado terminado para su respectiva eliminacion en el movil
			if($rutas_cargadas == ""){			//Si no se tienen rutas precargadas
				$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.Rutas_Terminadas_Inspector(".$id_interno.")");
			}else{
				$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.Rutas_Terminadas_Inspector(".$id_interno.") WHERE CAST(id_ciclo AS TEXT)||'-'||CAST(id_municipio AS TEXT)||'-'||ruta IN (".$rutas_cargadas.")");
			}
			while($rtaQuery = pg_fetch_assoc($queryRespuesta)){
				$archivo .= "MaestroRutasTerminadas|".$rtaQuery["id_inspector"]."|".$rtaQuery["id_ciclo"]."|".$rtaQuery["id_municipio"]."|".$rtaQuery["ruta"]."|".$rtaQuery["mes"]."|".$rtaQuery["anno"]."\n";
				//$archivo .= "MaestroRutasTerminadas|".$rtaQuery["id_inspector"]."|".$rtaQuery["id_ciclo"]."|".$rtaQuery["id_municipio"]."|".$rtaQuery["ruta"]."\n";
			}
		}else{						//Cuando el inspector que se registra es de tipo verificador
			$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.Verificacion_Maestro_Rutas(".$id_interno.")");
			while($rtaQuery = pg_fetch_assoc($queryRespuesta)){
				$archivo .= "MaestroRutas|".$rtaQuery["id_inspector"]."|".$rtaQuery["id_ciclo"]."|".$rtaQuery["id_municipio"]."|".$rtaQuery["ruta"]."|".$rtaQuery["mes"]."|".$rtaQuery["anno"]."\n";
			}

			$queryRespuesta = $postgresWS->PostgresFunctionCamposTableOrder("id,id_ciclo,ruta,cuenta,medidor,serie,digitos,nombre,direccion,factor,tipo_uso,id_serial_1,lectura_1,tipo_energia_1,anomalia_1,promedio_1,id_serial_2,lectura_2,tipo_energia_2,anomalia_2,promedio_2,id_serial_3,lectura_3,tipo_energia_3,anomalia_3,promedio_3,id_municipio,estado_lectura","maestro.verificacion_by_agrupacion(".$id_interno.")","ruta_real, secuencia_ruta");
			$i = 1;
			while($rtaQuery = pg_fetch_assoc($queryRespuesta)){
				$archivo .= "MaestroClientes|".$i."|".$rtaQuery["id_ciclo"]."|".$rtaQuery["mes"]."|".$rtaQuery["anno"]."|".$rtaQuery["ruta"]."|".$rtaQuery["cuenta"]."|".$rtaQuery["medidor"]."|".$rtaQuery["serie"]."|".$rtaQuery["digitos"]."|".$rtaQuery["nombre"]."|".$rtaQuery["direccion"]."|".$rtaQuery["factor"]."|".$rtaQuery["tipo_uso"]."|".$rtaQuery["id_serial_1"]."|".$rtaQuery["lectura_1"]."|".$rtaQuery["tipo_energia_1"]."|".$rtaQuery["anomalia_1"]."|".$rtaQuery["promedio_1"]."|".$rtaQuery["id_serial_2"]."|".$rtaQuery["lectura_2"]."|".$rtaQuery["tipo_energia_2"]."|".$rtaQuery["anomalia_2"]."|".$rtaQuery["promedio_2"]."|".$rtaQuery["id_serial_3"]."|".$rtaQuery["lectura_3"]."|".$rtaQuery["tipo_energia_3"]."|".$rtaQuery["anomalia_3"]."|".$rtaQuery["promedio_3"]."|".$rtaQuery["id_municipio"]."|".$rtaQuery["estado_lectura"]."\n";		
				$i++;
			}
		}
		

		$archivo = str_replace("&",'Y',$archivo);
		$archivo = str_replace("<",' ',$archivo);
		$archivo = str_replace(">",' ',$archivo);

	    $postgresWS->ClosePostgres();

	    if (!$handle = fopen("download_trabajo_".$id_interno.".txt", "w")){   
			return "Cannot open file";  
			exit;  
		}else if (fwrite($handle, utf8_decode($archivo)) === FALSE){   
			return "Cannot write to file";  
			exit;  
		}else{									
			fclose($handle);
		}	


	//}
   	return $archivo;
}








/*****************************************************************************************************************************
***Funcion que recibe el Codigo del Inspector y le devuelve el trabajo asignado***
*****************************************************************************************************************************/
$server->register(	'DownLoadTrabajo',
					array('id_interno'=>'xsd:String','rutas_cargadas'=>'xsd:String'),
					array('Informacion' => 'xsd:String'),
					'xsd:JavaWS');


function DownLoadTrabajo($Id_interno,$rutas_cargadas){
	$archivo = "";
	$postgresWS = new PostgresDB();
	$postgresWS->OpenPostgres();
	
	/**
	Se carga la informacion general de las rutas pendientes por cargar
	**/
	if($rutas_cargadas == ""){			//Si no se tienen rutas precargadas
		$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.Programacion_Maestro_Rutas(".$Id_interno.")");
	}else{
		$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.Programacion_Maestro_Rutas(".$Id_interno.") WHERE CAST(id_ciclo AS TEXT)||'-'||CAST(id_municipio AS TEXT)||'-'||ruta NOT IN (".$rutas_cargadas.")");
	}
	while($rtaQuery = pg_fetch_assoc($queryRespuesta)){
		$archivo .= "MaestroRutas|".$rtaQuery["id_inspector"]."|".$rtaQuery["id_ciclo"]."|".$rtaQuery["id_municipio"]."|".$rtaQuery["ruta"]."\n";
	}

	/**
	Se carga la informacion detallada de los clientes segun las rutas pendientes por cargar
	**/
	if($rutas_cargadas == ""){			//Si no se tienen rutas precargadas
		$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.programacion_by_agrupacion(".$Id_interno.")");
	}else{								//Si se tienen rutas precargadas
		$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.programacion_by_agrupacion(".$Id_interno.") WHERE CAST(id_ciclo AS TEXT)||'-'||CAST(id_municipio AS TEXT)||'-'||ruta NOT IN (".$rutas_cargadas.")");	
	} 
	while($rtaQuery = pg_fetch_assoc($queryRespuesta)){
		$archivo .= "MaestroClientes|".$rtaQuery["id"]."|".$rtaQuery["id_ciclo"]."|".$rtaQuery["ruta"]."|".$rtaQuery["cuenta"]."|".$rtaQuery["medidor"]."|".$rtaQuery["serie"]."|".$rtaQuery["digitos"]."|".$rtaQuery["nombre"]."|".$rtaQuery["direccion"]."|".$rtaQuery["factor"]."|".$rtaQuery["tipo_uso"]."|".$rtaQuery["id_serial_1"]."|".$rtaQuery["lectura_1"]."|".$rtaQuery["tipo_energia_1"]."|".$rtaQuery["anomalia_1"]."|".$rtaQuery["promedio_1"]."|".$rtaQuery["id_serial_2"]."|".$rtaQuery["lectura_2"]."|".$rtaQuery["tipo_energia_2"]."|".$rtaQuery["anomalia_2"]."|".$rtaQuery["promedio_2"]."|".$rtaQuery["id_serial_3"]."|".$rtaQuery["lectura_3"]."|".$rtaQuery["tipo_energia_3"]."|".$rtaQuery["anomalia_3"]."|".$rtaQuery["promedio_3"]."|".$rtaQuery["id_municipio"]."|".$rtaQuery["estado_lectura"]."\n";	
	}


	/**
	Se carga las rutas que estan asignadas el inspector y que ya estan en el servidor en estado terminado para su respectiva eliminacion en el movil
	**/
	if($rutas_cargadas == ""){			//Si no se tienen rutas precargadas
		$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.Rutas_Terminadas_Inspector(".$Id_interno.")");
	}else{
		$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.Rutas_Terminadas_Inspector(".$Id_interno.") WHERE CAST(id_ciclo AS TEXT)||'-'||CAST(id_municipio AS TEXT)||'-'||ruta IN (".$rutas_cargadas.")");
	}
	while($rtaQuery = pg_fetch_assoc($queryRespuesta)){
		$archivo .= "MaestroRutasTerminadas|".$rtaQuery["id_inspector"]."|".$rtaQuery["id_ciclo"]."|".$rtaQuery["id_municipio"]."|".$rtaQuery["ruta"]."\n";
	}


    $postgresWS->ClosePostgres();
    
	if (!$handle = fopen("temp_trabajo_".$Id_interno.".txt", "w")){   
		return "Cannot open file";  
		exit;  
	}else if (fwrite($handle, utf8_decode($archivo)) === FALSE){   
		return "Cannot write to file";  
		exit;  
	}else{									
		fclose($handle);
	}	

	$picture = fread(fopen("temp_trabajo_".$Id_interno.".txt","rb", 0),filesize("temp_trabajo_".$Id_interno.".txt")); 
	$base64 = chunk_split(base64_encode($picture)); 	    
	return $base64;
}

/*********************************************************************************************************************************************
***Registro del metodo que recibe un archivo txt con todas las notificaciones y revisiones que se han realizado y las carga  en el servidor***
*********************************************************************************************************************************************/
$server->register(	'UploadTrabajo',
					array('usuario ' => 'xsd:String', 'informacion' => 'xsd:base64Binary', 'bluetooth'=>'xsd:String'),
					array('retorno'=>'xsd:String'),
					'xsd:JavaWS');


function UploadTrabajo($Usuario, $Informacion, $Bluetooth){
	$stringInfoInsertados="";
	$postgresWS = new PostgresDB();
	$postgresWS->OpenPostgres();
	
	$destino = dirname(__FILE__)."/../Files/Upload/".$Usuario.".txt";
	$subido=fopen($destino, 'w');
	fwrite($subido,$Informacion);
 	fclose($subido);

	$file = fopen($destino,'r') or exit("Error abriendo fichero!");
       while($linea = fgets($file)) {
           if (feof($file)) break;
              $info = explode(",", $linea);
               
            	$id_serial1 	=  $info[1];
              	$lectura1 		=  $info[2];
              	$critica1 		=  $info[3];
              	$id_serial2 	=  $info[4];
              	$lectura2   	=  $info[5];
              	$critica2 		=  $info[6];
              	$id_serial3 	=  $info[7];
              	$lectura3 		=  $info[8];
              	$critica3 		=  $info[9];
              	$anomalia 		=  $info[10];
              	$mensaje 		=  $info[11];
              	$tipo_uso 		=  $info[12];
              	$fecha_toma 	=  $info[13];
              	$longitud		=  $info[14];
              	$latitud		=  $info[15];
              	 
				//$queryRespuesta = $postgresWS->PostgresFunction("maestro.recibir_toma_lectura(".$id_serial1.",".$lectura1.",".$critica1.",".$id_serial2.",".$lectura2.",".$critica2.",".$id_serial3.",".$lectura3.",".$critica3.",".$anomalia.",'".$mensaje."','".$tipo_uso."',".$Usuario.",'".$fecha_toma."')");
				$queryRespuesta = $postgresWS->PostgresFunction("maestro.recibir_toma_lectura(".$id_serial1.",".$lectura1.",".$critica1.",".$id_serial2.",".$lectura2.",".$critica2.",".$id_serial3.",".$lectura3.",".$critica3.",".$anomalia.",'".$mensaje."','".$tipo_uso."',".$Usuario.",'".$fecha_toma."',".$longitud.",".$latitud.",'".$Bluetooth."')");
				if($queryRespuesta == 0){
					$stringInfoInsertados=$info[0]."|".$stringInfoInsertados;
				}        																					
            }	

           fclose($file);

	$postgresWS->ClosePostgres();

	//$base64 = base64_encode(gzcompress(serialize($arrayInsertados)));
	return $stringInfoInsertados;
}



/*********************************************************************************************************************************************
***Registro del metodo que recibe un archivo txt con todas las notificaciones y revisiones que se han realizado y las carga  en el servidor***
*********************************************************************************************************************************************/
$server->register(	'UploadTrabajoExplicitInspector',
					array('informacion' => 'xsd:base64Binary', 'bluetooth'=>'xsd:String'),
					array('retorno'=>'xsd:String'),
					'xsd:JavaWS');


function UploadTrabajoExplicitInspector($Informacion, $Bluetooth){
	$stringInfoInsertados="";
	//$postgresWS	= new PostgresDB('192.168.1.37','5432','postgres','t3st3r','lecturas');
	//$postgresWS = new PostgresDB('192.168.0.51','5432','test_lecturas','t3st3r','lecturas');
	$postgresWS = new PostgresDB();
	$postgresWS->OpenPostgres();
	
	$destino = dirname(__FILE__)."/../Files/Upload/".str_replace(":","_",$Bluetooth).".txt";
	$subido=fopen($destino, 'w');
	fwrite($subido,$Informacion);
 	fclose($subido);

	$file = fopen($destino,'r') or exit("Error abriendo fichero!");
       	while($linea = fgets($file)) {
           	if (feof($file)) break;
              
            $info = explode(",", $linea);
               
           	$id_serial1 	=  $info[1];
            $lectura1 		=  $info[2];
            $critica1 		=  $info[3];
            $id_serial2 	=  $info[4];
            $lectura2   	=  $info[5];
            $critica2 		=  $info[6];
            $id_serial3 	=  $info[7];
            $lectura3 		=  $info[8];
            $critica3 		=  $info[9];
            $anomalia 		=  $info[10];
            $mensaje 		=  $info[11];
            $tipo_uso 		=  $info[12];
            $fecha_toma 	=  $info[13];
            $longitud		=  $info[14];
            $latitud		=  $info[15];
            $id_inspector	=  $info[16];
              	 
			//$queryRespuesta = $postgresWS->PostgresFunction("maestro.recibir_toma_lectura(".$id_serial1.",".$lectura1.",".$critica1.",".$id_serial2.",".$lectura2.",".$critica2.",".$id_serial3.",".$lectura3.",".$critica3.",".$anomalia.",'".$mensaje."','".$tipo_uso."',".$Usuario.",'".$fecha_toma."')");
			$queryRespuesta = $postgresWS->PostgresFunction("maestro.recibir_toma_lectura(".$id_serial1.",".$lectura1.",".$critica1.",".$id_serial2.",".$lectura2.",".$critica2.",".$id_serial3.",".$lectura3.",".$critica3.",".$anomalia.",'".$mensaje."','".$tipo_uso."',".$id_inspector.",'".$fecha_toma."',".$longitud.",".$latitud.",'".$Bluetooth."')");
			if($queryRespuesta == $id_serial1){
				$stringInfoInsertados=$info[0]."|".$stringInfoInsertados;
			}        																					
        }	
        fclose($file);
	$postgresWS->ClosePostgres();

	//$base64 = base64_encode(gzcompress(serialize($arrayInsertados)));
	return $stringInfoInsertados;
}  


/*****************************************************************************************************************************
***Metodo que recibe el registro fotografico de la Lecturas***
*****************************************************************************************************************************/
$server->register(	'FotoTomada_old',
					array('cuenta' => 'xsd:String','id_memsa' => 'xsd:String','usuario' => 'xsd:String','informacion' => 'xsd:base64Binary'),
					array('retorno'=>'xsd:String'),
					'xsd:JavaWS');


function FotoTomada_old($cuenta,$id_memsa,$usuario,$informacion){	
	if(!file_exists('../TreeFiles/FotosActas/'.$cuenta)){
		mkdir('../TreeFiles/FotosActas/'.$cuenta);
	}
	$ruta = "../TreeFiles/FotosActas/".$cuenta."/";

	
	$postgresWS = new PostgresDB();
	$postgresWS->OpenPostgres();
	$Query 		= $postgresWS->PostgresFunction("toma.registrofotografia('".date('d-m-Y')."',".$cuenta.",".$id_memsa.",'".$ruta."',".$usuario.") ");

	$subido = fopen("../TreeFiles/FotosActas/".$cuenta."/Foto_".$cuenta.".jpg",'w');	
 	fwrite($subido,$informacion);
	$foto_small = resizeImage("../TreeFiles/FotosActas/".$cuenta."/Foto_".$Query.".jpg",450,450);	
 	imagejpeg($foto_small,"../TreeFiles/FotosActas/".$cuenta."/Foto_".$Query.".jpg",100);

 	if($subido){
 		$ValorRetorno = "1";
 	}
 	else{
 		$ValorRetorno = "0";
 	}
 	fclose($subido);
 	$postgresWS->ClosePostgres(); 
 	$ValorRetorno = "1";
	return $ValorRetorno;
}

function resizeImage($originalImage,$toWidth,$toHeight){
  
  list($width, $height) = getimagesize($originalImage);
  
  $xscale=$width/$toWidth;
  $yscale=$height/$toHeight;
  
  if ($yscale>$xscale){
    $new_width = round($width * (1/$yscale));
    $new_height = round($height * (1/$yscale));
      
  } else {
    $new_width = round($width * (1/$xscale));
    $new_height = round($height * (1/$xscale));
  }
    
  $imageResized = imagecreatetruecolor($new_width, $new_height);
  
  $imageTmp     = imagecreatefromjpeg ($originalImage);
  imagecopyresampled($imageResized, $imageTmp,
    0, 0, 0, 0, $new_width, $new_height, $width, $height);

  return $imageResized;
}




/*****************************************************************************************************************************
***Registro del metodo que recibe un archivo txt con todas las desviaciones que se han realizado y las devuelve al servidor***
*****************************************************************************************************************************/
$server->register(	'UpLoadFotos',
					array('Solicitud'=>'xsd:String', 'NombreFoto'=>'xsd:String', 'Foto' => 'xsd:base64Binary'),
					array('retorno'=> 'xsd:String'),
					'xsd:JavaWS');


function UpLoadFotos($Solicitud, $NombreFoto, $Foto){
	if(!file_exists('../Files/Fotos/'.$Solicitud)){
		mkdir('../Files/Fotos/'.$Solicitud);
	}
	$total_imagenes = count(glob('../Files/Fotos/'.$Solicitud.'/{*.jpeg,*.jpg,*.gif,*.png}',GLOB_BRACE));
	if($subido = fopen('../Files/Fotos/'.$Solicitud.'/'.$Solicitud."_".$total_imagenes.".jpeg",'w')){
		fwrite($subido,$Foto);
 		fclose($subido);
		$retorno = $NombreFoto;
	}else{
		$retorno = $NombreFoto.'-Fail';
	}
 	
	return $retorno;
}


// Use the request to (try to) invoke the service
$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$server->service($HTTP_RAW_POST_DATA);
?>
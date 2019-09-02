<?php
	session_start();

	include_once(dirname(__FILE__)."/../Clases/ClassPostgresBD.php");
	
	switch($_POST['Peticion']){
		case 'Parametros':  				
			DescargarParametros($_POST['Inspector']);
			break;			

		case 'Trabajo': 					
			DescargarTrabajo($_POST['Inspector'], $_POST['RutasFacturas']);	
			break;


		case 'UploadErrorPrinter':          
			LoadErrorPrinter($_POST['datosimpresion']);
			break;


		case 'SyncDate':          			
			SyncDate($_POST['bluetooth'], $_POST['fecha_hora']);
			break;	
	}



	function SyncDate($_bluetooth, $fecha_hora){
		$date 	= new DateTime(); // Fecha actual
		$date2 	= explode(" ", $fecha_hora); // Segunda fecha
		$date->setTimeZone( new DateTimeZone('America/Bogota')); // Definimos seTimeZone para asegurarnos de que sea la hora actual del lugar donde estamos


		if($date->format('d/m/Y') != $date2[0]){
			echo "Bad_1|".$date->format("d/m/Y H:i:s")."|".$fecha_hora;
		}else{
			$hora_server	= explode(":", $date->format('H:i:s'));
			$hora_movil 	= explode(":", $date2[1]);

			$segServer 	= ((int)$hora_server[0]*3600) + ((int)$hora_server[1]*60) + (int)$hora_server;
			$segMovil 	= ((int)$hora_movil[0]*3600) + ((int)$hora_movil[1]*60) + (int)$hora_movil;

			if(abs($segServer-$segMovil)>300){
				echo "Bad_2|".$date->format("d/m/Y H:i:s")."|".$fecha_hora;
			}else{
				echo "Ok_1|".$date->format("d/m/Y H:i:s")."|".$fecha_hora;
			}
		}
	}


	function LoadErrorPrinter($_datos){
		$postgresWS = new PostgresDB();
		$postgresWS->OpenPostgres();

		$datosImpresion = json_decode($_datos, true);
		$data = array();
		$k=0;
		for($i=0;$i<count($datosImpresion['Impresion']);$i++){
			if($postgresWS->PostgresInsertIntoValues("toma.registro_impresion",
					"cuenta,id_inspector,error,fecha_impresion",
					$datosImpresion['Impresion'][$i]['cuenta'].",".$datosImpresion['Impresion'][$i]['id_inspector'].",'".$datosImpresion['Impresion'][$i]['error']."','".$datosImpresion['Impresion'][$i]['fecha_toma']."'")){
				$data[$k]['id'] = $datosImpresion['Impresion'][$i]['id'];
				$k++;
			}
		}
		$postgresWS->ClosePostgres();
		echo json_encode($data);
	}


	function DescargarParametros($_inspector){
		$postgresWS = new PostgresDB();
		$postgresWS->OpenPostgres();
			
		$informacion = array();
		$data = array();
		/**
			Consulta de los datos de inspectores
		**/
		$queryArchivo = $postgresWS->PostgresSelectDistinctWhereOrder(	"parametros.inspectores", 
																		"id_inspector,nombre,cedula", 
																		"estado = TRUE", 
																		"nombre");
		$data['Inspectores'] = $postgresWS->QueryToJson($queryArchivo,["id_inspector","nombre","cedula"],[null,null,null],true);

		$queryArchivo = $postgresWS->PostgresSelectDistinctWhereOrder(	"parametros.mensajes", 
																		"codigo, mensaje", 
																		"codigo is NOT NULL", 
																		"codigo");
		$data['Mensajes'] = $postgresWS->QueryToJson($queryArchivo,["codigo", "mensaje"],[null,null],true);
		

		$queryArchivo = $postgresWS->PostgresSelectDistinctWhereOrder(	"parametros.distancia", 
																		"id_serial, distancia", 
																		"distancia is NOT NULL", 
																		"distancia");
		$data['Distancia'] = $postgresWS->QueryToJson($queryArchivo,["id_serial","distancia"],[null,null],true);

		$informacion = $data;
		$postgresWS->ClosePostgres();
		echo json_encode($informacion);
	}


	function DescargarTrabajo($_inspector, $_rutasLecturas){
		$postgresWS = new PostgresDB();
		$postgresWS->OpenPostgres();

		if($_rutas_cargadas == ""){
			$_rutas_cargadas = "''";
		}


		//Se carga la informacion general de las rutas pendientes por cargar
		$queryRutas = $postgresWS->PostgresFunctionTable("maestro.Programacion_Maestro_Rutas(".$_inspector.") WHERE id_programacion NOT IN (".$_rutasLecturas.")");
		$k = 0;
		$informacion = array();
		$data = array();
		
		while($infRuta = pg_fetch_assoc($queryRutas)){
			$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.programacion_by_agrupacion(".$infRuta['ciclo'].",'".$infRuta['municipio']."','".$infRuta['ruta']."',".$infRuta['mes'].",".$infRuta['anno'].")");	
			
			$data[$k]['id_programacion']= $infRuta['id_programacion'];
			$data[$k]['id_inspector'] 	= $infRuta['id_inspector'];
			$data[$k]['ciclo'] 			= $infRuta['ciclo'];
			$data[$k]['municipio'] 		= $infRuta['municipio'];
			$data[$k]['ruta'] 			= $infRuta['ruta'];
			$data[$k]['mes'] 			= $infRuta['mes'];
			$data[$k]['anno'] 			= $infRuta['anno'];

			$data[$k]['cuentas'] = $postgresWS->QueryToJson($queryRespuesta,["id","cuenta","nombre","direccion","marca_con","numero_con","sec_imp","sec_ruta","estado","codigo_ruta"],[null,null,null,null,null,null,null,null,null,null],true);

			$array = array();

			for($l=0; $l<pg_num_rows($queryRespuesta); $l++){
				$row = pg_fetch_assoc($queryRespuesta, $l);
				$cuenta_gps = $row['cuenta'];
				$postgresWS->setConexion('lecturas');	
				$postgresWS->OpenPostgres();

				$mes_gps 	= $infRuta['mes']-1;
				$anno_gps 	= $infRuta['anno'];

				if($mes_gps==1){
					$mes_gps  = 12;
					$anno_gps = $anno_gps-1;
				}

				$queryGPS = $postgresWS->PostgresSelectJoinWhereOrder("toma.lectura AS a", "latitud,longitud", "maestro.log_ciclo_muni_ruta_cuentas AS b", "a.id_maestro_emsa=b.id_serial", "usuario='ServiceMovil' AND b.mes=".$mes_gps." AND b.anno=".$anno_gps." AND b.cuenta=".$cuenta_gps, "ORDER BY fecha_toma DESC");

				 $row_gps = pg_fetch_assoc($queryGPS);
				 $array[$l]['latitud'] 	= $row_gps['latitud'];
				 $array[$l]['longitud'] = $row_gps['longitud'];
			}
			$data[$k]['gps'] = $array;
			$k++;
		}
		$informacion['TrabajoProgramado'] = $data;

		//Inicio de proceso de consultas que ya han sido terminadas pero que el verificador y/o lector aun tienen cargadas
		$queryRespuesta = $postgresWS->PostgresFunctionTable("maestro.Rutas_Terminadas_Inspector(".$_inspector.") WHERE id_programacion IN (".$_rutasLecturas.")");
		
		$k = 0;
		unset($data);
		$data = array();
		
		while($rtaQuery = pg_fetch_assoc($queryRespuesta)){
			$data[$k]['id_programacion']=$rtaQuery['id_programacion'];
			$data[$k]['id_lector']		=$_inspector;
			$k++;
		}
		$informacion['LecturasTerminadas'] = $data;

		$postgresWS->ClosePostgres();
		echo json_encode($informacion);
	}

?>	
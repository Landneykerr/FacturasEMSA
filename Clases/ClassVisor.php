<?php
	session_start();
	include_once(dirname(__FILE__)."/../Clases/ClassPostgresBD.php");

	class ClassVisor{
	   private $trabajo_connect;
	   
		function ClassVisor(){
			$this->trabajo_connect = new PostgresDB();
		}


		function ConsultaRutas($_mes, $_anno){
			$this->trabajo_connect->OpenPostgres();
			$Informacion =  $this->trabajo_connect->PostgresSelectDistinctWhereOrder( 	"maestro.log_ciclo_muni_rutas", 
																						"id_serial,ciclo,ruta,municipio,total,total_entregadas,total_pendientes", 
																						"mes = ".$_mes." AND anno = ".$_anno." AND estado_ciclo_ruta IN ('E','T')", 
																						"ruta");
			return json_encode($this->trabajo_connect->QueryToJson($Informacion,["id_serial","ciclo","ruta","municipio","total","total_entregadas","total_pendientes"],[null,null,null,null,null,null],false));
			$this->trabajo_connect->ClosePostgres();
		}

		function consultarRutaCuenta($id){
        	$this->trabajo_connect->OpenPostgres();
			$Informacion =  $this->trabajo_connect->PostgresFunctionCamposTableOrder("pocision",
        																   	     "toma.consulta_pocision_gps_ruta(".$id.") ",
        																   	     "fecha_toma");
			$data = array();
			$i = 0;
			while ($row = pg_fetch_assoc($Informacion)) {
				if($row['pocision'] != ""){
					if($row['pocision'] != "0.0:0.0"){
					   $data[$i] = $row['pocision'];
				  	   $i++;
					}				  
				}				
				
  			}
			return json_encode($data);
			$this->trabajo_connect->ClosePostgres();
        }

        function consultarRutaCuentaDatos($id){
        	$this->trabajo_connect->OpenPostgres();
			$Informacion =  $this->trabajo_connect->PostgresFunctionCamposTableOrder("fecha_toma, pocision, cuenta, medidor, 																		nombre, mes, anno",
        																   			"toma.consulta_pocision_gps_cuenta(".$id.") ",
        																   			  "fecha_toma"); 										
			$this->trabajo_connect->ClosePostgres();
			$this->trabajo_connect->setConexion('fotos');	
			$this->trabajo_connect->OpenPostgres();



			$data = array();
			$arrayName = array();
			$i = 0;
			while ($row = pg_fetch_assoc($Informacion)) {

				$query = $this->trabajo_connect->PostgresSelectJoinWhereOrder("registro.fotos as a", 
																		"a.foto, a.nombre_foto",
										 								"registro.cuentas as b", 
										 								"b.id_cuenta=a.id_cuenta",
										 								"b.cuenta=".$row['cuenta']." AND b.mes=".$row['mes']." AND b.anno=".$row['anno'], 
										 								"a.fecha_entrega");

				$queryFoto = pg_fetch_assoc($query);

				if($row['pocision'] != ""){
					if($row['pocision'] != "0.0:0.0"){					    
					   $arrayName['cuenta'] = $row['cuenta'];
					   $arrayName['medidor'] = $row['medidor'];
					   $arrayName['nombre'] = $row['nombre'];
					   $arrayName['pocision'] = $row['pocision'];					
					   $arrayName['fecha'] = date("d-m-Y",strtotime($row['fecha_toma']));

					   $arrayName['foto'] = $queryFoto['foto'];
					   $arrayName['nombre_foto'] = $queryFoto['nombre_foto'];

					   $data[$i] = $arrayName;
				  	   $i++;
					}				  
				}				
				
  			}
			return json_encode($data);
			$this->trabajo_connect->ClosePostgres();
        }


	}
?>


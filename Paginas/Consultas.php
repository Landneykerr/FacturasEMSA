<?php
	session_start();
	include_once(dirname(__FILE__)."/../Clases/ClassUsuario.php");
	include_once(dirname(__FILE__)."/../Clases/ClassConfiguracion.php");
	include_once(dirname(__FILE__)."/../Clases/ClassParametros.php");

	$FcnUsuario 		= new Usuario();
	$FcnConfiguracion	= new Configuracion();
	$FcnParametros		= new ClassParametros();


	if(!isset($_SESSION['Accesos']['Consultas']))
		header("Location: ../index.php");
?>


<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		<meta name="description" content="">
		<meta name="author" content="">
		<link rel="icon" href="../../favicon.ico">

		<title>Dashboard Template for Bootstrap</title>

		<!-- Bootstrap core CSS -->
		<link rel="stylesheet" type="text/css" href="../FrameWork/bootstrap-3.3.5-dist/css/bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="../FrameWork/bootstrap-3.3.5-dist/css/bootstrap-theme.min.css">
		<link rel="stylesheet" type="text/css" href="../FrameWork/dataTables/css/dataTables.bootstrap.css">
		<link rel="stylesheet" type="text/css" href="../FrameWork/css/theme.css">
		

		<!-- Bootstrap core JS -->
		<script type="text/javascript" src="../FrameWork/bootstrap-3.3.5-dist/js/jquery.js"></script>
		<script type="text/javascript" src="../FrameWork/bootstrap-3.3.5-dist/js/bootstrap.min.js"></script>
		<script type="text/javascript" src="../FrameWork/dataTables/js/jquery.dataTables.min.js"></script>
		<script type="text/javascript" src="../FrameWork/dataTables/js/dataTables.bootstrap.js"></script>		
		<script type="text/javascript" src="../FrameWork/dataTables/js/hTablas.js"></script>
		<script type="text/javascript" src="../FrameWork/jquery/FuncionesRepetitivas.js"></script>


		
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {
				oTable11 = CrearDataTable("TablaEstadoCiclos",true,true,true);
				oTable12 = CrearDataTable("TablaEstadoRutas",true,true,true);
				oTable13 = CrearDataTable("TablaAporteInspectores",true,true,true);
				oTable14 = CrearDataTable("TablaLecturasPendientes",true,true,true);
				oTable15 = CrearDataTable("TablaLecturasTomadas",true,true,true);
				oTable16 = CrearDataTable("TablaErroresImpresion",true,true,true);
				oTable17 = CrearDataTable("TablaCorrecciones",true,true,true);
				oTable18 = CrearDataTable("TablaEstadoNoLecturas",true,true,true);
				oTable19 = CrearDataTable("TablaPendientesNoLecturas",true,true,true);
				oTable20 = CrearDataTable("TablaTomadasNoLecturas",true,true,true);



				
				$('#TablaEstadoNoLecturas tbody').on( 'click', 'tr', function () {
					$(this).toggleClass('selected');	
				});


				$('#TerminarNoLecturas').click(function(){
					var listaNoLecturas = InfTablaSelectedToJSON(oTable18,"IdNoLecturas",["id"],[0]);	

					$.ajax({ 	async: 		false, 
								type: 		"POST", 
								url: 		"../Ajax/AjaxConsultas.php", 
								data: 		{	Peticion: 		"TerminarRutasNoLecturas", 
												IdNoLecturas: 	listaNoLecturas
											}, 
								success: function(data){ 	
									alert(data);
								} 
							});
				});




				$('#TablaEstadoCiclos tbody').on( 'click', 'tr', function () {
					$(this).toggleClass('selected');

					var listaCiclos = InfTablaSelectedToJSON(oTable11,"CiclosSeleccionados",["ciclo"],[0]);			        
					$.ajax({ 	async: 		false, 
								type: 		"POST", 
								dataType: 	"json", 
								url: 		"../Ajax/AjaxConsultas.php", 
								data: 		{	Peticion: 		"ConsultaRutasCiclo", 
												Mes: 			$("#MesConsulta option:selected").val(),
												Anno: 			$("#AnnoConsulta option:selected").val(),
												Ciclos: 		listaCiclos
											}, 
								success: function(data){ 	
									MostrarTabla(oTable12,data);
								} 
							});
				});


				$('#TablaEstadoRutas tbody').on( 'click', 'tr', function () {
					$(this).toggleClass('selected');
					var listaRutas = InfTablaSelectedToJSON(oTable12,"RutasSeleccionadas",["ruta"],[0]);	
					$.ajax({ 	async: 		true, 
								type: 		"POST", 
								dataType: 	"json", 
								url: 		"../Ajax/AjaxConsultas.php", 
								data: 		{	Peticion: 		"ConsultaRutasEstado", 
												Mes: 			$("#MesConsulta option:selected").val(),
												Anno: 			$("#AnnoConsulta option:selected").val(),
												Rutas: 			listaRutas
											}, 
								success: function(data){ 	
									MostrarTabla(oTable13,data['AporteInspectores']);
									MostrarTabla(oTable14,data['ClientesPendientes']);
								} 
							});	
				});


				$('#TablaAporteInspectores tbody').on( 'click', 'tr', function () {
					$(this).toggleClass('selected');
					var listaInspectores = InfTablaSelectedToJSON(oTable13,"InspectoresSeleccionados",["ruta","inspector"],[0,1]);
					$.ajax({ 	async: 		true, 
								type: 		"POST", 
								dataType: 	"json", 
								url: 		"../Ajax/AjaxConsultas.php", 
								data: 		{	Peticion: 		"ConsultaLecturasTomadas", 
												Mes: 			$("#MesConsulta option:selected").val(),
												Anno: 			$("#AnnoConsulta option:selected").val(),
												Inspectores:	listaInspectores
											}, 
								success: function(data){ 	
									MostrarTabla(oTable15,data['ClientesLeidos']);
								} 
							});	
				});


				$("#ConsultaGeneralCiclos").click(function(){
					$.ajax({ 	async: 		false, 
								type: 		"POST", 
								dataType: 	"json", 
								url: 		"../Ajax/AjaxConsultas.php", 
								data: 		{	Peticion: 		"ConsultaPeriodo", 
												Mes: 			$("#MesConsulta option:selected").val(),
												Anno: 			$("#AnnoConsulta option:selected").val()
											}, 
								success: function(data){ 
									MostrarTabla(oTable11,data);
								} 
							});
				});


				$("#ConsultaConsolidado").click(function(){
					var Ciclo = GetColumnOfRowSelected(oTable11,0);
					url = "../Excel/DescargaConsolidado.php?Mes="+$("#MesConsulta option:selected").val()+"&Anno="+$("#AnnoConsulta option:selected").val();	
					window.open(url, '_blank');
					return false;
				});

			});
		</script>
	</head>

	<body>
		<header>
			<div class="container-fluid">
				<div class="row">
					<div class="col-sm-4 col-md-4"><h2>SYPELC - Consultas</h2></div>
					<div class="col-sm-8 col-md-8">
						<div id="navbar" class="navbar-collapse collapse">
							<ul class="nav navbar-nav navbar-right">
								<?php $FcnUsuario->AccesoPaginas("Consultas"); ?>
								<li><a href="../index.php">Salir</a></li>
							</ul>
						</div>	
					</div>
				</div>				
			</div>
		</header>

		<div class="container-fluid">
			<div class="col-sm-9 col-md-12 ">
				<ul class="nav nav-tabs">
					<?php $FcnUsuario->AccesoModulos("Consultas"); ?>
				</ul>

				<div class="tab-content">
					<?php 
					if(isset($_SESSION['Accesos']['Consultas']['consultas_general_facturas'])){ ?>
						<div id="consultas_general_facturas" class="tab-pane fade" height="100%">
							<div class="row">
								<div class="col-md-2">
									<div class="panel panel-success table-responsive">
										<div class="panel-heading">Periodo Consulta</div>						
										<div class="panel-body">
											<div class="form-group">
												<label for="MesConsulta">Mes</label>
												<select id="MesConsulta" class="form-control" >
													<?php
														$_mes = json_decode($FcnParametros->getMes());
														foreach($_mes as $obj){
															echo "<option value='".$obj->numero_mes."'>".$obj->nombre_mes."</option>";											   
														}
													?> 
												</select>
											</div>

											<div class="form-group">
												<label for="AnnoConsulta">Año</label>
												<select id="AnnoConsulta" class="form-control" >
													<?php
														$_anno = json_decode($FcnParametros->getAnno());
														foreach($_anno as $obj){
															echo "<option value='".$obj->anno."'>".$obj->anno."</option>";											   
														}
													?> 
												</select>
											</div>

											<div class="form-group">
												<button id="ConsultaGeneralCiclos" type="button" class="btn btn-primary btn-block btn-md">
													Consultar    <span class="glyphicon glyphicon-search"></span>
												</button>
												<button id="ConsultaConsolidado" type="button" class="btn btn-success btn-block btn-md">
													Consolidado  <span class="glyphicon glyphicon-save"></span>
												</button>
											</div>														
										</div>
									</div>	
								</div>

								<div class="col-md-3">
									<div class="panel panel-success table-responsive">
										<div class="panel-heading">Estado General Ciclos</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<table id="TablaEstadoCiclos" class="table table-condensed" cellspacing="0" width="99%">
															<thead>
																<tr class="info"> 
																	<th width="20%">Ciclo</th>
																	<th width="20%">Total</th>
																	<th width="20%">Certificar</th>
																	<th width="20%">Entregadas</th>
																	<th width="20%">Pend.</th>
																</tr>
															</thead>
															<tbody>							
															</tbody>
														</table>	
													</div>	
												</div>	
											</div>	
										</div>					
									</div>	
								</div>
								
								<div class=" col-md-7">
									<div class="panel panel-primary table-responsive">
										<div class="panel-heading">Estado General Rutas</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<table id="TablaEstadoRutas" class="table table-condensed" cellspacing="0" width="99%">
															<thead>
																<tr class="warning"> 
																	<th width="10%">Ruta</th>
																	<th width="40%">Inspector</th>
																	<th width="10%">Total</th>
																	<th width="10%">Certificar</th>
																	<th width="10%">Entregadas</th>
																	<th width="10%">Pendientes</th>
																	<th width="10%">Est.</th>
																</tr>
															</thead>
															<tbody>							
															</tbody>
														</table>
													</div>
												</div>	
											</div>	
										</div>	
									</div>
								</div>	
							</div>

							<div class="row">
								<div class=" col-md-5">
									<div class="panel panel-info table-responsive">
										<div class="panel-heading">Aporte De Inspectores A Rutas</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<table id="TablaAporteInspectores" class="table table-condensed" cellspacing="0" width="99%">
															<thead>
																<tr class="danger"> 
																	<th width="20%">Ruta</th>
																	<th width="15%">Cod</th>
																	<th width="45%">Nombre</th>
																	<th width="20%">Entregadas</th>
																</tr>
															</thead>
															<tbody>								
															</tbody>
														</table>
													</div>
												</div>			
											</div>	
										</div>						
									</div>
								</div>
										
								<div class=" col-md-7">
									<div class="panel panel-danger table-responsive">
										<div class="panel-heading">Clientes Pendientes De Toma De Lectura</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<table id="TablaLecturasPendientes" class="table table-condensed" cellspacing="0" width="99%">
															<thead>
																<tr class="primary"> 
																	<th width="15%">Ruta</th>
																	<th width="15%">Cuenta</th>
																	<th width="15%">Medidor</th>
																	<th width="30%">Nombre</th>
																	<th width="25%">Direccion</th>
																</tr>
															</thead>
															<tbody>							
															</tbody>
														</table> 
													</div>
												</div>
											</div>			
										</div>
									</div>
								</div>	
							</div>

							<div class="row">
								<div class=" col-md-12">
									<div class="panel panel-warning table-responsive">
										<div class="panel-heading">Lista de Clientes A Los Cuales Ya Se Realizo Toma De Lectura</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<table id="TablaLecturasTomadas" class="table table-bordered table-condensed" cellspacing="0" width="99%">
															<thead>
																<tr> 
																	<th width="8%">Ruta</th>
																	<th width="8%">Cuenta</th>
																	<th width="15%">Medidor</th>
																	<th width="30%">Nombre</th>
																	<th width="30%">Direccion</th>
																	<th width="8%">Fecha Entrega</th>
																	<th width="10%">Fecha Registro</th>
																	<th width="10%">Mensaje</th>
																	<th width="15%">Inspector</th>
																	<th width="15%">Latitud</th>
																	<th width="8%">Longitud</th>
																	<th width="8%">Distancia</th>
																</tr>
															</thead>
															<tbody>							
															</tbody>
														</table> 
													</div>
												</div>
											</div>			
										</div>
									</div>
								</div>	
							</div>
						</div>		
					<?php }?>				
			</div>
		</div>
	</body>
</html>

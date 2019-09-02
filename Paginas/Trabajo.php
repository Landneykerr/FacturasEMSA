<?php
	session_start();
	include_once(dirname(__FILE__)."/../Clases/ClassUsuario.php");
	include_once(dirname(__FILE__)."/../Clases/ClassConfiguracion.php");
	include_once(dirname(__FILE__)."/../Clases/ClassParametros.php");

	$FcnUsuario 		= new Usuario();
	$FcnConfiguracion	= new Configuracion();
	$FcnParametros		= new ClassParametros();


	if(!isset($_SESSION['Accesos']['Trabajo']))
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
				var rows_selected = [];	
				oTable11 = CrearDataTable("TablaTrabajo",true,true,true);


				//Acciones sobre tabla de trabajo
				$('#TablaTrabajo tbody').on( 'click', 'tr', function () {
					$(this).toggleClass('row_selected');
				});


				//JQUERY TRABAJO
				$('#TablaTrabajo tbody').on( 'click', 'tr', function () {
					$(this).toggleClass('selected');
				});

				$(".PeriodoTrabajo").change(function(){
					$.ajax({ 	async: 		true, 
								type: 		"POST", 
								dataType: 	"json", 
								url: 		"../Ajax/AjaxTrabajo.php", 
								data: 		{	Peticion: 		"getCiclosActivos", 
												Mes: 			$("#MesTrabajo option:selected").val(),
												Anno: 			$("#AnnoTrabajo option:selected").val()
											}, 
								success: function(data){ 
									MostrarResultadoCombo(CicloTrabajo,data);
								} 
							});
				});


				$("#ConsultarTrabajo").click(function(){
					var SendInformacionN =	$.ajax({    async:  	true,
														type:   	"POST",
														dataType: 	"json",
														url:    	"../Ajax/AjaxTrabajo.php",
														data:   {   Peticion: 	"ConsultarAsignacion",
																	Ciclo: 		$("#CicloTrabajo option:selected").val(),
																	Mes: 		$("#MesTrabajo option:selected").val(),
																	Anno: 		$("#AnnoTrabajo option:selected").val()
																},
														success:function(data){
															MostrarTabla(oTable11,data);
														}
													});

					SendInformacionN.fail(function(jqXHR, textStatus) {
						alert( "Error en la consulta de usuarios." );
					});
				});


				$("#AsignarTrabajo").click(function(){
					var Id_Rutas 	= InfTablaSelectedToJSON(oTable11,"Id_Rutas",["id"],[0]);
					var SendInformacionN =	$.ajax({    async:  	true,
														type:   	"POST",
														url:    	"../Ajax/AjaxTrabajo.php",
														data:   {   Peticion: 	"AsignarTrabajo",
																	Inspector: 	$("#InspectorTrabajo option:selected").val(),
																	Rutas: 		Id_Rutas 
																},
														success:function(data){
															alert(data);
														}
													});

					SendInformacionN.fail(function(jqXHR, textStatus) {
						alert( "Error de conexion con el servidor." );
					});
				})


				$("#EliminarTrabajo").click(function(){
					var Id_Rutas 	= InfTablaSelectedToJSON(oTable11,"Id_Rutas",["id"],[0]);
					var SendInformacionN =	$.ajax({    async:  	true,
														type:   	"POST",
														url:    	"../Ajax/AjaxTrabajo.php",
														data:   {   Peticion: 	"EliminarAsignacion",
																	Inspector: 	$("#InspectorTrabajo option:selected").val(),
																	Rutas: 		Id_Rutas 
																},
														success:function(data){
															alert("TRABAJO ELIMINADO");
														}
													});

					SendInformacionN.fail(function(jqXHR, textStatus) {
						alert( "Error de conexion con el servidor." );
					});
				})

				/******* EXPORTAR INFORMACIÓN **/

				$("#PDFAsignadas").click(function(){		
					var ruta = JSON.stringify(InfTablaSelectedToJSON(oTable11,"Ruta",["ruta"],[2]));
					
					url = "../PDF/Plantilla.php?Mes="+$("#MesTrabajo").val()+"&Anno="+$("#AnnoTrabajo").val()+"&Ciclo="+$("#CicloTrabajo").val()+"&Ruta="+ruta;	
					window.open(url, '_blank');
					return false;

					/*url = "../Excel/DescargaPlanillaExcel.php?Mes="+$("#MesTrabajo").val()+"&Anno="+$("#AnnoTrabajo").val()+"&Ciclo="+$("#CicloTrabajo").val()+"&Ruta="+ruta;	
					window.open(url, '_blank');
					return false;*/	
				});

			});
		</script>
	</head>

	<body>
		<header>
			<div class="container-fluid">
				<div class="row">
					<div class="col-sm-4 col-md-4">
						<h2>SYPELC - Trabajo</h2>	
					</div>
					<div class="col-sm-8 col-md-8">
						<div id="navbar" class="navbar-collapse collapse">
							<ul class="nav navbar-nav navbar-right">
								<?php $FcnUsuario->AccesoPaginas("Trabajo"); ?>
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
					<?php $FcnUsuario->AccesoModulos("Trabajo"); ?>
				</ul>

				<div class="tab-content">
					<?php 
					if(isset($_SESSION['Accesos']['Trabajo']['trabajo_asignacion'])){ ?>
						<div id="trabajo_asignacion" class="tab-pane" height="100%">
							<div class="row">
								<div class="col-md-3">
									<div class="row">
										<div class="panel panel-success table-responsive">
											<div class="panel-heading">Periodo de Trabajo
											</div>
											
											<div class="panel-body">
												<div class="form-group">
													<label for="MesTrabajo">Mes</label>
													<select id="MesTrabajo" class="form-control PeriodoTrabajo" >
														<?php
															$_mes = json_decode($FcnParametros->getMes());
															foreach($_mes as $obj){
																echo "<option value='".$obj->numero_mes."'>".$obj->nombre_mes."</option>";											   
															}
														?> 
													</select>
												</div>

												<div class="form-group">
													<label for="AnnoTrabajo">Año</label>
													<select id="AnnoTrabajo" class="form-control PeriodoTrabajo" >
														<?php
															$_anno = json_decode($FcnParametros->getAnno());
															foreach($_anno as $obj){
																echo "<option value='".$obj->anno."'>".$obj->anno."</option>";											   
															}
														?> 
													</select>
												</div>

												<div class="form-group">
													<label for="CicloTrabajo">Ciclo</label>
													<select id="CicloTrabajo" class="form-control" >
													</select>
												</div>
										
												<div class="form-group">
													<button id="ConsultarTrabajo" type="button" class="btn btn-primary btn-md pull-right">Consultar</button>
												</div>
											</div>					
										</div>
									</div>
									
									<div class="row">
										<div class="panel panel-success table-responsive">
											<div class="panel-heading">Administracion de Trabajo
											</div>
											
											<div class="panel-body">
												<div class="form-group">
													<label for="InspectorTrabajo">Inspector</label>
													<select id="InspectorTrabajo" class="form-control" >
														<?php
															$_inspectores = json_decode($FcnParametros->getInspectoresActivos(1));
															foreach($_inspectores as $obj){
																echo "<option value='".$obj->id_inspector."'>".$obj->nombre."</option>";											   
															}
														?>
													</select>
												</div>
												<div class="form-group">
													<button id="EliminarTrabajo" type="button" class="btn btn-danger btn-md pull-right">Eliminar</button>
													<button id="AsignarTrabajo" type="button" class="btn btn-success btn-md pull-left">Asignar</button>
												</div>
											</div>					
										</div>
									</div>
								</div> 

								<div class="col-md-9">
									<div class="panel panel-success table-responsive">
										<div class="panel-heading">Estado General Ciclos
											<button id="PDFAsignadas" type="button" class="btn btn-success">PDF
											</button>
										</div>
										<div class="panel-body">
											<div class="row">
												<div class="col-md-12">
													<div class="form-group">
														<table id="TablaTrabajo" class="table table-condensed" cellspacing="0" width="99%">
															<thead>
																<tr class="info"> 
																	<th width="10%">Id</th>
																	<th width="40%">Inspector</th>
																	<th width="10%">Ruta</th>
																	<th width="10%">Total</th>
																	<th width="10%">Certificar</th>
																	<th width="10%">Leidas</th>
																	<th width="10%">Pend.</th>															
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
					<?php }
					if(isset($_SESSION['Accesos']['Trabajo']['trabajo_recuperacion'])){ ?>
						<div id="trabajo_recuperacion" class="tab-pane fade" height="100%" >
							<div class="row">
								<div class="col-md-8 col-lg-8">
									<div class="panel panel-success">
										<div class="panel-body">
											<div class="row">
												<div class="col-md-4 col-lg-4">
													<div class="panel panel-success table-responsive">	
														<div class="panel-heading">Periodo y Ciclo</div>											
														<div class="panel-body">
															<div class="form-group">
																<label for="MesRecuperacion">Mes</label>
																<select id="MesRecuperacion" class="form-control PeriodoRecuperacion" >
																<?php
																	$_mes = json_decode($FcnParametros->getMes());
																	foreach($_mes as $obj){
																		echo "<option value='".$obj->numero_mes."'>".$obj->nombre_mes."</option>";											   
																	}
																?> 
																</select>
															</div>

															<div class="form-group">
																<label for="AnnoRecuperacion">Año</label>
																<select id="AnnoRecuperacion" class="form-control PeriodoRecuperacion" >
																<?php
																	$_anno = json_decode($FcnParametros->getAnno());
																	foreach($_anno as $obj){
																		echo "<option value='".$obj->anno."'>".$obj->anno."</option>";											   
																	}
																?> 
																</select>
															</div>

															<div class="form-group">
																<label for="CicloRecuperacion">Ciclo</label>
																<select id="CicloRecuperacion" class="form-control" >
																<?php
																	$_ciclo = json_decode($FcnParametros->getCiclos());
																	foreach($_ciclo as $obj){
																		echo "<option value='".$obj->id_ciclo."'>".$obj->id_ciclo."</option>";											   
																	}
																?> 
																</select>
															</div>													
														</div>					
													</div>
												</div>

												<div class="col-md-8 col-lg-8">
													<div class="panel panel-success table-responsive">
														<div class="panel-heading">Anomalias</div>
														<div class="panel-body">
														<?php
															$_critica = json_decode($FcnParametros->getAnomalia(false, "id_anomalia"));
															$i=0;
															foreach($_critica as $obj){
																if($i == 0){
																	echo "<div class='col-md-4'>";
																}
																echo "<div class='checkbox'><label>";
																echo "<input type='checkbox' id='AnomaliaRecuperacion' name='".$obj->id_anomalia."' value='".$obj->id_anomalia."'/>".$obj->id_anomalia;
																echo "</label></div>";	

																if(($i == 7)||($i == 15)){
																	echo "</div>";
																	echo "<div class='col-md-4'>";
																}
																$i++;										   
															}
															echo "</div>";
														?> 												
														</div>					
													</div>
												</div>
											</div>		

											<div class="row">
												<div class="col-md-12 col-lg-12">
													<div class="form-group">
														<button id="ExportarRecuperacion" type="button" class="btn btn-success btn-md pull-left">Descargar <span class="glyphicon glyphicon-save"></span></button>
														<button id="ConsultarRecuperacion" type="button" class="btn btn-primary btn-md pull-right">Consultar</button>
													</div>
												</div>	
											</div>	
										</div>	
									</div>						
								</div>							

								<div class="col-md-4">
									<div class="panel panel-success table-responsive">
										<div class="panel-heading">Administracion de Recuperaciones</div>
										<div class="panel-body">
											<div class="form-group">
												<label for="InspectorRecuperacion">Recuperador</label>
												<select id="InspectorRecuperacion" class="form-control" >
												<?php
													$_inspectores = json_decode($FcnParametros->getInspectoresActivos(2));
													foreach($_inspectores as $obj){
														echo "<option value='".$obj->id_inspector."'>".$obj->nombre."</option>";											   
													}
												?>
												</select>
											</div>
											<div class="form-group">
												<button id="EliminarRecuperacion" type="button" class="btn btn-danger btn-md pull-right">Eliminar</button>
												<button id="AsignarRecuperacion" type="button" class="btn btn-success btn-md pull-left">Asignar</button>
											</div>
										</div>					
									</div>
								</div>
							</div> 							

							<div class="main row">
								<div class="col-md-12 col-lg-12">
									<div class="panel panel-success table-responsive">
										<div class="panel-heading">Cuentas Para Recuperacion</div>
										<div class="panel-body">
											<table id="TablaRecuperacion" class="table table-condensed" cellspacing="0" width="99%">
												<thead>
													<tr class="info"> 
														<th width="3%"><input name="select_all" id="select_all" value="1" type="checkbox"></th>
														<th width="10%">Ruta</th>
														<th width="5%">Secu.</th>
														<th width="10%">Cuenta</th>
														<th width="18%">Nombre</th>
														<th width="18%">Direccion</th>
														<th width="10%">Medidor</th>
														<th width="10%">Anomalia</th>	
														<th width="18%">Mensaje</th>
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
					<?php }
					if(isset($_SESSION['Accesos']['Trabajo']['trabajo_verificacion'])){ ?>
						<div id="trabajo_verificacion" class="tab-pane fade" height="100%" >
							<div class="main row">
								<div class="col-md-8 col-lg-8">
									<div class="panel panel-success">
										<div class="panel-body">
											<div class="row">
												<div class="col-md-4 col-lg-4">
													<div class="panel panel-success table-responsive">	
														<div class="panel-heading">Periodo y Ciclo</div>											
														<div class="panel-body">
															<div class="form-group">
																<label for="MesVerificacion">Mes</label>
																<select id="MesVerificacion" class="form-control PeriodoVerificacion" >
																<?php
																	$_mes = json_decode($FcnParametros->getMes());
																	foreach($_mes as $obj){
																		echo "<option value='".$obj->numero_mes."'>".$obj->nombre_mes."</option>";											   
																	}
																?> 
																</select>
															</div>

															<div class="form-group">
																<label for="AnnoVerificacion">Año</label>
																<select id="AnnoVerificacion" class="form-control PeriodoVerificacion" >
																<?php
																	$_anno = json_decode($FcnParametros->getAnno());
																	foreach($_anno as $obj){
																		echo "<option value='".$obj->anno."'>".$obj->anno."</option>";											   
																	}
																?> 
																</select>
															</div>

															<div class="form-group">
																<label for="CicloVerificacion">Ciclo</label>
																<select id="CicloVerificacion" class="form-control" >
																<?php
																	$_ciclo = json_decode($FcnParametros->getCiclos());
																	foreach($_ciclo as $obj){
																		echo "<option value='".$obj->id_ciclo."'>".$obj->id_ciclo."</option>";											   
																	}
																?> 
																</select>
															</div>													
														</div>					
													</div>
												</div>

												<div class="col-md-8 col-lg-8">
													<div class="panel panel-success table-responsive">
														<div class="panel-heading">Criticas</div>
														<div class="panel-body">
														<?php
															$_critica = json_decode($FcnParametros->getCritica());
															$i=0;
															foreach($_critica as $obj){
																if($i == 0){
																	echo "<div class='col-md-4'>";
																}
																echo "<div class='checkbox'><label>";
																echo "<input type='checkbox' id='CriticaVerificacion' name='".$obj->descripcion."' value='".$obj->descripcion."'/>".$obj->descripcion;
																echo "</label></div>";	

																if(($i == 7)||($i == 15)){
																	echo "</div>";
																	echo "<div class='col-md-4'>";
																}
																$i++;										   
															}
															echo "</div>";
														?> 												
														</div>					
													</div>
												</div>
											</div>		

											<div class="row">
												<div class="col-md-12 col-lg-12">
													<div class="form-group">
														<button id="ExportarVerificacion" type="button" class="btn btn-success btn-md pull-left">Descargar <span class="glyphicon glyphicon-save"></span></button>
														<button id="ConsultarVerificacion" type="button" class="btn btn-primary btn-md pull-right">Consultar</button>
													</div>
												</div>	
											</div>	
										</div>	
									</div>						
								</div>							

								<div class="col-md-4">
									<div class="panel panel-success table-responsive">
										<div class="panel-heading">Administracion de Verificaciones</div>
										<div class="panel-body">
											<div class="form-group">
												<label for="InspectorVerificacion">Verificador</label>
												<select id="InspectorVerificacion" class="form-control" >
												<?php
													$_inspectores = json_decode($FcnParametros->getInspectoresActivos(2));
													foreach($_inspectores as $obj){
														echo "<option value='".$obj->id_inspector."'>".$obj->nombre."</option>";											   
													}
												?>
												</select>
											</div>
											<div class="form-group">
												<button id="EliminarVerificacion" type="button" class="btn btn-danger btn-md pull-right">Eliminar</button>
												<button id="AsignarVerificacion" type="button" class="btn btn-success btn-md pull-left">Asignar</button>
											</div>
										</div>					
									</div>
								</div>
							</div> 							

							<div class="main row">
								<div class="col-md-12 col-lg-12">
									<div class="panel panel-success table-responsive">
										<div class="panel-heading">Cuentas Para Verificacion</div>
										<div class="panel-body">
											<table id="TablaVerificacion" class="table table-condensed" cellspacing="0" width="99%">
												<thead>
													<tr class="info"> 
														<th width="3%"><input name="select_all" id="select_all" value="1" type="checkbox"></th>
														<th width="10%">Ruta</th>
														<th width="5%">Secu.</th>
														<th width="10%">Cuenta</th>
														<th width="18%">Nombre</th>
														<th width="18%">Direccion</th>
														<th width="10%">Medidor</th>
														<th width="10%">Lectura</th>	
														<th width="18%">Critica</th>
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
					<?php } ?>
					<div class="clearfix"></div>
				</div>	
			</div>	
		</div>
	</body>
</html>

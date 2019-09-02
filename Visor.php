<?php
	session_start();
	include_once(dirname(__FILE__)."/../Clases/ClassUsuario.php");
	include_once(dirname(__FILE__)."/../Clases/ClassConfiguracion.php");
	include_once(dirname(__FILE__)."/../Clases/ClassParametros.php");

	$FcnUsuario 		= new Usuario();
	$FcnConfiguracion	= new Configuracion();
	$FcnParametros		= new ClassParametros();


	if(!isset($_SESSION['Accesos']['Visor']))
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

		<!-- Necesario para el funcionamiento de el Visor -->
		<link rel="stylesheet" type="text/css" href="../galeriaOne/jquery.lightbox.css">
  		<link rel="stylesheet" type="text/css" href="../galeriaOne/galeria.css">
  		<!--<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>-->
		<script type="text/javascript" src="../galeriaOne/jquery.lightbox.js"></script>
		<script>  
		  $(function() {
		    $('.gallery a').lightbox(); 
		  });
		</script>
		
		<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {				
				oTable12 = CrearDataTable("TablaEstadoRutas",true,true,true);


				$('#TablaEstadoRutas tbody').on( 'click', 'tr', function () {
					$(this).toggleClass('selected');
					
				});

				$("#ConsultarRuta").click(function(){
					
					var IdRuta =  GetColumnOfRowSelected(oTable12,0);

					
					url = "../Maps/rutas.php?Id="+IdRuta;	
					window.open(url, '_blank');
					return false;

				});				

				$("#ConsultarRutaCuentas").click(function(){					
					var IdRuta =  GetColumnOfRowSelected(oTable12,0);

								
					url = "../Maps/cuentas.php?Id="+IdRuta;	
					window.open(url, '_blank');
					return false;

				});	

				
				$("#ConsultaGeneralRutas").click(function(){
					$.ajax({ 	async: 		false, 
								type: 		"POST", 
								dataType: 	"json", 
								url: 		"../Ajax/AjaxVisor.php", 
								data: 		{	Peticion: 		"ConsultaRutas", 
												Mes: 			$("#MesConsulta option:selected").val(),
												Anno: 			$("#AnnoConsulta option:selected").val()
											}, 
								success: function(data){ 
									MostrarTabla(oTable12,data);
								} 
							});
				});



				$("#DescargarFotos").click(function(){					
					url = "../Zip/DescargaFotoDia.php?cuenta="+$("#DatoCuenta").val();	
					window.open(url, '_blank');
					return false;
				});
				

				$("#ConsultarFoto").click(function(){	
					/*url = "../ConsultaFotos/ConsultaFoto.php?NombreFoto="+$("#DatoCuenta").val();	
					window.open(url, '_blank');
					return false;	*/
					
					var TipoBusqueda  =	$("#TipoConsulta option:selected").val();					
					if(TipoBusqueda==1){
						
						url = "VisorLecturas.php?Dato="+$("#DatoCuenta").val();	
						window.open(url, '_blank');
						return false;	

					}
				})
	

			});
		</script>
	</head>

	<body>
		<header>
			<div class="container-fluid">
				<div class="row">
					<div class="col-sm-4 col-md-4"><h2>SYPELC - Consulta Fotos</h2></div>
					<div class="col-sm-8 col-md-8">
						<div id="navbar" class="navbar-collapse collapse">
							<ul class="nav navbar-nav navbar-right">
								<?php $FcnUsuario->AccesoPaginas("Visor"); ?>
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
					<?php $FcnUsuario->AccesoModulos("Visor"); ?>
				</ul>

				<div class="tab-content">
					<?php 
					if(isset($_SESSION['Accesos']['Visor']['visor_mapas'])){ ?>
						<div id="visor_mapas" class="tab-pane fade" height="100%">
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
												<button id="ConsultaGeneralRutas" type="button" class="btn btn-primary btn-block btn-md">
													Consultar    <span class="glyphicon glyphicon-search"></span>
												</button>
																								
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
																	<th width="10%">Id</th>
																	<th width="20%">Ciclo</th>
																	<th width="20%">Ruta</th>
																	<th width="20%">Municipio</th>
																	<th width="10%">Total</th>
																	<th width="10%">Entregadas</th>
																	<th width="10%">Pendientes</th>
																</tr>
															</thead>
															<tbody>							
															</tbody>
														</table>
													</div>
													<div class="col-md-3">
														<div class="form-group">
															<button id="ConsultarRuta" type="button" class="btn btn-primary btn-block btn-md">Ruta </button>															
														</div>	
													</div>													
													<div class="col-md-3">
														<div class="form-group">														
															<button id="ConsultarRutaCuentas" type="button" class="btn btn-success btn-block btn-md">Ruta Cuentas</button>
														</div>	
													</div>													
												</div>	
											</div>	
										</div>	
									</div>
								</div>	
							</div>												
						</div>	
					<?php } 
					if(isset($_SESSION['Accesos']['Visor']['visor_fotos'])){ ?>
							<div id="visor_fotos" class="tab-pane fade" height="100%">
								<div class="row">
									<div class="col-md-4">
										<div class="panel panel-success table-responsive">
											<div class="panel-heading">Datos de Busqueda</div>						
											<div class="panel-body">
												<div class="form-group">
													<label for="TipoConsulta">Buscar Por:</label>
													<select id="TipoConsulta" class="form-control" >
														<option value="1">Cuenta</option>
														<!--<option value="2">Medidor</option>
														<option value="3">Fecha</option>-->
													</select>
												</div>
												<div class="form-group">													
													<label class="sr-only" for="DatoCuenta">Datos a Buscar</label>
													<input class="form-control" id="DatoCuenta" type="text" placeholder="Dato a Buscar">
												</div>
										
												<div class="form-group">
													<button id="ConsultarFoto" type="button" class="btn btn-primary btn-md pull-right">Consultar</button>
												</div>

												<div class="form-group">
													<button id="DescargarFotos" type="button" class="btn btn-warning btn-md pull-left">Descargar</button>
												</div>
											</div>
										</div>	
									</div>
									<div class="col-md-8 col-xs-3">
									<div class="panel panel-primary table-responsive">
										<div class="panel-heading">Visor de Registros Fotograficos</div>
										<div class="panel-body">										
											<ul class="gallery">
										      Para realizar la consulta en el visor de registros fotograficos por favor seleccione el parametro de busqueda 'cuenta'.
										    </ul>
										</div>	
									</div>
								</div>	
								</div>
							</div>		
					<?php } 
					  if(isset($_SESSION['Accesos']['RegistroFotografico']['fotos_cuentas'])){ ?>
							<div id="fotos_cuentas" class="tab-pane fade" height="100%">
								<div class="row">
									<div class="col-md-2">
										<div class="panel panel-success table-responsive">
											<div class="panel-heading">Datos de Busqueda</div>						
											<div class="panel-body">
												<div class="form-group">
													<label for="MesFotosCuentas">Mes</label>
													<select id="MesFotosCuentas" class="form-control PeriodoCorreccion" >
														<?php
															$_mes = json_decode($FcnParametros->getMes());
															foreach($_mes as $obj){
																echo "<option value='".$obj->numero_mes."'>".$obj->nombre_mes."</option>";											   
															}
														?> 
													</select>
												</div>
												<div class="form-group">
													<label for="AnnoFotosCuentas">Año</label>
													<select id="AnnoFotosCuentas" class="form-control PeriodoCorreccion" >
														<?php
															$_anno = json_decode($FcnParametros->getAnno());
															foreach($_anno as $obj){
																echo "<option value='".$obj->anno."'>".$obj->anno."</option>";											   
															}
														?> 
													</select>
												</div>
												<div class="form-group">													
													<label class="sr-only" for="DatoCuentas">Datos a Buscar</label>
													<input class="form-control" id="DatoCuentas" type="text" placeholder="Datos a Buscar">
												</div>
										
												<div class="form-group">
													<button id="DescargarFotosCuentas" type="button" class="btn btn-success btn-block btn-md">Descargar 
													<span class="glyphicon glyphicon-save"></span></button>
												</div>		
											</div>
										</div>	
									</div>
									<div class="col-md-10 col-xs-3">
									<div class="panel panel-primary table-responsive">
										<div class="panel-heading">Consulta de Registros Fotograficos</div>
										<div class="panel-body">										
											<ul class="gallery">
										     Para descargar fotos por cuentas, por favor selecciones Mes y Año, digite las cuentas separadas por coma, al final de el ultimo registro no debe aparecer coma.
											 Ejemplo:  1346,4896,4899,47996										     
										    </ul>
										</div>	
									</div>
								</div>	
								</div>
							</div>		
					<?php } ?>					
			</div>
		</div>
	</body>
</html>

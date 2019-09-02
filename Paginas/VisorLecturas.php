<?php
	session_start();
	include_once(dirname(__FILE__)."/../Clases/ClassPostgresBD.php");

	$consulta_connect =  new PostgresDB();

	$dato = $_GET['Dato'];		
?>

<html>
	<head>
		<meta http-equiv="Content-type" content="text/html; charset=utf-8">
		<title>SYPELC SAS</title>
		<link rel="stylesheet" href="css/basic.css" type="text/css" />				
		<link rel="stylesheet" href="css/white.css" type="text/css" />
		
		<script type="text/javascript" src="js/jquery-1.3.2.js"></script>
		<script type="text/javascript" src="js/jquery.history.js"></script>
		<script type="text/javascript" src="js/jquery.galleriffic.js"></script>
		<script type="text/javascript" src="js/jquery.opacityrollover.js"></script>
		<script type="text/javascript" src="js/gallery.js"></script>
		<link rel="stylesheet" href="css/galleriffic-5.css" type="text/css" />
		<!-- We only want the thunbnails to display when javascript is disabled -->
		<script type="text/javascript">
			document.write('<style>.noscript { display: none; }</style>');
		</script>
	</head>
	<body>
		<div id="page">
			<div id="container">
				<h2><a href="index.html">Historico Fotos Cuenta</a></h2>
				
				<!-- Start Advanced Gallery Html Containers -->				
				<div class="navigation-container">
					<div id="thumbs" class="navigation">
						<a class="pageLink prev" style="visibility: hidden;" href="#" title="Previous Page"></a>					
							<ul class="thumbs noscript">
								
								<?php

									


									$consulta_connect->setConexion('fotos');
									$consulta_connect->OpenPostgres();
									// ON (b.cuenta=303147306)
									$query = $consulta_connect->PostgresSelectJoinWhereOrder("registro.fotos as a", 
																		"a.fecha_entrega, a.foto, a.nombre_foto, b.inspector, b.mes, b.anno, b.ciclo",
										 								"registro.cuentas as b", 
										 								"b.id_cuenta=a.id_cuenta",
										 								"b.cuenta=".$dato, 
										 								"a.fecha_entrega DESC");
								    		
								     while($row   = pg_fetch_assoc($query)){

								     	$consulta_connect->setConexion('linode');
										$consulta_connect->OpenPostgres();										

										$query_0 = $consulta_connect->PostgresSelectWhereOrder("toma.factura", "latitud,longitud", "cuenta=".$dato." AND TO_CHAR(fecha_entrega,'YYYY/MM')='".$row['anno']."/".str_pad($row['mes'], 2, "0", STR_PAD_LEFT)."'", "cuenta");

										$row_gps 	= pg_fetch_assoc($query_0);
										$latitud 	= $row_gps['latitud'];
										$longitud 	= $row_gps['longitud'];

								      echo "
								      		<li>
											<a class='thumb' name='leaf' href='data:image/jpg;base64,".$row['foto']."' title=''>
												<img src='data:image/jpg;base64,".$row['foto']."' width = 75 height = 75 alt='Title #0' />
											</a>
												<div class='caption'>
													<div class='image-title'><b>Cuenta:</b> ".$dato."</div>
													<div class='image-desc'><p><b>Fecha:</b> ".$row['fecha_entrega']."</p>																																						
																			<p><b>Ciclo:</b> ".$row['ciclo']."</p>
																			<p><b>Inspector:</b> ".$row['inspector']."</p>
																			<p><b>Latitud:</b> ".$row_gps['latitud']."</p>
																			<p><b>Longitud:</b> ".$row_gps['longitud']."</p>
																			</div>
													<div class='download'>
														<a href='data:image/jpg;base64,".$row['foto']."'>Descargar Original</a> 
													</div>
												</div>
										    </li>	
								      ";
								    }    								    
								    $consulta_connect->ClosePostgres();
								?>
						</ul>
						<a class="pageLink next" style="visibility: hidden;" href="#" title="Next Page"></a>
					</div>
				</div>
				<div class="content">
					<div class="slideshow-container">
						<div id="controls" class="controls"></div>
						<div id="loading" class="loader"></div>
						<div id="slideshow" class="slideshow"></div>
					</div>
					<div id="caption" class="caption-container">
						<div class="photo-index"></div>
					</div>
				</div>
				<!-- End Gallery Html Containers -->
				<div style="clear: both;"></div>
			</div>
		</div>
		<div id="footer">&copy; 2015 Sypelc SAS</div>		
	</body>
</html>
<?php
	include_once(dirname(__FILE__)."/../Clases/ClassPostgresBD.php");

	$postgresWS	= new PostgresDB();
	$postgresWS->setConexion("fotos");
	$postgresWS->OpenPostgres();

	$nombre_foto   	= $_GET['NombreFoto'];

	$foto_db = $postgresWS->PostgresSelectWhereOrder(	"registro.fotos",
												  		"foto", 
														"nombre_foto='".$nombre_foto."'",
														"foto asc");
	

	$row_foto   = pg_fetch_assoc($foto_db);

	//echo "String Foto: ".$row_foto['foto'];

	$image=base64_decode($row_foto['foto']);
	$im = new Imagick();
	$im->readimageblob($image);
	/*
	// Create thumbnail max of 200x82
	$im->thumbnailImage(200,82,true);

	// Add a subtle border
	$color=new ImagickPixel();
	$color->setColor("rgb(220,220,220)");
	$im->borderImage($color,1,1);
	*/
	$output = $im->getimageblob();
	$outputtype = $im->getFormat();

	header("Content-type: $outputtype");
	echo $output;

	$postgresWS->$postgresWS->ClosePostgres();
?>

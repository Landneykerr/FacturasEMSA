<?php
  	session_start();
    include_once(dirname(__FILE__)."/../Clases/ClassPostgresBDFotos.php");
	
	$_myDataBase =  new PostgresBDFotos();
	$fecha    	= $_GET['Fecha'];
	$inspector  = $_GET['Inspector'];


    $ruta = "uploads/";
    $zip = new ZipArchive();
 	$zip->open("zip/".$inspector."_".str_replace("/","_",$fecha).".zip",ZipArchive::CREATE);
    
    $_myDataBase->OpenPostgres();   
    $query =  $_myDataBase->PostgresFunctionCamposTable( "nombre_foto, foto, fecha_toma, id_ciclo", 
                                                         "registro.exportar_fotos(".$inspector.",'".$fecha."')");     
 	
    while($row   = pg_fetch_assoc($query)){

        if(!file_exists('uploads/CICLO'.$row['id_ciclo'])){
            mkdir('uploads/CICLO_'.$row['id_ciclo']);
        }

        $rutaT = 'uploads/CICLO_'.$row['id_ciclo'];    
        $im = base64_decode($row['foto']);
        file_put_contents($rutaT."/".$row['nombre_foto'], $im);
    }    
    $_myDataBase->ClosePostgres();

    if (is_dir($ruta)) { 
      if ($dh = opendir($ruta)) { 
         while (($file = readdir($dh)) !== false) {             
            if (is_dir($ruta . $file) && $file!="." && $file!=".."){                                            
               addFolderToZip($ruta . $file . "/",$zip);
            } 
         } 
      closedir($dh); 
      } 
    }

    function addFolderToZip($dir, $zipArchive){
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {

            $zipArchive->addEmptyDir($dir);
                    
            while (($file = readdir($dh)) !== false) {
                            
                if(!is_file($dir . $file)){
            
                    if( ($file !== ".") && ($file !== "..")){
                        addFolderToZip($dir . $file . "/", $zipArchive);
                    }
                    
                }else{            
                    $zipArchive->addFile($dir . $file);
                    
                }
            }
        }
      }
    }

    $zip->close();

    function deldir($dir){ 
        $current_dir = opendir($dir); 
        while($entryname = readdir($current_dir)){ 
            if(is_dir("$dir/$entryname") and ($entryname != "." and $entryname!="..")){ 
                deldir("${dir}/${entryname}");   
            }elseif($entryname != "." and $entryname!=".."){ 
                unlink("${dir}/${entryname}"); 
            } 
    } 
        closedir($current_dir); 
        rmdir(${'dir'}); 
    }

 	header( "Content-Type: application/force-download"); 
	header( "Content-Length: ".filesize("zip/".$inspector."_".str_replace("/","_",$fecha).".zip")); 
	header( "Content-Disposition: attachment; filename=".basename("zip/".$inspector."_".str_replace("/","_",$fecha).".zip")); 
	readfile("zip/".$inspector."_".str_replace("/","_",$fecha).".zip");	

    $dir = "zip/"; 
    $handle = opendir($dir); 
    while ($file = readdir($handle))  {   if (is_file($dir.$file)) { unlink($dir.$file); }}

    deldir("uploads/");

    if(!file_exists('uploads')){
        mkdir('uploads');
    }

?>
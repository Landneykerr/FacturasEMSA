<?php
  	session_start();
    include_once(dirname(__FILE__)."/../Clases/ClassPostgresBDFotos.php");
	
	  $_myDataBase =  new PostgresBDFotos();
	  $mes    	= $_GET['Mes'];
	  $anno     = $_GET['Anno'];
    $cuentas  = $_GET['Cuentas'];
    $array    = "array[".$cuentas."]";

    $ruta = "Fotos/";
    $zip = new ZipArchive();
 	  $zip->open("zip/".$mes."_".$anno.".zip",ZipArchive::CREATE);
    
    $_myDataBase->OpenPostgres();   
    $query =  $_myDataBase->PostgresFunctionTable("registro.exportar_fotos_cuentas(".$mes.",".$anno.",".$array.")");     
 	
    while($row   = pg_fetch_assoc($query)){

        if(!file_exists('Fotos/Ciclo_'.$row['id_ciclo'])){
            mkdir('Fotos/Ciclo_'.$row['id_ciclo']);
        }

        if(!file_exists('Fotos/Ciclo_'.$row['id_ciclo']."/".$row['inspector'])){
            mkdir('Fotos/Ciclo_'.$row['id_ciclo']."/".$row['inspector']);
        }

         $rutaT = 'Fotos/Ciclo_'.$row['id_ciclo']."/".$row['inspector'];
         
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
	header( "Content-Length: ".filesize("zip/".$mes."_".$anno.".zip")); 
	header( "Content-Disposition: attachment; filename=".basename("zip/".$mes."_".$anno.".zip")); 
	readfile("zip/".$mes."_".$anno.".zip");	

  $dir = "zip/"; 
  $handle = opendir($dir); 
  while ($file = readdir($handle))  {   if (is_file($dir.$file)) { unlink($dir.$file); }}

  deldir("Fotos/");

  if(!file_exists('Fotos')){
        mkdir('Fotos');
  }

?>
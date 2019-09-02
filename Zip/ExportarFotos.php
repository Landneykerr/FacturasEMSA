<?php
  	session_start();
    include_once(dirname(__FILE__)."/../Clases/ClassPostgresBDFotos.php");
    
	 
	  $_myDataBase =  new PostgresBDFotos();

    $nombre_archivo = date('d')."_".date('m')."_".date('Y');
    $ruta = "Fotos/";

    $zip = new ZipArchive();
 	  $zip->open("Descargas/".$nombre_archivo.".zip",ZipArchive::CREATE);
    
    $_myDataBase->OpenPostgres();   
    $query =  $_myDataBase->PostgresSelectJoinWhereOrder("registro.cuentas AS a", 
                                                        "count(b.nombre_foto) AS cantidad", 
                                                        "registro.fotos AS b", 
                                                        "a.id_serial = b.id_cuenta",
                                                        "fecha_toma::date IN (SELECT current_date - integer '1')", 
                                                        "cantidad"); 
    $id_serial = 0;
    $datos = pg_fetch_assoc($query);
    $cantidad = $datos['cantidad'];

    for($i=0;$i<sizeof($cantidad);$i++){

        $query2 =  $_myDataBase->PostgresSelectJoinWhereOrder("registro.cuentas AS a", 
                                                        "b.id_serial,a.id_ciclo,a.inspector,b.foto,b.nombre_foto", 
                                                        "registro.fotos AS b", 
                                                        "a.id_serial = b.id_cuenta",
                                                        "fecha_toma::date IN (SELECT current_date - integer '1') AND b.id_serial > ".$id_serial, 
                                                        "a.id_serial ASC LIMIT 500"); 


        while($row   = pg_fetch_assoc($query2)){

          if(!file_exists('Fotos/Ciclo_'.$row['id_ciclo'])){
              mkdir('Fotos/Ciclo_'.$row['id_ciclo']);
          }

          if(!file_exists('Fotos/Ciclo_'.$row['id_ciclo']."/".$row['inspector'])){
              mkdir('Fotos/Ciclo_'.$row['id_ciclo']."/".$row['inspector']);
          }

           $rutaT = 'Fotos/Ciclo_'.$row['id_ciclo']."/".$row['inspector'];
   
           $im = base64_decode($row['foto']);
           
          file_put_contents($rutaT."/".$row['nombre_foto'], $im);  

          $id_serial = $row['id_serial'];
        }    
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

  deldir("Fotos/");

  if(!file_exists('Fotos')){
        mkdir('Fotos');
  }

?>
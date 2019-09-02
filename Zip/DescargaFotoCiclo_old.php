<?php
    session_start();
    include_once(dirname(__FILE__)."/../Clases/ClassPostgresBDFotos.php");
    
      $_myDataBase =  new PostgresBDFotos();
      $mes      = "12";
      $anno       = "2015";
      $ciclo      = "26";

    $ruta = "uploads/";
    $zip = new ZipArchive();
      $zip->open("zip/".$mes."_".$anno."_".$ciclo.".zip",ZipArchive::CREATE);
    
    $_myDataBase->OpenPostgres();   
    $query =  $_myDataBase->PostgresSelectJoinWhereOrder("registro.cuentas as a", 
                                                        "a.inspector,b.nombre_foto,b.foto", 
                                                        "registro.fotos as b",
                                                        "a.id_serial = b.id_cuenta",
                                                        "a.id_ciclo = 26 and a.fecha_proceso::date = '15-12-2015' ", 
                                                        "b.nombre_foto");
    
    while($row   = pg_fetch_assoc($query)){

        if(!file_exists('uploads/'.$row['inspector'])){
            mkdir('uploads/'.$row['inspector']);
        }

         $rutaT = 'uploads/'.$row['inspector'];
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
    header( "Content-Length: ".filesize("zip/".$mes."_".$anno."_".$ciclo.".zip")); 
    header( "Content-Disposition: attachment; filename=".basename("zip/".$mes."_".$anno."_".$ciclo.".zip")); 
    readfile("zip/".$mes."_".$anno."_".$ciclo.".zip");  

  $dir = "zip/"; 
  $handle = opendir($dir); 
  while ($file = readdir($handle))  {   if (is_file($dir.$file)) { unlink($dir.$file); }}

  deldir("uploads/");

  if(!file_exists('uploads')){
        mkdir('uploads');
  }

?>
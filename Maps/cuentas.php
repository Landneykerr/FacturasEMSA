<!DOCTYPE html>
<html>
  <head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no">
    <meta charset="utf-8">
    <title>Dashed Lines (Symbols)</title>
    <style>
      html, body, #map-canvas {
        height: 100%;
        margin: 0px;
        padding: 0px
      }
    </style>
   <script type="text/javascript" src="../FrameWork/dataTables/js/jquery.js"></script>    
   <script src="https://maps.googleapis.com/maps/api/js?v=3.exp&key=AIzaSyBLVhMqSQIQ4NfReV9ZtyZ-8RJpdg-DSaQ"></script> 
    <script>   

    function initialize(data) {  
    var coordinatesCenter = [];
    var coordinatesOne    = [];   

    coordinatesCenter = data[0]['pocision'].split(":");
    
    var mapOptions = {
      zoom: 18,
      center: new google.maps.LatLng(coordinatesCenter[0],coordinatesCenter[1])
    };

    var map = new google.maps.Map(document.getElementById('map-canvas'),
        mapOptions);

    var infowindow;
 
    for (var i=0;i<data.length;i++) {
      coordinatesOne = data[i]['pocision'].split(":");      
      var myLatLng = new google.maps.LatLng(coordinatesOne[0],coordinatesOne[1]);
      var marker = new google.maps.Marker({
        position: myLatLng,
        map: map,
        icon: "../imagenes/iconos/home.png",  
        title: data[i]['cuenta']
      });
      
      (function(i, marker) {
          google.maps.event.addListener(marker,'click',function() {
          if (!infowindow) {
            infowindow = new google.maps.InfoWindow();
          } 
            var info = "<p><b>Cuenta: </b>"+data[i]['cuenta']+"</p><p><b>Mensaje: </b>"+data[i]['medidor']+"</p><p><b>Nombre: </b>Foto Cuenta</p>"+"<img src='data:image/jpg;base64,"+data[i]['foto']+"' WIDTH='320' HEIGHT='174'></img>";
            infowindow.setContent(info);
            infowindow.open(map, marker);
          });
      })(i, marker);
     }            
    }

    </script>
    
    <script type="text/javascript">
     var idruta = '<?php echo $_GET["Id"];?>';        
     
      window.onload = function() {
        ConsultarDatos(idruta);
      }

     function ConsultarDatos(idruta){
      var idRuta       = idruta;
        

      var SendInformacionN =  $.ajax({    
                            async:    true,
                            type:     "POST",
                            dataType: "json",
                            url:      "../Ajax/AjaxVisor.php",
                            data:   {   Peticion:   "ConsultaRutaCuentas",
                                        IdRuta   :   idRuta
                                },
                            success:function(data){                                                        
                            //alert(JSON.stringify(data));
                            initialize(data);
                            }
                          });

      SendInformacionN.fail(function(jqXHR, textStatus) {
            alert( "Error en la consulta GPS." );
          });
     }

    </script>
  </head>
  <body>
    <div id="map-canvas"></div>
  </body>
</html>
<?php
	session_start();

	include_once(dirname(__FILE__)."/../Clases/ClassVisor.php");
	
	switch($_POST['Peticion']){
		case 'ConsultaRutas':			ConsultaRutas($_POST['Mes'],$_POST['Anno']);				 break;		
		case 'ConsultaRutaGps':			ConsultaRutaGps($_POST['IdRuta']);				 		     break;		
		case 'ConsultaRutaCuentas':		ConsultaRutaCuentas($_POST['IdRuta']);				 		     break;		
		
	};


	function ConsultaRutas($mes,$anno){		
		$AjaxDigitacion 	= new ClassVisor();
		echo $AjaxDigitacion->ConsultaRutas($mes,$anno);
	}

	function ConsultaRutaGps($_IdRuta){
		$AjaxDigitacion 	= new ClassVisor();
		echo $AjaxDigitacion->consultarRutaCuenta($_IdRuta);	
	}

	function ConsultaRutaCuentas($_IdRuta){
		$AjaxDigitacion 	= new ClassVisor();
		echo $AjaxDigitacion->consultarRutaCuentaDatos($_IdRuta);
	}

?>
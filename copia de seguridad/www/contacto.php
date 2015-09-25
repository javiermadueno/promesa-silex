<?php 
	
	if(sizeof($_POST) > 0 && $_POST['email']!=''){
		
		$asuntoemail = "Contacto desde web";
		
		$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
		$cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

		// // Cabeceras adicionales
		// $cabeceras .= 'To: javiermadueno@gmail.com' . "\r\n";
		$cabeceras .= 'From: Web <'. $_POST['email'].'>\r\n';
		// $cabeceras .= 'Cc: birthdayarchive@example.com' . "\r\n";
		// $cabeceras .= 'Bcc: birthdaycheck@example.com' . "\r\n";
		$cabecera = <<<EOD
		<html>
		<head>
		    <title>$asuntoemail</title>
		    <style>
		        body{font-family: 'Helvetica Neue', Helvetica, Arial, Verdana, sans-serif;color: #333;}h2{color: #177e85;}span{font-size: 12px;}ul{list-style: none;}ul li{list-style: none;}.legal{font-size:8px;}
		    </style>
		</head>
		<body>
EOD;
		$texto = $cabecera;
		$texto .= "<h2>Ha recibido un email de contacto desde su web</h2>";
		$texto .= "<p><strong>Nombre: </strong>".$_POST['nombre']."</p>";
		if(isset($_POST['telefono']) && $_POST['telefono'] != ""){
			$texto .= "<p><strong>Tel&eacute;fono: </strong>".$_POST['telefono']."</p>";
		}
		$texto .= "<p><strong>eMail: </strong>".$_POST['email']."</p>";
		$texto .= "<p><strong>Mensaje: </strong>".$_POST['mensaje']."</p>";
		$texto .= "</body></html>";

		if(mail('promesadelavirgendefatima@gmail.com', $asuntoemail, $texto, $cabeceras)){
		?>	
		<div id="alerta" class="alerta show white success">
				<div class="mensaje">
					Su mensaje se ha enviado correctamente.
				</div>
				<button class="unstyled destroy"></button>
			</div>
		<?php 
			}else{
		 ?>
		

		<div id="alerta" class="alerta show white error">
				<div class="mensaje">
					Ha habido un problema al enviar el mensaje. Por Favor, intentelo de nuevo.
				</div>
				<button class="unstyled destroy"></button>
		</div>;
<?php 		
	}
}	
 ?>	
    
<?php 

$link = mysqli_connect("localhost", "root", "", "sistema");

/* comprobar la conexión */
if (mysqli_connect_errno()) {
    printf("Falló la conexión: %s\n", mysqli_connect_error());
    exit();
}

    	
  		$numdniocuit = $_POST['numdniocuit'];
  		$dniocuit = $_POST['dniocuit'];
  		$nombre = $_POST['nombre'];
  		$domicilio = $_POST['domicilio'];
  		$localidad = $_POST['localidad'];
  		$condventa = $_POST['condventa'];
  		$condiva = $_POST['condiva'];
  		$tipocomprobante = $_POST['tipocomprobante'];
  		mysqli_query($link, "UPDATE clientetemporal	SET 
  			dniocuit = '$dniocuit', 
  			numdniocuit = '$numdniocuit',
  			nombre = '$nombre',
  			domicilio = '$domicilio',
  			localidad = '$localidad',
  			condventa = '$condventa',
  			condiva = '$condiva',
  			tipocomprobante = '$tipocomprobante'
  			WHERE id = '1'");

  		


?>
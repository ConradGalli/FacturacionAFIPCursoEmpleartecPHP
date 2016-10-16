<?php


session_start();
if(!isset($_SESSION["valido"]) OR $_SESSION["valido"] != TRUE){
  header("Location: index.php");
  exit();
}

$link = mysqli_connect("localhost", "root", "", "sistema");

/* comprobar la conexión */
if (mysqli_connect_errno()) {
    printf("Falló la conexión: %s\n", mysqli_connect_error());
    exit();
}




// Si se aprieta un botón de "-"
if(isset($_REQUEST['menos']))
    {
    	$codigo = $_REQUEST['codigo'];
    	$carrito = $_REQUEST['carrito'];
    	if ($carrito > 1) {
			mysqli_query($link, "UPDATE carritotemporal SET carrito = carrito - 1 WHERE codigo = '$codigo'");
    	} else {
    		mysqli_query($link, "DELETE FROM carritotemporal WHERE codigo = '$codigo'");
    	}

       	header('Location: '.$_SERVER['REQUEST_URI']);
    }

// Si se aprieta un botón de "+"
if(isset($_REQUEST['mas']))
    {
    	$codigo = $_REQUEST['codigo'];
    	$carrito = $_REQUEST['carrito'];
    	if ($carrito < 99) {
			mysqli_query($link, "UPDATE carritotemporal SET carrito = carrito + 1 WHERE codigo = '$codigo'");
    	}
       	
     	header('Location: '.$_SERVER['REQUEST_URI']);
    }


if(isset($_REQUEST['codigodebarras']) AND !empty($_REQUEST['codigodebarras']))
    {
    	$codigo = $_REQUEST['codigodebarras'];
  		mysqli_query($link, "INSERT INTO carritotemporal (codigo, carrito) VALUES ('$codigo', '1') ON DUPLICATE KEY UPDATE carrito = carrito + 1");


    	header('Location: '.$_SERVER['REQUEST_URI']);     	
    }


if(isset($_REQUEST['procesar']))
    {
    	
    	// Guarda en $resultado las filas que tienen 1 o mas articulos en el carrito
		$resultado = mysqli_query($link, "SELECT carritotemporal.codigo, carrito, stock, costo, pvp FROM carritotemporal, inventario WHERE carritotemporal.codigo = inventario.codigo");
		$hoy = date("Y-m-d");	
		$ganancia = 0;
		while ($row = $resultado->fetch_assoc()) {
			$ganancia+=round($row['carrito'] * ($row['pvp'] - $row['costo']));
			$carrito = $row['carrito'];
			$codigo = $row['codigo'];
			mysqli_query($link, "UPDATE inventario SET stock = stock - '$carrito' WHERE codigo = '$codigo'");
		}
		mysqli_query($link, "INSERT INTO recibos (fecha, ganancia) VALUES ('$hoy', '$ganancia')");

		//luego de haber registrado la ganancia y restado el stock, borro todo
   		mysqli_query($link, "DELETE FROM carritotemporal");
		mysqli_query($link, "UPDATE clientetemporal SET
			dniocuit = '', 
  			numdniocuit = '',
  			nombre = '',
  			domicilio = '',
  			localidad = '',
  			condventa = '',
  			condiva = '',
  			tipocomprobante = ''
			");

  	
    	header('Location: '.$_SERVER['REQUEST_URI']);    
 	
    }




use \Tordek\AfiPHP\Auth;
use \Tordek\AfiPHP\FacturaElectronica as Factura;
use \Tordek\AfiPHP\FacturaElectronica\Comprobante;

if(isset($_REQUEST['procesarfactura']))
    {
    	require "../vendor/autoload.php";

		include "barcode.class.php";




		require "configs.php";

		//$auth = new Auth\WSAAAuth($certfile, $pkey, new \SoapClient(Auth\WSAAAuth::URL_PRODUCCION));
		$auth = new Auth\WSAAAuth($certfile, $pkey, new \SoapClient(Auth\WSAAAuth::URL_HOMOLOGACION));
		$auth = new Auth\AuthCache($auth, new Auth\Storage\CredencialesDiskStorage("wsfe"));

		$fe = new Factura\FacturaElectronica($auth, $cuit);

		// Caso simple
		$lote_builder = new Factura\LoteComprobantesBuilder("0002", Comprobante::TIPO_FACTURA_C);

		$lote_builder->nuevoComprobante()
		    ->neto($_POST["total"])
		    ->concepto(Comprobante::CONCEPTO_PRODUCTOS);


		$ultimo_numero = $fe->getUltimoNumero("0002", Comprobante::TIPO_FACTURA_C);

		$lote_builder->asignarNumeros($ultimo_numero);

		$lote = $lote_builder->build();

		$resultado = $fe->solicitarCae($lote);


		// RUTINA PARA EL CALCULO DEL DIGITO VERIFICADOR según especificación: 
		// http://www.afip.gov.ar/afip/resol170204.html
		function verificador($numero) {
		    $par = 0;
		    $impar = 0;

		    // sumo los numeros en POSICIONES pares e impares por separado.
		    for ($contador = 0; $contador < strlen($numero); $contador++){
		        if (($contador % 2) == 0){
		            $par = $par + $numero[$contador];
		        } else {
		            $impar = $impar + $numero[$contador];
		        }
		    }
		    
		    // multiplico por 3 la suma de numeros en posiciones pares.
		    $par = $par * 3;

		    // calculo el total.
		    $total = $par + $impar;

		    // determino cual es el menor numero que sumado al $total me de un multiplo de 10.
		    $digitoverificador = 10 - ($total % 10);

		    // en el caso de que sea 10 reemplazo por 0, sino devuelvo el numero que corresponde.
		    if ($digitoverificador == 10) {
		        return 0;
		    } else {
		        return $digitoverificador;
		    }
		    
		}


		$CAE = $resultado->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAE;
		$CAEFchVto = $resultado->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CAEFchVto;
		$CbteDesde = $resultado->FECAESolicitarResult->FeDetResp->FECAEDetResponse->CbteDesde;

		$precodigoDeBarras = "20357487650110002".$CAE.$CAEFchVto;
		$codigoDeBarras = $precodigoDeBarras.verificador($precodigoDeBarras);

		// Guarda en $resultado las filas que tienen 1 o mas articulos en el carrito
		$resultado = mysqli_query($link, "SELECT carritotemporal.codigo, carrito, stock, costo, pvp FROM carritotemporal, inventario WHERE carritotemporal.codigo = inventario.codigo");
		$hoy = date("Y-m-d");	
		$ganancia = 0;
		while ($row = $resultado->fetch_assoc()) {
			$ganancia+=round($row['carrito'] * ($row['pvp'] - $row['costo']));
			$carrito = $row['carrito'];
			$codigo = $row['codigo'];
			mysqli_query($link, "UPDATE inventario SET stock = stock - '$carrito' WHERE codigo = '$codigo'");
		}
		$t = strtotime($CAEFchVto); 
		$fechavto = date('Y-m-d',$t);
		mysqli_query($link, "INSERT INTO facturas (num, fecha, ganancia, cae, fechavto) VALUES ('$CbteDesde', '$hoy', '$ganancia', '$CAE', '$fechavto')");


    }





if(isset($_REQUEST['borrar']))
    {
    	
   		mysqli_query($link, "DELETE FROM carritotemporal");
		mysqli_query($link, "UPDATE clientetemporal SET
			dniocuit = '', 
  			numdniocuit = '',
  			nombre = '',
  			domicilio = '',
  			localidad = '',
  			condventa = '',
  			condiva = '',
  			tipocomprobante = ''
			");

  	
    	header('Location: '.$_SERVER['REQUEST_URI']);     	
    } 


$clientetemporal = mysqli_fetch_object(mysqli_query($link, "SELECT * FROM clientetemporal WHERE id = '1'"));

echo'
<!DOCTYPE html>
<html>
	<head>
		
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Inventario</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.css" rel="stylesheet">

        <style>
            hr
            {
			    display: block;
			    -webkit-margin-before: 0.5em;
			    -webkit-margin-after: 0.5em;
			    -webkit-margin-start: auto;
			    -webkit-margin-end: auto;
			    border-style: inset;
			    border-width: 1px;
            }

			table {
				    display: table;
				    border-collapse: separate;
				    border-spacing: 2px;
				    border-color: gray;
				}   



        </style>


		<style type="text/css" media="print">
			.dontprint {
				display: none;
			}

		</style>







		<script>
		function ajax_post(){
		    // Create our XMLHttpRequest object
		    var hr = new XMLHttpRequest();
		    // Create some variables we need to send to our PHP file
		    var url = "ajax.php";
		    
		    var numdniocuit = document.getElementById("numdniocuit").innerText;
		    var dniocuit = document.getElementById("dniocuit").value;
		    var nombre = document.getElementById("nombre").innerText;
		    var domicilio = document.getElementById("domicilio").innerText;
		    var localidad = document.getElementById("localidad").innerText;
		    var condventa = document.getElementById("condventa").value;
		    var condiva = document.getElementById("condiva").value;
		    var tipocomprobante = document.getElementById("tipocomprobante").value;
		    var vars = "numdniocuit="+numdniocuit+"&dniocuit="+dniocuit+"&nombre="+nombre+"&domicilio="+domicilio+"&localidad="+localidad+"&condventa="+condventa+"&condiva="+condiva+"&tipocomprobante="+tipocomprobante;
		    
		    hr.open("POST", url, true);
		    // Set content type header information for sending url encoded variables in the request
		    hr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		    // Access the onreadystatechange event for the XMLHttpRequest object
		    hr.onreadystatechange = function() {
			    if(hr.readyState == 4 && hr.status == 200) {
				    var return_data = hr.responseText;
					document.getElementById("status").innerHTML = return_data;
			    }
		    }
		    // Send the data to PHP now... and wait for response to update the status div
		    hr.send(vars); // Actually execute the request
		    document.getElementById("status").innerHTML = "processing...";
		}


    

    </script>	

	</head>

		<body style="margin: 0px; font: 14px arial">


    <nav class="navbar navbar-default">
      <div class="container">
        <div class="navbar-header">
        </div>
        <!-- Note that the .navbar-collapse and .collapse classes have been removed from the #navbar -->
        <div id="navbar">
          <ul class="nav navbar-nav">
            <li><a href="inventario.php">Inventario</a></li>
            <li class="active"><a href="comprobante.php">Comprobante</a></li>
            <li><a href="ganancia.php">Ganancia</a></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li><a href="logout.php">Cerrar Sesion</a></li>
            <li><a>

            </a></li>
            
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>



		
		<div style="margin: auto; width: 210mm; height: 297mm">


			<nav></nav>
			<section style="margin: auto; width: 146mm; height: 210mm; border: 1px solid black">
				
				<table style="border-spacing: 0px">
					<tr>
						<td style="padding: 10px; width: 242px; text-align: center; font: bold 25px arial">
							Casa de Música y<br>Computación
						</td>
						<td style="text-align: center; width: 55px; height: 55px; border: 1px solid black">
							<span style="font: bold 50px arial">', ($clientetemporal->tipocomprobante == 'factura') ? 'C':'X' ,'</span>
							<br>
							', ($clientetemporal->tipocomprobante == 'factura') ? 'Cod. 11':'' ,'
						</td>
						
						<td style="padding-left: 15px; font: bold 24px arial">
							<select id="tipocomprobante" onclick="ajax_post();location.reload()" style="font: bold 24px arial; border: none; -webkit-appearance: none; -moz-appearance: none">
								<option value="recibo"', ($clientetemporal->tipocomprobante == 'recibo') ? ' selected':'' ,'>RECIBO</option>
								<option value="factura"', ($clientetemporal->tipocomprobante == 'factura') ? ' selected':'' ,'>FACTURA</option>
							</select>
							<br>
							Nº 0002-';

							if ($clientetemporal->tipocomprobante == 'factura') {
								// le da formato con ceros a la izquierda al num. de comprobante devuelto por AFIP
								if (isset($CbteDesde)) {
									echo str_pad($CbteDesde, 8, 0, STR_PAD_LEFT);
								} else {
									echo "00000000";
								}
							} else {
								//busca el numero del ultimo recibo
								$num = mysqli_fetch_object(mysqli_query($link, "SELECT num FROM recibos ORDER BY num DESC LIMIT 1"));
								// le suma 1 al ultimo recibo y le da formato con ceros a la izquierda
								echo str_pad($num->num + 1, 8, 0, STR_PAD_LEFT);
							}
							


						echo'</td>

					</tr>
					<tr>
						<td style="padding-left: 27px; font: 15px arial">
							De Espinosa David Emanuel
							<br>
							Santa Fe 718, San Genaro
							<br>
							CP: 2147, Prov. Santa Fe
							<br>
							<b>Responsable Monotributo</b>
						</td>
						<td></td>
						<td style="padding-left: 15px">
							<b>Fecha:</b> 11/09/2016
							<br>
							<b>CUIT:</b> 20-35748765-0
							<br>
							<b>Ingresos Brutos:</b> 083-004946-0
							<br>
							<b>Inicio de Actividades:</b> 01/09/13
						</td>
					</tr>
				</table>
				<hr>



				<table style="padding-left: 10px">
					<tr>
						<td>
							<b>Nombre:</b>
						</td>
						<td contenteditable="true" style="width: 292px" id="nombre" onfocusout="ajax_post()">
							'.$clientetemporal->nombre.'
						</td>
						<td>
							<select id="dniocuit" onclick="ajax_post()" style="border: none; font-weight: bold; -webkit-appearance: none; -moz-appearance: none">
								<option value="dni"', ($clientetemporal->dniocuit == 'dni') ? ' selected':'' ,'>DNI:</option>
								<option value="cuit"', ($clientetemporal->dniocuit == 'cuit') ? ' selected':'' ,'>CUIT:</option>	
							</select>
						</td>



						
						<td contenteditable="true" style="width: 130px" id="numdniocuit" onfocusout="ajax_post()">
							'.$clientetemporal->numdniocuit.'
						</td>
						



					</tr>
				</table>
				<table style="padding-left: 10px">
					<tr>
						<td>
							<b>Domicilio:</b>
						</td>
						<td contenteditable="true" style="width: 282px" id="domicilio" onfocusout="ajax_post()">
							'.$clientetemporal->domicilio.'
						</td>
						<td>
							<b>Localidad:</b>
						</td>
						<td contenteditable="true" style="width: 90px" id="localidad" onfocusout="ajax_post()">
							'.$clientetemporal->localidad.'
						</td>
					</tr>
				</table>
				<table style="padding-left: 10px; padding-bottom: 8px">
					<tr>
						<td>
							<b>Cond. de Venta:</b>
						</td>
						<td style="width: 240px">
							<select id="condventa" onclick="ajax_post()" style="border: none; font-size: 14px; -webkit-appearance: none; -moz-appearance: none">
								<option value="contado"', ($clientetemporal->condventa == 'contado') ? ' selected':'' ,'>Contado</option>
								<option value="tarjeta"', ($clientetemporal->condventa == 'tarjeta') ? ' selected':'' ,'>Tarjeta</option>	
							</select>
						</td>
						<td>
							<b>Cond. IVA:</b>
						</td>
						<td>
							<select id="condiva" onclick="ajax_post()" style="border: none; font-size: 14px; -webkit-appearance: none; -moz-appearance: none">
								<option value="consumidorfinal"', ($clientetemporal->condiva == 'consumidorfinal') ? ' selected':'' ,'>Cons. Final</option>
								<option value="responsableinscripto"', ($clientetemporal->condiva == 'responsableinscripto') ? ' selected':'' ,'>Resp. Inscripto</option>
								<option value="monotributista"', ($clientetemporal->condiva == 'monotributista') ? ' selected':'' ,'>Monotributista</option>
								<option value="exento"', ($clientetemporal->condiva == 'exento') ? ' selected':'' ,'>Exento</option>									
							</select>
						</td>
					</tr>
				</table>
				
				<table style="border-collapse: collapse">
					<tr>
						<th style="width: 70px; height: 25px; border: 1px solid black">
							Cant.
						</th>
						<th style="width: 348px; border: 1px solid black">
							Descripción
						</th>
						<th style="width: 72px; border: 1px solid black">
							Prec. Unit.
						</th>
						<th style="width: 60px; border: 1px solid black">
							Subtotal
						</th>
					</tr>';




$total = 0;  
//$result = mysqli_query($link, "SELECT codigo, descripcion, pvp, carrito FROM inventario WHERE carrito > 0");  
$result = mysqli_query($link, "SELECT * FROM inventario JOIN carritotemporal ON inventario.codigo = carritotemporal.codigo WHERE carrito > 0");  

if (mysqli_num_rows($result) > 0) {
    // output data of each row
    $counter = 0;
	
    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

    	echo '
			<tr>
				<td style="text-align: center; height: 20px; border: 1px solid black">
					<form method="post" style="display: inline">
    					<input type="hidden" name="codigo" value="'.$row["codigo"].'"/>
    					<input type="hidden" name="carrito" value="'.$row["carrito"].'"/>
    					<input type="submit" value="-" name="menos" style="cursor: pointer" class="dontprint"/>
    				</form>
    				'.$row["carrito"].'
    				<form method="post" style="display: inline">
    					<input type="hidden" name="codigo" value="'.$row["codigo"].'"/>
    					<input type="hidden" name="carrito" value="'.$row["carrito"].'"/>
    					<input type="submit" value="+" name="mas" style="cursor: pointer" class="dontprint"/>
    				</form>
				</td>
				<td style="height: 20px; padding-left: 5px; border: 1px solid black">
					'.$row["descripcion"].'
				</td>
				<td style="text-align: center; height: 20px; border: 1px solid black">
					$'.$row["pvp"].'
				</td>
				<td style="text-align: center; height: 20px; border: 1px solid black">
					$'.$row["pvp"] * $row["carrito"].'
				</td>
			</tr>
        ';
        $counter++;
        $total += $row["pvp"] * $row["carrito"];
    }


    while ($counter < 17) {
    	echo '
			<tr>
				<td style="text-align: center; height: 23px; border: 1px solid black">
				
				</td>
				<td style="height: 20px; padding-left: 5px; border: 1px solid black">
							
				</td>
				<td style="text-align: center; height: 20px; border: 1px solid black">
						
				</td>
				<td style="text-align: center; height: 20px; border: 1px solid black">
							
				</td>
			</tr>    		
    	';
    	$counter++;
    }

} else {
	$counter = 0;
	while ($counter < 17) {
    	echo '
			<tr>
				<td style="text-align: center; height: 23px; border: 1px solid black">
				
				</td>
				<td style="height: 20px; padding-left: 5px; border: 1px solid black">
							
				</td>
				<td style="text-align: center; height: 20px; border: 1px solid black">
						
				</td>
				<td style="text-align: center; height: 20px; border: 1px solid black">
							
				</td>
			</tr>    		
    	';
    	$counter++;
    }	
}




echo'					


					<tr>
						<td colspan="3" style="text-align: right; padding-right: 5px; height: 23px; border: 1px solid black">
							<b>TOTAL:</b>
						</td>
						<td style="text-align: center; height: 20px; border: 1px solid black">
							$'.$total.'
						</td>
					</tr>
				</table>

				<table style="padding: 12px">
';
				if (isset($CAE)) {
					echo '
					<tr>
						<td style="padding-top: 12px">
							<img src="newsample.php?CAE='.$codigoDeBarras.'" alt="codigo de barras"/>
							<br>
							'.$codigoDeBarras.'
						</td>
						<td style="text-align: center; padding-top: 10px">
							<span style="font-size: 19px">ORIGINAL</span>
							<br>
							<p style="line-height: 150%; font-size: small">
							<b>CAE:</b> '.$CAE.'
							<br>
							<b>Vto. de CAE:</b> ';echo date('d/m/y',$t);
							echo'
							</p>
						</td>
					</tr>
					';

   		mysqli_query($link, "DELETE FROM carritotemporal");
		mysqli_query($link, "UPDATE clientetemporal SET
			dniocuit = '', 
  			numdniocuit = '',
  			nombre = '',
  			domicilio = '',
  			localidad = '',
  			condventa = '',
  			condiva = '',
  			tipocomprobante = ''
			");

  		

				} else {
					echo '
				<tr>
					<td class="dontprint">
			    					

						<div style="float:left">
						<form method="post">
							Código: <input style="margin-top: 25px; margin-right:5px" type="text" size="9" style="height: 20px" name="codigodebarras">
						</form>
						</div>
';
						
						if ($clientetemporal->tipocomprobante == 'factura') {
							echo'
							<div style="float:left; margin-top: 23px">
							<form method="post">
								<input type="hidden" name="total" value="'.$total.'"/>
								<input type="submit" name="procesarfactura" value="SOLICITAR CAE AFIP" style="height: 26px">
							</form>
							</div>							


							';
						} else {
							echo'
							<div style="float:left; margin-top: 23px">
							<form method="post">
							
								<input type="submit" name="procesar" value="PROCESAR" style="height: 26px">
							
							</form>
							</div>
							<div style="float:left; margin-top: 23px">
							<form method="post">
								<input type="submit" name="procesar" onclick="window.print()" value="PROCESAR E IMPRIMIR" style="height: 26px">
							</form>
							</div>
							';
						}
						
echo'




						<div style="float:left; margin-top: 23px">
						<form method="post">
							<input type="submit" name="borrar" value="BORRAR" style="height: 26px">
						</form>
						</div>
						
						<div id="status"></div>


					</td>
				</tr>
					';
				}
echo'

			



				</table>

			</section>	
';

			if (isset($CAE)) {
			echo'
			<div class="dontprint" style="margin-left:350px; margin-top:10px">
			<form method="post">
				<input type="submit" onclick="window.print()" name="borrar" value="IMPRIMIR" style="height: 26px">
			</form>
			</div>
			';}
echo'


		
		</div>

	</body>
</html>
';



mysqli_close($link);
?>


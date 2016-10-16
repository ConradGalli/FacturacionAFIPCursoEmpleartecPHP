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





?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Ganancia</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/grid.css" rel="stylesheet">


  </head>
  <body>
        
    <nav class="navbar navbar-default">
      <div class="container">
        <div class="navbar-header">
        </div>
        <!-- Note that the .navbar-collapse and .collapse classes have been removed from the #navbar -->
        <div id="navbar">
          <ul class="nav navbar-nav">
            <li><a href="inventario.php">Inventario</a></li>
            <li><a href="comprobante.php">Comprobante</a></li>
            <li class="active"><a href="ganancia.php">Ganancia</a></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li><a href="logout.php">Cerrar Sesion</a></li>
            <li><a>
<?php 
/*

include("simple_html_dom.php");

  $html = @file_get_html('http://www.air-computers.com/air2011/index.php');

if (!empty($html)){ 
echo "DÓLAR $";
$scripts = $html->find('script');
    foreach($scripts as $s) {
        if(strpos($s->innertext, 'ndolar') !== false) {
            echo $s;
        }
    }

}

*/


?>

            </a></li>
            
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container">

<?php


$result = mysqli_query($link, "SELECT fecha, SUM(ganancia) AS total FROM facturas GROUP BY fecha");  


// Tabla de ganancia diaria
echo'

<div id="middle" align="center">
<table style="width:280px" class="table table-bordered">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Ganancia Diaria</th>
    </tr>
    </thead>
    <tbody>
  ';

  // inicializa variable que suma los subtotales de la tabla
  $total = 0;

  
  while ($row = $result->fetch_assoc()) {
  
  echo '<tr><td>'.$row['fecha'].'</td><td>$'
  .$row['total'].'</td></tr>'
  ;
  
  // variable que en cada loop suma el valor del subtotal de esa fila
  $total += $row['total']; 
  
  
    }

  echo '<tr><td><p><b>GANANCIA TOTAL:</b></p></td>';
  echo '<td>' . '$' .$total.'</td></tr>';
  
echo'
  </tbody>
    </table>
  </div>
  ';





?>

        </div>
  </body>
</html>
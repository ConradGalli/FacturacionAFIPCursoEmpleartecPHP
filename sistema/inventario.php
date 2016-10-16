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

if(isset($_REQUEST['codigodebarras']))
    {
      $codigo = $_REQUEST['codigodebarras'];
      mysqli_query($link, "INSERT INTO carritotemporal (codigo, carrito) VALUES ('$codigo', '1') ON DUPLICATE KEY UPDATE carrito = carrito + 1");


      header('Location: comprobante.php');       
    }


if(isset($_REQUEST['borrar']))
    {
      $codigo = $_REQUEST['codigo'];
      mysqli_query($link, "DELETE FROM inventario WHERE codigo = '$codigo'");
    
      header('Location: '.$_SERVER['REQUEST_URI']);       
    } 



?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Inventario</title>

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
            <li class="active"><a href="inventario.php">Inventario</a></li>
            <li><a href="comprobante.php">Comprobante</a></li>
            <li><a href="ganancia.php">Ganancia</a></li>
          </ul>
          <ul class="nav navbar-nav navbar-right">
            <li><a href="logout.php">Cerrar Sesion</a></li>
            <li><a>
<?php 


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




?>

            </a></li>
            
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Código</th>
                  <th>Artículo</th>
                  <th>Stock</th>
                  <th>Costo</th>
                  <th>%</th>
                  <th>Ganancia</th>
                  <th>PVP</th>
                  <th style="padding:5px"><a href="cargar.php" type="button" class="btn btn-success">CARGAR NUEVO</a></th>
                </tr>
              </thead>
              <tbody>
<?php

$result = mysqli_query($link, "SELECT * FROM inventario");  

if (mysqli_num_rows($result) > 0) {
    // output data of each row
    $counter = 0;
  
    while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {

echo'
                <tr>
                  <td>'.$row['codigo'].'</td>
                  <td>'.$row['descripcion'].'</td>
                  <td>'.$row['stock'].'</td>
                  <td>$'.$row['costo'].'</td>
                  <td>'.round(($row['pvp'] - $row['costo']) * 100 / $row['costo']).'%</td>
                  <td>$'.($row['pvp'] - $row['costo']).'</td>
                  <td>$'.$row['pvp'].'</td>
                  <td style="padding:5px">
                    <form method="post" style="display: inline">
                    <input type="hidden" name="codigodebarras" value="'.$row['codigo'].'"/>                    
                    <input value="+" type="submit" class="btn btn-primary dontprint">
                    </form>
                    <form method="post" style="display: inline">
                    <input type="hidden" name="codigo" value="'.$row['codigo'].'"/>                    
                    <input value="Borrar" type="submit" name="borrar" class="btn btn-danger" onclick="return confirm(\'Estas seguro que querés borrar '.$row['descripcion'].'?\')">
                    </form>
                    <a href="actualizar.php?codigo=' . $row['codigo'] . '" type="button" class="btn btn-success">Actualizar</a>
                  </td>                  
                </tr>';
}
}




?>
              </tbody>
            </table>
          </div>
        </div>
  </body>
</html>
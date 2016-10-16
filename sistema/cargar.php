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


if(isset($_REQUEST['cargar']))
    {
      $codigo = !empty($_REQUEST['codigo']) ? $_REQUEST['codigo'] : '';
      $descripcion = !empty($_REQUEST['descripcion']) ? $_REQUEST['descripcion'] : '';
      $stock = !empty($_REQUEST['stock']) ? $_REQUEST['stock'] : '';
      $costo = !empty($_REQUEST['costo']) ? $_REQUEST['costo'] : '';
      $pvp = !empty($_REQUEST['pvp']) ? $_REQUEST['pvp'] : '';
         

      mysqli_query($link, "INSERT INTO inventario (codigo, descripcion, stock, costo, pvp)
      VALUES ('$codigo', '$descripcion', '$stock', '$costo', '$pvp')");
      

        header('Location: inventario.php');
    }



?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Cargar</title>

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


echo'

<div align="center">

<form style="width:500px" method="post">
    <fieldset>
  
    CARGAR ARTÍCULO:
  <br><br>
        <div class="control-group">
            <input class="form-control" name="codigo" placeholder="Código:" type="text"/>
        </div>
        <div class="control-group">
            <input class="form-control" name="descripcion" placeholder="Articulo:" type="text"/>
        </div>        
        <div class="control-group">
            <input class="form-control" name="stock" placeholder="Stock:" type="text"/>
        </div>
        <div class="control-group">
            <input class="form-control" name="costo" placeholder="Costo:" type="text"/>
        </div>
        <div class="control-group">
            <input class="form-control" name="pvp" placeholder="Precio de Venta al Público:" type="text"/>
        </div>
    
    <br>
    
        <div class="control-group">
            <button type="submit" value="Submit" name="cargar" class="btn">Cargar nuevo artículo</button>
        </div>
  </fieldset>
</form> 

</div>

';






?>

        </div>
  </body>
</html>
<?php

session_start();

$link = mysqli_connect("localhost", "root", "", "sistema");

/* comprobar la conexión */
if (mysqli_connect_errno()) {
    printf("Falló la conexión: %s\n", mysqli_connect_error());
    exit();
}


if(!empty($_POST)){
  $usr = trim($_POST["usuario"]);
  $pass = trim($_POST["clave"]);
  if(
    ($usr == "test@gmail.com" AND $pass == "david")
  ){
    $_SESSION["valido"] = TRUE;
    header("Location: inventario.php");
    exit();
  }
}



?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Sistema</title>

    <!-- Bootstrap core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles for this template -->
    <link href="css/signin.css" rel="stylesheet">

  </head>

  <body>

    <div class="container">

    <h1 style="text-align: center">Sistema de control de stock, ganancias y facturación electrónica</h1>
    <br><br><br>
      <form class="form-signin" method="POST">
        <h3 class="form-signin-heading">Ingrese sus datos:</h2>
        <label for="inputEmail" class="sr-only">Email address</label>
        <input type="email" id="inputEmail" name="usuario" class="form-control" placeholder="Email address" required autofocus>
        <label for="inputPassword" class="sr-only">Password</label>
        <input type="password" id="inputPassword" name="clave" class="form-control" placeholder="Password" required>

        <button class="btn btn-lg btn-primary btn-block" type="submit">Entrar</button>
      </form>

    </div> <!-- /container -->

  </body>
</html>

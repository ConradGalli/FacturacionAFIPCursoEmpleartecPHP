<?PHP
include "barcode.class.php";

  $bc = new BarcodeI25();
  $bc->tipoRetorno = 1; // 1 = imagen PNG
  $bc->SetCode($_GET["CAE"]);
  $bc->Generate();


?>
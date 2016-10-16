<?php

/* en windows:
$certfile = 'file://C:/xampp/htdocs/facturaDavid/examples/DAVIDESPINOSA.crt';
$pkey = ['file://C:/xampp/htdocs/facturaDavid/examples/MiClavePrivada', ''];
*/

$certfile = 'file://DAVIDESPINOSA.crt';
$pkey = ['file://MiClavePrivada', ''];


$cuit = (float)"20357487650";

// Si te da un error de CUIT 2147483647 probá poner float adelante del CUIT:
// $cuit = (float)"22111111110";

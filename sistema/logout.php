<?php

session_start();
unset($_SESSION);
session_destroy();
/*

$_SESSION["valido"] = FALSE;
*/

header("Location: index.php");
exit();

?>
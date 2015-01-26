<?php
include_once "lib/functions.php";

if(IsLogged())
{
	header('Location:index.php');
    die();
}
else if(isset($_POST["nif_login"]) && isset($_POST["pass_login"]))
{
	$nif  = $_POST["nif_login"];
	$pass = $_POST["pass_login"];
	
	Login($nif, $pass);	
}
else
{
    $msg = "Datos inválidos.";
    $_SESSION["error"] = $msg;
	header('Location:login.php');
    die();
}

?>
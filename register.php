<?php
include_once "lib/functions.php";

if(IsLogged())
{
	header('Location:index.php');
    die();
}
else if(isset($_POST["nif_login"], $_POST["pass_login"], $_POST["g-recaptcha-response"]) == true)
{
	$nif      = strtoupper($_POST["nif_login"]);
	$pass     = $_POST["pass_login"];
    $captcha  = $_POST["g-recaptcha-response"];
    
    $response = NULL;
    $url      = getCaptchaValidationURL($captcha);
    $data     = file_get_contents($url);
    if($data !== FALSE)
        $response = json_decode($data);
    
    if($response != NULL && $response->success == true)
    {
        Login($nif, $pass);	
        die();
    }
}

$msg = "Datos inválidos. Revise los datos y vuelva a intentarlo.";
$_SESSION["error"] = $msg;
header('Location:login.php');
die();

?>
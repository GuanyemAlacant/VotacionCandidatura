<?php
//Protegido por seguridad

// descomentar en modo debug
//ini_set('display_errors', '1');

define("FromMail", "web@guanyemalacant.net");
define("BaseURL", "http://candidaturas.guanyemalacant.net");

define("LOG_MSG",  1);
define("LOG_WARN", 2);
define("LOG_ERROR",  3);

define("USE_HTTP_ONLY", true);

define("ADD_VOTE_TRACE", false);

define("ADMIN_CODE_ID", 0);

//--
// Base de datos
function getBBDD()
{
    // Datos de conexión a la base de datos
    $dbinfo = 'mysql:host=localhost;dbname=candidaturas';
    $dbuser = "root";
    $dbpass = "root";
    
    //--
    $conn = new PDO($dbinfo, $dbuser, $dbpass);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("set names utf8");
	return $conn;
}

//--
function getCaptchaValidationURL($data)
{
    $captchaSecret = "GOOGLE_CAPTCHA_CODE";
    
    $url = "https://www.google.com/recaptcha/api/siteverify";
    $url = $url."?secret=".$captchaSecret."&response=".urlencode($data)."&remoteip=".$_SERVER['REMOTE_ADDR'];
    return $url;
}

//--
//Configuración Twig - Motor de plantillas
//Cargador de Twig
//Realpath nos da la ruta absoluta de ese directorio.
require_once realpath(dirname(__FILE__)."/../vendor/twig/twig/lib/Twig/Autoloader.php");

//Inicializamos el motor de plantillas (twig)
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem(realpath(dirname(__FILE__)."/../views"));
$twig   = new Twig_Environment($loader);

?>
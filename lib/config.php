<?php
//Protegido por seguridad

// descomentar en modo debug
//ini_set('display_errors', '1');

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
//Configuración Twig - Motor de plantillas
//Cargador de Twig
//Realpath nos da la ruta absoluta de ese directorio.
require_once realpath(dirname(__FILE__)."/../vendor/twig/twig/lib/Twig/Autoloader.php");

//Inicializamos el motor de plantillas (twig)
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem(realpath(dirname(__FILE__)."/../views"));
$twig   = new Twig_Environment($loader);

?>
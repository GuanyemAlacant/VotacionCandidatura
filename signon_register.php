<?php
include_once "lib/functions.php";

if(IsLogged() == true)
{
    die();
}

$result = "false";
if(isset($_POST) && count($_POST) > 0)
{
    
    $body = "<h2>Solicitud de inscripción de votante</h2>";
    $body .= "<br />Nombre: ".$_POST["signon_nombre"];
    $body .= "<br />Apellidos: ".$_POST["signon_apellidos"];
    $body .= "<br />NIF: ".$_POST["signon_nif"];
    $body .= "<br />Dirección: ".$_POST["signon_direccion"];
    $body .= "<br />C.P.: ".$_POST["signon_cp"];
    $body .= "<br />email: ".$_POST["signon_email"];
    $body .= "<br />Tel: ".$_POST["signon_telefono"];
    $body .= "<br />nacimiento: ".$_POST["signon_fecha_nacimiento"];
    $body .= "<br />acepta manifiesto: ".$_POST["signon_manifiesto"];
    $body .= "<br />acepta newsletter: ".$_POST["signon_newsletter"];
    
    $body .= "<br />";
    $body .= "<br />Datos para copiar en el fichero Excel:";
    $body .= "<br />";
    $body .= "<pre>";
    $body .= "'".$_POST["signon_nombre"]."',\t";
    $body .= "'".$_POST["signon_apellidos"]."',\t";
    $body .= "'".$_POST["signon_nif"]."',\t";
    $body .= "'".$_POST["signon_direccion"]."',\t";
    $body .= "'".$_POST["signon_cp"]."',\t";
    $body .= "'".$_POST["signon_email"]."',\t";
    $body .= "'".$_POST["signon_telefono"]."',\t";
    $body .= "'".$_POST["signon_fecha_nacimiento"]."',\t";
    $body .= "'".$_POST["signon_manifiesto"]."',\t";
    $body .= "'".$_POST["signon_newsletter"]."'";
    $body .= "</pre>";
    
    
    $num = SendMailMultiAttach("casensio@gmail.com", "Inscripcion", $body, $_FILES["file"]["tmp_name"], $_FILES["file"]["name"], "casensio@gmail.com");
    
    if($num > 0)
    {
        // TODO: Guardar el email para evitar duplicados?
        $result = "true";
    }
    
    // TODO: Delete temp files
}

echo $result;

?>
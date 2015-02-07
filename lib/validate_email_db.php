<?php
include_once "config.php";

$valid = 'false';

//Nos intentamos conectar:
try {
    $db = getBBDD();

    // Para hacer debug cargaríamos a mano el parámetro, descomentaríamos la siguiente línea:
    if (isset($_REQUEST['signon_email'])) 
    {
        // La línea siguiente la podemos descomentar para ver desde firebug-xhr si se pasa bien el parámetro desde el formulario
        //echo $_REQUEST['signon_email'];
        $email = $_REQUEST['signon_email'];
        $sql   = $db->prepare("(SELECT id FROM cnd_signon WHERE email=?) UNION (SELECT id FROM cnd_users WHERE email=?)");
        $sql->bindParam(1, $email, PDO::PARAM_STR);
        $sql->bindParam(2, $email, PDO::PARAM_STR);
        $sql->execute();
        
        $valid = ($sql->rowCount() > 0) ? 'false' : 'true';
    }
}
catch (Exception $e)
{
}

$sql=null;
$db = null;
echo $valid;
?>
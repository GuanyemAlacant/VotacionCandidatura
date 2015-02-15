<?php
include_once "../lib/functions.php";


try
{
    $newPass = "";
    $conn   = getBBDD();

    // step1: check exists
    $user = FALSE;
    $result   = $conn->prepare("SELECT * FROM cnd_users WHERE password IS NULL LIMIT 0, 1");
    $result->execute(array());
    if($result->rowCount() > 0)
    {
        $user = $result->fetch(PDO::FETCH_ASSOC);
    }

    if($user !== FALSE)
    {
        $id       = $user['id'];
        $hasher   = new PasswordHash(8, FALSE);
        $newPass  = GeneratePassword();
        $new_hash = $hasher->HashPassword($newPass);
        $userData = array(
            'id' => $id,
            'new_pass' => $new_hash
        );
        $result = $conn->prepare("UPDATE cnd_users SET password=:new_pass WHERE id=:id;");
        if($result->execute($userData) !== FALSE && $result->rowCount() > 0)
        {
            SendEmail_AlertPassGenerated($user['email'], $newPass);
            $error = "Enviado correctamente.";
        }
    }
    else
    {
        $error = "No existen registros para procesar.";
    }
}
catch(PDOException $e)
{
    $error = "Se ha producido un error al cambiar la contraseña. ". $e->getMessage();
}
catch(Exception $e)
{
    $error = "Se ha producido un error al cambiar la contraseña. ". $e->getMessage();
}

if(strlen($error) > 0)
{
    echo $error;
}

?>
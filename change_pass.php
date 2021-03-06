<?php
include_once "lib/functions.php";

if(isset($_POST["nif_login"]) && isset($_POST["old_pass_login"]) && isset($_POST["new_pass_login"]))
{
	$nif      = addslashes(strtoupper($_POST["nif_login"]));
	$old_pass = addslashes($_POST["old_pass_login"]);
	$new_pass = addslashes($_POST["new_pass_login"]);
    
    // Creamos el objeto que nos permitirá gestionar nuestro hash
	$hasher   = new PasswordHash(8, FALSE);
	$new_hash = $hasher->HashPassword($new_pass);
	$hasher   = new PasswordHash(8, FALSE);
	
	try
	{
        $conn   = getBBDD();
        
        
        $userData   = array(
            'nif' => $nif
        );
        
        // step1: check exists
        $user     = FALSE; 
        $result   = $conn->prepare("SELECT * FROM cnd_users WHERE nif=:nif");
        $result->execute($userData);
        if($result->rowCount() == 1)
        {
            $user = $result->fetch(PDO::FETCH_ASSOC);
        }

        if($user !== FALSE && $hasher->CheckPassword($old_pass, $user['password']))
        {
            $userData   = array(
                'nif' => $nif,
                'new_pass' => $new_hash
            );
            $result = $conn->prepare("UPDATE cnd_users SET password=:new_pass WHERE nif=:nif;");
            $result->execute($userData);
            
            // TODO: Check update count

            if(SendEmail_AlertPassChanged($user['email'], null) == false)
            {
                $error = "Se ha producido un error al generar su correo de confirmación. A pesar de esto su contraseña se ha cambiado correctamente. Si este punto se repite continuadamente pongase en contacto con nosotros: candidaturaguanyem@gmail.com";
            }
        }
        else
        {
            $error = "El nif o la contraseña indicada son inválidas.";
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
	
	$conn = null;
    
    // Show confirm message
    $msg = "Se ha cambiado la contraseña con exito.";
    if(isset($error))
        $msg = $error;
    $datos = array('title' => "Cambiar contraseña",
                    'msg' => $msg,
                    'user' => GetAuthenticated());

    $template = $twig->loadTemplate('message.html');
    echo $template->render($datos);
}
else
{
    header('Location:change.php');
    die();
}
?>
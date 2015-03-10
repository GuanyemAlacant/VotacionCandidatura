<?php
include_once "lib/functions.php";

if(isset($_POST["nif_login"]))
{
	$nif = addslashes($_POST["nif_login"]);
	
	try
	{   
		$conn   = getBBDD();
        
        // step0: remove expired temporal hashes
        $result = $conn->prepare("DELETE FROM cnd_newpass WHERE creation < DATE_SUB(NOW(), INTERVAL 1 DAY);");
        $result->execute();
        
        // step1: check nif exists
        $user   = FALSE;
        $data   = array($nif);
        $result = $conn->prepare("SELECT * FROM cnd_users WHERE nif=?");
        $result->execute($data);
        if($result->rowCount() == 1)
        {
            $user = $result->fetch(PDO::FETCH_ASSOC);
        }
        
        if($user !== FALSE)
        {
            if(strlen($user['email']) > 0)
            {
                // step2: create temporal hash to confirm change
                $user_id     = $user['id'];
                $hash        = CreateConfirmationHash($user_id, time(), $nif);
                $newPassData = array('user_id' => $user_id,
                                    'hash' => $hash);
                $result      = $conn->prepare("INSERT INTO cnd_newpass (user_id, hash) VALUES (:user_id, :hash) ON DUPLICATE KEY UPDATE hash=:hash;");

                $result->execute($newPassData);

                // step3: send confirmation email to user
                if(SendEmail_ComfirmPassSet($user['email'], $nif, $hash) == false)
                {
                    $error = "Se ha producido un error al generar su confirmación. Por favor, espere unos minutos y vuelva a intentarlo. Si este punto se repite continuadamente pongase en contacto con nosotros: candidaturaguanyem@gmail.com";
                }
            }
            else
            {
                $error = "Usted no tiene un correo electrónico asignado, si desea que le asociemos una cuenta de correo indiquenos su DNI y la dirección de email a: candidaturaguanyem@gmail.com";
            }
        }
        else
        {
            $error = "El nif indicado es inválido.";
        }
	}
	catch(PDOException $e)
	{
		$error = "Se ha producido un error al enviar la confirmación para restablecer la contraseña. ". $e->getMessage();
	}
    catch(Exception $e)
    {
		$error = "Se ha producido un error al enviar la confirmación para restablecer la contraseña. ". $e->getMessage();
    }
	
	$conn = null;
    
    // Show confirm message
    $msg = "Se ha enviado la confirmación para restablecer la contraseña con exito.";
    if(isset($error))
        $msg = $error;
    $datos = array('title' => "Restablecer contraseña",
                    'msg' => $msg,
                    'user' => GetAuthenticated());

    $template = $twig->loadTemplate('message.html');
    echo $template->render($datos);
    die();
}
else
{
    header('Location:index.php');
    die();
}

?>
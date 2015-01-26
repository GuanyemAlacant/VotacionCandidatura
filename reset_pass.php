<?php
include_once "lib/functions.php";

if(isset($_GET["hash"]))
{   
	try
	{   
		$conn   = getBBDD();
        
        // step0: remove expired temporal hashes
        $result = $conn->prepare("DELETE FROM cnd_newpass WHERE creation < DATE_SUB(NOW(), INTERVAL 1 DAY);");
		$result->execute();
        
        // step1: check hash exists
        $hash     = $_GET["hash"];
        $data     = array('hash' => $hash);
        $result   = $conn->prepare("SELECT * FROM cnd_newpass WHERE hash=:hash;");
		$result->execute($data);
		$user = $result->fetch(PDO::FETCH_ASSOC);

        if($user !== FALSE)
        {
            // step2: remove hash row
            $user_id  = $user['user_id'];
            $data     = array('user_id' => $user_id);
            $result   = $conn->prepare("DELETE FROM cnd_newpass WHERE user_id=:user_id;");
            $result->execute($data);

            // step3: create the new password
            $pass   = GeneratePassword();
            $hasher = new PasswordHash(8, FALSE);
            $hash   = $hasher->HashPassword($pass);

            // step4: store the password
            $data = array('pass' => $hash,
                            'id' => $user_id);
            $result   = $conn->prepare("UPDATE cnd_users SET password=:pass WHERE id=:id;");
            $result->execute($data);
            
            //TODO: Check update count
            
            // step5: Send an email
            $data = GetUserDataById($user_id);
            SendEmail_AlertPassSet($data['email'], $pass." - ".$hash);
        }
        else
        {
            $error = "La solicitud indicada es inválida.";
        }
	}
	catch(PDOException $e)
	{
		$error = "Se ha producido un error al restablecer la contraseña. ". $e->getMessage();
	}
    catch(Exception $e)
    {
		$error = "Se ha producido un error al restablecer la contraseña. ". $e->getMessage();
    }
	
	$conn = null;
    
    // Show confirm message
    $msg = "Se ha restaurado la contraseña con exito.";
    if(isset($error))
        $msg = $error;
    $datos = array('title' => "Restablecer contraseña",
                   'msg' => $msg);

    $template = $twig->loadTemplate('message.html');
    echo $template->render($datos);
}
else
{
    header('Location:index.php');
}

?>
<?php
include_once "lib/functions.php";

$canVote     = false;
$voteExpired = false;


if($canVote && $voteExpired == false && IsLogged() == false)
{   
    header('Location:login.php');
    die();
}
else
{
    
    // 
	try
	{   
        $conn   = getBBDD();
        $candidates = NULL;
        $result     = $conn->prepare("SELECT * FROM cnd_candidatos ORDER BY RAND();");
        $result->execute();
        if($result->rowCount() > 0)
        {
            $candidates = $result->fetchAll(PDO::FETCH_ASSOC);
        }

        $userData = GetAuthenticated();
        $user_id  = $userData['id'];
        $datos = array('voteExpired' => $voteExpired,
                      'canVote' => $canVote,
                      'user' => $user_id,
                      'candidates' => $candidates);


	}
	catch(PDOException $e)
	{
		$error = "Se ha producido un error al restablecer la contraseña. ". $e->getMessage();
	}
    catch(Exception $e)
    {
		$error = "Se ha producido un error al restablecer la contraseña. ". $e->getMessage();
    }
    
    $template = $twig->loadTemplate('index.html');
    echo $template->render($datos);
}

?>
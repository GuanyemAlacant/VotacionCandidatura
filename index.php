<?php
include_once "lib/functions.php";


include_once "lib/logged_check.php";


//--
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

    //$userData = GetAuthenticated();
    //$user_id  = $userData['id'];
    $datos = array('voteExpired' => $voteExpired,
                    'canVote' => $canVote,
                    'candidates' => $candidates,
                    'user' => $user,
                    'simulation' => $simulation,
                    'num_votes' => NUM_MAX_VOTES);
}
catch(Exception $e)
{
    $error = "Se ha producido un error obtener los candidatos. ". $e->getMessage();
    echo $error;
    die();
}

$template = $twig->loadTemplate('index.html');
echo $template->render($datos);

?>
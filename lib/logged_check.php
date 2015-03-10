<?php
include_once "lib/functions.php";

$simulation  = GetSimulation();
if($simulation)
{
    $canVote     = true;
    $voteExpired = false;
}
else
{
    $canVote     = GetCanVote();
    $voteExpired = GetVoteExpired();
}
$conn        = NULL;
$user        = NULL;
$val         = IsLogged();

if($val != 1)
{
    if($val == 2)
    {
        $_SESSION['error'] = "Ha expirado el tiempo de su sesión. Vuelva a iniciar sesión para continuar.";
    }
    
    header('Location:login.php');
    die();
}
else
{
    $user = GetAuthenticated();
    if($canVote == false || ($canVote && $voteExpired))
    {
        Logout();
        $datos = array('title' => "Votación de candidaturas",
                        'msg' => "El proceso electoral todavía no esta en marcha. Por favor, vuelva en las fechas indicadas.",
                        'user' => $user,
                        'simulation' => $simulation);
        $template = $twig->loadTemplate('message.html');
        echo $template->render($datos);
        die();
    }
    else
    {
        if($user['voted'] == 1)
        {
            if(IsAminAuthenticated())
            {
                $datos = array('voteExpired' => $voteExpired,
                                'canVote' => $canVote,
                                'user' => $user,
                                'simulation' => $simulation);
                $template = $twig->loadTemplate('panel.html');
                echo $template->render($datos);
                die();
            }
            else
            {
                Logout(false);

                $_SESSION['error'] = "Usted ya ha realizado su voto. Gracias por participar.";
                header('Location:login.php');
                die();
            }
        }
    }
}

?>
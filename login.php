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
$startDate     = GetVoteStartDate();
$endDate       = GetVoteEndDate();
$signonExpired = GetSignonExpired();
    
if(IsLogged() != 1)
{
    if(isset($_SESSION['error']))
    {
        $msg = $_SESSION["error"];
        unset($_SESSION['error']);
    }

    $datos = array('voteExpired' => $voteExpired,
                    'canVote' => $canVote,
                    'startDate' => $startDate,
                    'endDate' => $endDate,
                    'signonExpired' => $signonExpired,
                    'msg' => $msg,
                    'simulation' => $simulation,
                    'recaptcha_code' => GOOGLE_CAPTCHA_PUBLIC_CODE);

    $template = $twig->loadTemplate('login.html');
    echo $template->render($datos);
}
else
{
    //echo "Not logged!!!";
    header('Location:index.php');
    die();
}

?>
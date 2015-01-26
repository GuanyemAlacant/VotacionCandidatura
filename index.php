<?php
include_once "lib/functions.php";

$canVote     = true;
$voteExpired = false;

$userData = GetAuthenticated();
$user_id  = $userData['id'];
$datos = array('voteExpired' => $voteExpired,
              'canVote' => $canVote,
              'user' => $user_id);

if($canVote && $voteExpired == false && IsLogged() == false)
{   
    header('Location:login.php');
    die();
}
else
{
    $template = $twig->loadTemplate('index.html');
    echo $template->render($datos);
}

?>
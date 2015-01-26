<?php
include_once "lib/functions.php";

$canVote     = false;
$voteExpired = false;

$userData = GetAuthenticated();
$user_id  = $userData['id'];
$datos = array('voteExpired' => $voteExpired,
              'canVote' => $canVote,
              'user' => $user_id);

if($canVote && $voteExpired == false && empty($user_id))
{
    header('Location:login.php');
}
else
{
    $template = $twig->loadTemplate('index.html');
    echo $template->render($datos);
}

?>
<?php
include_once "lib/functions.php";

$canVote     = true;
$voteExpired = false;

if(isset($_SESSION['error']))
{
    $msg = $_SESSION["error"];
    unset($_SESSION['error']);
}

$datos = array('voteExpired' => $voteExpired,
              'canVote' => $canVote,
              'msg' => $msg);

$template = $twig->loadTemplate('login.html');
echo $template->render($datos);


var_dump($_SESSION);
?>
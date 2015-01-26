<?php
include_once "lib/functions.php";

$canVote     = false;
$voteExpired = false;

$datos = array('voteExpired' => $voteExpired,
              'canVote' => $canVote);
$template = $twig->loadTemplate('login.html');
echo $template->render($datos);

?>
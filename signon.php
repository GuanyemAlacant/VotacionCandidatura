<?php
include_once "lib/functions.php";

$signonExpired  = GetSignonExpired();
if($signonExpired)
{
    $_SESSION['error'] = "El proceso de inscripción ha finalizado.";
    header('Location:login.php');
    die();
}
else if(IsLogged() == 1)
{   
    header('Location:index.php');
    die();
}

$template = $twig->loadTemplate('signon.html');
echo $template->render(array());

?>
<?php
include_once "lib/functions.php";

$datos    = array('user'=>GetAuthenticated());

$template = $twig->loadTemplate('aviso_legal.html');
echo $template->render($datos);


?>
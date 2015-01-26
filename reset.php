<?php
include_once "lib/functions.php";

$datos = array();

$template = $twig->loadTemplate('reset.html');
echo $template->render($datos);

?>
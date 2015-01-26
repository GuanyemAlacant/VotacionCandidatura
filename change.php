<?php
include_once "lib/functions.php";

$datos = array();

$template = $twig->loadTemplate('change.html');
echo $template->render($datos);

?>
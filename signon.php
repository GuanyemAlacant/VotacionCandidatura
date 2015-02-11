<?php
include_once "lib/functions.php";

if(IsLogged() == true)
{   
    header('Location:index.php');
    die();
}

//if(isset($_POST) && count($_POST) > 0)
//{
//    echo "<pre>";
//    var_dump($_POST);
//    var_dump($_FILES);
//    echo "</pre>";
//    //die();
//}

$template = $twig->loadTemplate('signon.html');
echo $template->render(array());

?>
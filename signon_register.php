<?php
include_once "lib/functions.php";
include_once "lib/sendmail.php";

if(IsLogged() == true)
{
    die();
}

if(isset($_POST) && count($_POST) > 0)
{
    echo "<pre>";
    echo json_encode($_POST);
    echo "<pre>";
    echo "</pre>";
    echo json_encode($_FILES);
    echo "</pre>";
}


echo multi_attach_mail("casensio@gmail.com", "Inscripcion", json_encode($_POST), $_FILES["file"]["tmp_name"], "casensio@gmail.com");

// TODO: Delete temp files

?>
<?php
include_once "../functions.php";

$result = array('success' => false);

// TODO: Comprobar que somos admin!!!
if(IsLogged() == 1 && IsAminAuthenticated())
{
    //Nos intentamos conectar:
    try {
        $db = getBBDD();

        // Para hacer debug cargaríamos a mano el parámetro, descomentaríamos la siguiente línea:
        if (isset($_REQUEST['nif']))
        {
            $val    = false;
            $params = array('nif' => $_REQUEST['nif']);
            //echo "UPDATE cnd_users SET voted=1, presencial=1 WHERE voted=0 AND nif=".$_REQUEST['nif'];
            $sql    = $db->prepare("UPDATE cnd_users SET voted=1, presencial=1 WHERE voted=0 AND nif=:nif");
            if($sql->execute($params) === TRUE)
            {
                $val = ($sql->rowCount() == 1);
                $result = array('success' => true, 'voted' => $val);
                if($val == true)
                {
                    Log_Action(LOG_MSG, $_SESSION['user_id'], "VOTED!!");
                }
            }
            else
            {
                $result = array('success' => false, 'exists' => false);
            }
        }
    }
    catch (Exception $e)
    {
        $result = array('success' => false, 'error' => $e->getMessage());
    }
}
else
{
    $result = array('success' => false, 'unlogged' => true);
}

echo json_encode($result);

?>
<?php
include_once "../functions.php";

$val = IsLogged();

$result = array('success' => false);

if($val == 1 && IsAminAuthenticated())
{
    //Nos intentamos conectar:
    try {
        $db = getBBDD();

        // Para hacer debug cargaríamos a mano el parámetro, descomentaríamos la siguiente línea:
        if (isset($_REQUEST['nif']))
        {
            $params = array('nif' => $_REQUEST['nif']);
            $sql    = $db->prepare("SELECT voted FROM cnd_users WHERE nif=:nif");
            if($sql->execute($params) === TRUE)
            {
                $data = $sql->fetch(PDO::FETCH_ASSOC);
                if($data !== FALSE)
                {
                    $result = array('success' => true, 'voted' => ($data['voted'] > 0));
                }
                else
                {
                    $result = array('success' => false, 'exists' => false);
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
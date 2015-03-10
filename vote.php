<?php
include_once "lib/functions.php";

include_once "lib/logged_check.php";


//--
try
{
    $msg  = "Se ha producido un error al realizar la votación.";
    $conn = getBBDD();
    $conn->beginTransaction();

    // Comprobar la integridad de los datos!
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    {
        throw new Exception("Los datos de votación no se han enviado según el método adecuado.");
    }

    for($_i = 1; $_i < 8; $_i++)
    {
        $val1 = intval($_POST["vote_".$_i]);
        if($val1 != 0)
        {
            for($_j = $_i+1; $_j <= 8; $_j++)
            {
                $val2 = intval($_POST["vote_".$_j]);
                if($val2 != 0 && $val1 == $val2)
                {
                    throw new Exception("Los datos de votación no son válidos, el candidato ".$val1." tiene dos votaciones. (".$_i." y ".$_j.")");
                }
            }
        }
    }

    $hashData = GetVoteHashData();
    if($hashData == NULL)
    {
        throw new Exception("Se ha producido un error al generar los datos del voto.");
    }
    
    //--
    $data = array('first' => intval($_POST["vote_first"]),
                'v1' => intval($_POST["vote_1"]),
                'v2' => intval($_POST["vote_2"]),
                'v3' => intval($_POST["vote_3"]),
                'v4' => intval($_POST["vote_4"]),
                'v5' => intval($_POST["vote_5"]),
                'v6' => intval($_POST["vote_6"]),
                'v7' => intval($_POST["vote_7"]),
                'v8' => intval($_POST["vote_8"]),
                'data' => $hashData
    );
    $userData = array('id' => $user['id']);

    //--
    $result = $conn->prepare("INSERT INTO cnd_votes (first, v1, v2, v3, v4, v5, v6, v7, v8, data) VALUES (:first, :v1, :v2, :v3, :v4, :v5, :v6, :v7, :v8, :data);");
    if($result->execute($data) !== FALSE && $result->rowCount() == 1)
    {
        $result = $conn->prepare("UPDATE cnd_users SET voted=1 WHERE id=:id");
        if($result->execute($userData) !== FALSE && $result->rowCount() == 1)
        {
            $msg = "Su votación se ha registrado correctamente.";
            $conn->commit();
            $conn = NULL;
            
            Log_Action(LOG_MSG, 0, "VOTE: ".$hashData);
        }
        else
        {
            $msg = "Error realizando el voto.";
        }
    }
    else
    {
        $msg = "Error almacenando la papeleta virtual.";
    }
}
catch(Exception $e)
{
    $msg .= " Error: ". $e->getMessage();
}

if($conn != NULL)
{
    $conn->rollback();
}


$datos = array('title' => "Votación de candidaturas",
                'msg' => $msg,
                'user' => $user,
                'simulation' => $simulation);
$template = $twig->loadTemplate('message.html');
echo $template->render($datos);

?>
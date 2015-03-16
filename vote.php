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

    for($_i = 1; $_i < NUM_MAX_VOTES; $_i++)
    {
        $val1 = intval($_POST["vote_".$_i]);
        if($val1 != 0)
        {
            for($_j = $_i+1; $_j <= NUM_MAX_VOTES; $_j++)
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
    if(ADD_VOTE_TRACE == TRUE && $hashData == NULL)
    {
        throw new Exception("Se ha producido un error al generar los datos del voto.");
    }
    
    //--
    $sqlNames  = "first, data";
    $sqlValues = ":first, :data";
    
    $data = array();
    $data['first'] = intval($_POST["vote_first"]);
    $data['data']  = $hashData;
    for($_i = 1; $_i <= NUM_MAX_VOTES; $_i++)
    {
        $data['v'.$_i] = intval($_POST["vote_".$_i]);
        $sqlNames     .= ", v".$_i;
        $sqlValues    .= ", :v".$_i;
    }

    $userData = array('id' => $user['id']);

    //--
    $result = $conn->prepare("INSERT INTO cnd_votes (".$sqlNames.") VALUES (".$sqlValues.");");
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
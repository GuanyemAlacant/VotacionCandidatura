<?php
include_once "config.php";
include_once "PasswordHash.php";
include_once "sendmail.php";

//La coloco aquí para asegurar que se ejecuta siempre una única vez.
sec_session_start();
//session_start();



//-------------------------------------
function sec_session_start() 
{
    $session_name = 'id';   // Set a custom session name
    $secure = false;
    // This stops JavaScript being able to access the session id.
    $httponly = USE_HTTP_ONLY;
    // Forces sessions to only use cookies.
    if (ini_set('session.use_only_cookies', 1) === FALSE) 
    {
        header("Location: ../error.php?err=Could not initiate a safe session (ini_set)");
        exit();
    }
    // Gets current cookies params.
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params($cookieParams["lifetime"],
        $cookieParams["path"], 
        $cookieParams["domain"], 
        $secure,
        $httponly);
    // Sets the session name to the one set above.
    session_name($session_name);
    session_start();            // Start the PHP session 
    session_regenerate_id(true);    // regenerated the session, delete the old one. 
}

//-------------------------------------
function Log_Msg($action)
{
    Log_Action(LOG_MSG, $_SESSION['user_id'], $action);
}
//-------------------------------------
function Log_Warn($action)
{
    Log_Action(LOG_WARN, $_SESSION['user_id'], $action);
}
//-------------------------------------
function Log_Error($action)
{
    Log_Action(LOG_ERROR, $_SESSION['user_id'], $action);
}

//-------------------------------------
function Log_Action($level, $user, $action)
{
    try
    {
        $ip = "--";
        if(isset($_SERVER['REMOTE_ADDR']))
        {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        
        $conn     = getBBDD();
        $data     = array("level" => $level,
                         "user" => $user,
                         "action" => $action,
                         "ip" => $ip);
        $consulta = "INSERT INTO cnd_log (level, user_id, activity, ip) VALUES (:level, :user, :action, :ip);";
        $result   = $conn->prepare($consulta);
        $result->execute($data);
    }
    catch(Exception $e)
    {
        //echo "error: ".$e->getMessage();
        //die();
    }
}

//-------------------------------------
function Login($nif, $pass) 
{
	// Creamos el objeto que nos permitirá gestionar nuestro hash
	$hasher = new PasswordHash(8, FALSE);
    try
	{
        $user_id = 0;
        $msg     = "Datos de acceso inválidos.";
		$conn    = getBBDD();
		$user    = array(
			'nif' => $nif
		);
		$result = $conn->prepare("SELECT id, password FROM cnd_users WHERE nif=:nif;");
		$result->execute($user);
		if($result->rowCount() == 1)
        {
            //echo "User selected<br />";
            
            $cont = $result->fetch(PDO::FETCH_ASSOC);
            if($cont !== FALSE)
            {
                $password = $cont['password'];
                $user_id  = $cont['id'];
            }
            if(CheckBruteAttack($user_id, (15 * 60), 3) == false)
            {
                //echo "Not a brute force!<br />";
                
                if($hasher->CheckPassword($pass, $password))
                {
                    //echo "Pass is correct<br />";
                    $now = time();
                    $ip  = $_SERVER['REMOTE_ADDR'];
                    
                    $user_browser          = $_SERVER['HTTP_USER_AGENT'];
                    $_SESSION['agent']     = $user_browser;
                    $_SESSION['ip']        = $ip;
                    $_SESSION['timestamp'] = $now;
                    $_SESSION['user_id']   = $user_id;
                    $_SESSION['login_string'] = hash('sha512', $ip . $password . $user_browser);

                    //echo "Log_Action<br />";
                    Log_Action(LOG_MSG, $user_id, "Log in!");
                    
                    if(LoggedInDB_StartSession($user_id) == true)
                    {
                        //Log_Action(LOG_MSG, $user_id, "Session started!");
                        header('Location: index.php');
                        die();
                    }
                    else
                    {
                        Logout(false);
                        $msg = "Se ha producido un error al crear su sesión. Es posible que hubiese dejado una sesión abierta, si el problema persiste pongase en conctacto con nosotros. candidaturaguanyem@gmail.com";
                    }
                }
                else
                {
                    InsertLoginAttemp($user_id);
                }
            }
            else
            {
                $msg = "Se ha intentado conectar demasiadas veces segidas, espere unos minutos antes de continuar.";
            }
        }
	}
	catch(Exception $e)
	{
		//echo $e->getMessage();
	}

    //echo "ERROR!!!!!";
    
    //Si no coinciden envía a login con sesión de error.
    $_SESSION["error"] = $msg;
    Log_Action(LOG_ERROR, $user_id, $nif." - ".$msg);
    header('Location: login.php');
    die();
}

//-------------------------------------
function IsLogged()
{
    //0 not logged
    //1 logged
    //2 unlogged
    
    $val     = 0;   // not logged
    $ret     = false;
    $user_id = 0;
    
    if(isset($_SESSION['agent'],
            $_SESSION['ip'],
            $_SESSION['timestamp'],
            $_SESSION['user_id'],
            $_SESSION['login_string']) == true)
    {
        $user_data = GetAuthenticated();
        if($user_data !== NULL)
        {
            // Get the user-agent string of the user.
            $user_browser = $_SERVER['HTTP_USER_AGENT'];
            $ip           = $_SERVER['REMOTE_ADDR'];
            $password     = $user_data['password'];
            $hash         = hash('sha512', $ip . $password . $user_browser);
            if(strcmp($_SESSION['login_string'], $hash) == 0)
            {
                $user_id = $_SESSION['user_id'];
                $ret     = true;
                $val     = 1;   // logged
            }
        }
    }
    
    
    if($ret == true)
    {
        if(LoggedInDB_Check($user_id) == false)
        {
            $val = 2;   // unlogged
            $ret = false;
            Logout(false);
        }
        else
        {
            LoggedInDB_KeepAlive($user_id);
        }
    }
    
    return $val;
}

//-------------------------------------
function Logout($redirect = true)
{
    Log_Msg("Log out");
        
    LoggedInDB_EndSession($_SESSION['user_id']);

    // Unset all session values 
    $_SESSION = array();

    if($redirect)
    {
        // get session parameters 
        $params = session_get_cookie_params();

        // Delete the actual cookie. 
        setcookie(session_name(),
                '', time() - 42000, 
                $params["path"], 
                $params["domain"], 
                $params["secure"], 
                $params["httponly"]);

        // Destroy session
        session_destroy();
        
        header('Location: index.php');
        die();
    }
}

//-------------------------------------
function LoggedInDB_Check($user_id)
{
    $elapsed    = (10 * 60);
    $now        = time();
    $valid_time = date('Y-m-d H:i:s', $now - $elapsed);
    $ip         = $_SERVER['REMOTE_ADDR'];
    try
	{
		$conn   = getBBDD();
		$params = array(
			'user_id' => intval($user_id),
            'valid_time' => $valid_time
		);
		$result = $conn->prepare("SELECT * FROM cnd_logged_users WHERE user_id=:user_id AND timestamp > :valid_time;");
		$result->execute($params);
        $data = $result->fetch(PDO::FETCH_ASSOC);
        if($data !== FALSE)
        {
            if($data['ip'] == $ip)
            {
                return true;
            }
            else
            {
                Log_Action(LOG_ERROR, $user_id, $ip." - LoggedInDB_Check IP change: (".$data['ip'].")");
            }
        }
        else
        {
            Log_Action(LOG_WARN, $user_id, $ip." - LoggedInDB_Check session expired.");
        }
    }
    catch(Exception $e)
    {
        Log_Action(LOG_ERROR, $user_id, $ip." - LoggedInDB_Check exception: ".$e->getMessage().".");
    }
    return false;
}

//-------------------------------------
function LoggedInDB_KeepAlive($user_id)
{
    $now        = time();
    try
	{
		$conn   = getBBDD();
		$params = array(
			'user_id' => intval($user_id)
		);
		$result = $conn->prepare("UPDATE cnd_logged_users SET timestamp=now() WHERE user_id=:user_id;");
		$result->execute($params);
    }
    catch(Exception $e)
    {
        Log_Action(LOG_ERROR, $user_id, " - LoggedInDB_KeepAlive exception: ".$e->getMessage().".");
    }
}

//-------------------------------------
function LoggedInDB_StartSession($user_id)
{
    $now = time();
    $ip  = $_SERVER['REMOTE_ADDR'];
    
    if(LoggedInDB_EndSession($user_id) == true)
    {
        Log_Action(LOG_ERROR, $user_id, "Session already open!");
        return false;
    }
    
    try
	{
		$conn   = getBBDD();
		$params = array(
			'user_id' => intval($user_id),
            'ip' => $ip
		);
		$result = $conn->prepare("INSERT INTO cnd_logged_users (user_id, timestamp, ip) VALUES (:user_id, now(), :ip)");
		$result->execute($params);
        if($result->rowCount() != 1)
        {
            return false;
        }
    }
    catch(Exception $e)
    {
        Log_Action(LOG_ERROR, $user_id, " - LoggedInDB_StartSession exception: ".$e->getMessage().".");
        return false;
    }
    
    return true;
}

//-------------------------------------
function LoggedInDB_EndSession($user_id)
{
    try
	{
		$conn   = getBBDD();
		$params = array(
			'user_id' => intval($user_id)
		);
		$result = $conn->prepare("DELETE FROM cnd_logged_users WHERE user_id=:user_id;");
		$result->execute($params);
        if($result->rowCount() != 1)
        {
            return false;
        }
    }
    catch(Exception $e)
    {
        Log_Action(LOG_ERROR, $user_id, " - LoggedInDB_EndSession exception: ".$e->getMessage().".");
        return false;
    }
    
    return true;
}


//-------------------------------------
function InsertLoginAttemp($user_id)
{
    $ip  = $_SERVER['REMOTE_ADDR'];
    try
	{
		$conn   = getBBDD();
		$user   = array(
			'user_id' => intval($user_id),
            'ip' => $ip
		);
		$result = $conn->prepare("INSERT INTO cnd_login_attempts (user_id, ip) VALUES (:user_id, :ip);");
		$result->execute($user);
        
        
        //Log_Action(LOG_WARN, $user_id, "Log attempt");
    }
    catch(Exception $e)
    {
    }
}

//-------------------------------------
function CheckBruteAttack($user_id, $elapsed, $attemps)
{
    $now        = time();
    $valid_time = date('Y-m-d H:i:s', $now - $elapsed);
 
    try
	{
		$conn   = getBBDD();
		$user   = array(
			'user_id' => intval($user_id),
            'valid_time' => $valid_time
		);
		$result = $conn->prepare("SELECT * FROM cnd_login_attempts WHERE user_id = :user_id AND time > :valid_time;");
		$result->execute($user);
        
		return ($result->rowCount() > $attemps);
    }
    catch(Exception $e)
    {
        echo "error: ".$e->getMessage()."<br />";
        die();
    }

    return false;
}

//-------------------------------------
//Busca todos los datos del usuario identificado
function IsAminAuthenticated()
{
	if(isset($_SESSION["user_id"]))
	{
        return ($_SESSION["user_id"] === ADMIN_CODE_ID);
	}
    return false;
}

//-------------------------------------
//Busca todos los datos del usuario identificado
function GetAuthenticated()
{
	if(isset($_SESSION["user_id"]))
	{
        return GetUserDataById($_SESSION["user_id"]);
	}
    return NULL;
}

//-------------------------------------
//Busca todos los datos del usuario identificado
function GetUserDataById($id)
{
    $ret = NULL;
    

    try
    {
        $conn     = getBBDD();
        $user     = array($id);
        $consulta = "SELECT * FROM cnd_users WHERE id=?;";
        $result   = $conn->prepare($consulta);
        $result->execute($user);
        foreach($result as $res)
        {
            $ret = $res;
        }
        $conn = null;
    }
    catch(PDOException $e)
    {
        echo $e->getMessage();
    }
    catch(Exception $e)
    {
        echo $e->getMessage();
    }
    
    return $ret;
}


//-------------------------------------
// Obtiene el valor de la tabla config indicado
function GetConfigValue($id)
{
    $ret = NULL;

    try
    {
        $conn     = getBBDD();
        $user     = array($id);
        $consulta = "SELECT value FROM cnd_config WHERE name=?;";
        $result   = $conn->prepare($consulta);
        $result->execute($user);
        foreach($result as $res)
        {
            $ret = $res[0];
        }
        $conn = null;
    }
    catch(Exception $e)
    {
        echo $e->getMessage();
        die();
    }
    
    return $ret;
}

//-------------------------------------
function GetSimulation()
{
    $sim = GetConfigValue("simulation");
    return ($sim != NULL);
}

//-------------------------------------
function GetVoteStartDate()
{   
    $date = GetConfigValue("vote_start_date");
    return $date;
}

//-------------------------------------
function GetVoteEndDate()
{
    $date = GetConfigValue("vote_end_date");
    return $date;
}

//-------------------------------------
function GetSignonEndDate()
{
    $date = GetConfigValue("signon_end_date");
    return $date;
}

//-------------------------------------
function GetCanVote()
{
    $date = GetVoteStartDate();
    if($date != NULL)
        return (time() > strtotime($date));
    return false;
}

//-------------------------------------
function GetVoteExpired()
{
    $date = GetVoteEndDate();
    if($date != NULL)
        return (time() > strtotime($date));
    return false;
}

//-------------------------------------
function GetSignonExpired()
{
    $date = GetSignonEndDate();
    if($date != NULL)
        return (time() > strtotime($date));
    return false;
}

//-------------------------------------
function SendEmail_AlertPassChanged($mail, $nif)
{
    $subject = "Cambio de contraseña"; 
    $body = '<html> 
    <body> 
    
    <h1>Cambio de contraseña</h1> 
    <p>Nuestro sistema ha recibido un cambio de contraseña vinculada con este correo.</p>
    <p>Si usted no ha realizado este cambio, pongase en contacto con nosotros para solucionar su incidencia.</p>
    <p>Si ha realizado el cambio usted mismo puede borrar este mensaje.</p>
    <p></p>
    <p>email: '.FromMail.'</p>
    <p>web: '.BaseURL.'</p>
    </body> 
    </html>';

    Log_Action(LOG_MSG, 0, $mail." - AlertPassChanged");
    return SendMailHTML(FromMail, $mail, $subject, $body);
}

//-------------------------------------
function SendEmail_AlertPassSet($mail, $pass)
{
    $url     = BaseURL.'/change.php';
    $subject = "Contraseña restablecida";
    $body    = '<html> 
    <body> 
    
    <h1>Se ha restablecido la contraseña en el portal de votación de candidaturas</h1> 
    <p>El sistema ha genrerado la siguiente clave de acceso vinculada a su nif:</p>
    <p>'.$pass.'</p>
    <p>Le recomendamos que cambie esta contraseña por una de su elección lo antes posible.<br />
    <a href="'.$url.'">Puede hacerlo aquí.</a></p>
    <p></p>
    </body> 
    </html>'; 

    Log_Action(LOG_MSG, 0, $mail." - AlertPassSet");
    return SendMailHTML(FromMail, $mail, $subject, $body);
}

//-------------------------------------
function SendEmail_AlertPassGenerated($mail, $pass)
{
    $url     = BaseURL.'/change.php';
    $subject = "Contraseña generada";
    $body    = '<html> 
    <body> 
    
    <h1>Se han registrado sus datos en el portal de votación de candidaturas</h1> 
    <p>El sistema ha genrerado la siguiente clave de acceso vinculada a su nif:</p>
    <p>'.$pass.'</p>
    <p>Le recomendamos que cambie esta contraseña por una de su elección lo antes posible.<br />
    <a href="'.$url.'">Puede hacerlo aquí.</a></p>
    <p></p>
    </body> 
    </html>'; 

    Log_Action(LOG_MSG, 0, $mail." - AlertPassGenerated");
    return SendMailHTML(FromMail, $mail, $subject, $body);
}

//-------------------------------------
function SendEmail_ComfirmPassSet($mail, $nif, $hash)
{
    $url     = BaseURL.'/reset_pass.php?hash='.$hash;
    $subject = "Restablecer contraseña"; 
    $body    = '<html> 
    <body> 
    
    <h1>Restablecer contraseña en el portal de votación de candidaturas.</h1> 
    <p>Nuestro sistema ha recibido una petición para restablecer la contraseña vinculada al NIF: '.$nif.' </p>
    <p>Si desea que el sistema genere una nueva contraseña siga el siguiente enlace o copielo en un navegador:</p>
    <p><a href="'.$url.'" target="_blank">'.$url.'</a></p>
    <p>Este enlace tiene una validez de 24 horas.</p>
    <p></p>
    <p>Si usted no ha iniciado esta solicitud, puede ignorar este mensaje de forma segura.</p>
    </body> 
    </html>'; 

    Log_Action(LOG_MSG, 0, $mail." - ComfirmPassSet");
    return SendMailHTML(FromMail, $mail, $subject, $body);
}

//-------------------------------------
function CreateConfirmationHash($user_id, $time, $nif)
{
    $inputA = "". ($time * $user_id);
    $inputB = $nif;
    $output = "";
    
    
    // Merge inputs
    $i = 0;
    $j = 0;
    while($i < strlen($inputA) || $j < strlen($inputB))
    {
        if($i < strlen($inputA))
            $output .= $inputA[$i];
        if($j < strlen($inputB))
            $output .= $inputB[$j];
        $i++;
        $j++;
    }

    // Encrypt it with the MD4 hash
    $output = md5($output);

    // Make it uppercase, not necessary, but it's common to do so with NTLM hashes
    $output = strtoupper($output);
    
    // Return the result
    return($output);
}


//-------------------------------------
function GeneratePassword($length=8, $strength=15) {
    $vowels = 'aeuy';
    $consonants = 'bdghjmnpqrstvz';
    if ($strength & 1) {
        $consonants .= 'BDGHJLMNPQRSTVWXZ';
    }
    if ($strength & 2) {
        $vowels .= "AEUY";
    }
    if ($strength & 4) {
        $consonants .= '23456789';
    }
    if ($strength & 8) {
        $consonants .= '@#$%';
    }

    $password = '';
    $alt = time() % 2;
    for ($i = 0; $i < $length; $i++) {
        if ($alt == 1) {
            $password .= $consonants[(rand() % strlen($consonants))];
            $alt = 0;
        } else {
            $password .= $vowels[(rand() % strlen($vowels))];
            $alt = 1;
        }
    }
    return $password;
}

//-------------------------------------
function GetVoteHashData()
{
    $hash = NULL;
    $user = GetAuthenticated();
    
    if($user != NULL && ADD_VOTE_TRACE == TRUE)
    {
        $merge = "";
        $i = 0;
        while(true)
        {
            $size = strlen($merge);
            if($i < strlen($user['nombre']) - 1)
                $merge .= $user['nombre'][$i];
            if($i < strlen($user['apellidos']) - 1)
                $merge .= $user['apellidos'][$i];
            if($i < strlen($user['direccion']) - 1)
                $merge .= $user['direccion'][$i];
            if($i < strlen($user['nif']) - 1)
                $merge .= $user['nif'][$i];
            if($i < strlen($user['email']) - 1)
                $merge .= $user['email'][$i];
            if($size == strlen($merge))
                break;
            $i++;
        }
        
        if(strlen($merge) > 0)
            $hash = md5($merge);
    }
    return $hash;
}
?>
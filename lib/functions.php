<?php
include_once "config.php";
include_once "PasswordHash.php";
include_once "sendmail.php";

//La coloco aquí para asegurar que se ejecuta siempre una única vez.
session_start();

//-------------------------------------
function ParseCSVFiles()
{
	$numCorrect = 0;
	$strRet     = "";
	if(strtolower($_SERVER["REQUEST_METHOD"])=="post")
	{
		if (!empty($_POST['submit'])) 
		{
			$strFilesRet = "";
			if(is_array($_FILES))
			{
				$file_count = count($_FILES['files']['name']);
				
				for($i = 0; $i < $file_count; $i++)
				{
					$fileName     = $_FILES['files']['name'][$i];
					$fileType     = $_FILES['files']['type'][$i];
					$fileTempName = $_FILES['files']['tmp_name'][$i];
					
					if(strlen($fileName)>0)
					{
						$splitFileType = split("/", $fileType);
						if ($splitFileType[0] == "text")
						{
							$fileContent = file($fileName);
							if($fileContent !== FALSE)
							{
								$numRows     = count($fileContent);
								
								$rowHeader = $fileContent[0];
								$dataRows  = array_slice($fileContent, 1);
								if(CheckHeaders($rowHeader))
								{
									$strRet .= "<ul>";
									foreach($dataRows as $key => $row)
									{
                                        
//                                        $insert = $gbd->prepare("INSERT INTO fruit(name, colour) VALUES (?, ?)");
//                                        $insert->execute(array('apple', 'green'));
//                                        $insert->execute(array('pear', 'yellow'));
										if(InsertRow($row))
										{
                                            SendEmail_();
											$numCorrect++;
										}
										else
										{
											$strRet .= "<li>La fila ".$key." ha producido un error.</li>";
										}
									}
									$strRet .= "</ul>";
								}
								else
								{
									$strRet .= "<p>Los datos contenidos en el fichero no son válidos.</p>";
								}
							}
							else
							{
								$strRet .= "<p>El fichero ".$fileName." no continene datos.</p>";
							}
						}
						else
						{
							$strRet .= "<p>El fichero ".$fileName." no es del tipo indicado (".$fileType.")</p>";
						}
					}
					else
					{
						$strRet .= "<p>El fichero no tiene datos.</p>";
					}
				}
			}
			else
			{
				$strRet = "<p>No es un array (".$_FILES.").</p>";
			}
			
		}
		else
		{
			$strRet = "<p>No hay ficheros para procesar.</p>";
		}
	}
	return $strRet;
}


//-------------------------------------
function CheckHeaders($rowHeader)
{
    
	$requiredColumns = array("nombre", "apellidos", "nif", "email", "cp", "direccion", "provincia", "municipio", "nacimiento", "telefono");
	$numFound = 0;
	$columns  = split(",", $rowHeader);

    foreach($columns as $key => $val)
	{
		foreach($requiredColumns as $name)
		{
			if(strcasecmp($val, $name) == 0)
			{
				$numFound++;
				break;
			}
		}
	}
	
	return (count($requiredColumns) == $numFound);
}


//-------------------------------------
function InsertRow($row)
{
	$columns  = split(",", $row);
	$conn = getBBDD();
	
	$conn->prepare("INSERT INTO cnd_users (nombre, apellidos, nif, email, cp, direccion, provincia, municipio, nacimiento, telefono) VALUES (:nombre, :apellidos, :nif, :email, :cp, :direccion, :provincia, :municipio, :nacimiento, :telefono)");
	$data = array('nombre' => $columns['nombre'],
					'apellidos' => $columns['apellidos'],
					'nif' => $columns['nif'],
					'email' => $columns['email'],
					'cp' => $columns['cp'],
					'direccion' => $columns['direccion'],
					'provincia' => $columns['provincia'],
					'municipio' => $columns['municipio'],
					'nacimiento' => $columns['nacimiento'],
					'telefono' => $columns['telefono']);
					
	return $conn->execute($data);
}

//-------------------------------------
function Login($nif, $pass)
{
	// Creamos el objeto que nos permitirá gestionar nuestro hash
	$hasher = new PasswordHash(8, FALSE);
	try
	{
		$conn   = getBBDD();
		$user   = array(
			'nif' => $nif
		);
		$result = $conn->prepare("SELECT id, password FROM cnd_users WHERE nif=:nif;");
		$result->execute($user);
		if($result->rowCount() == 1)
        {
            $cont = $result->fetch(PDO::FETCH_ASSOC);
            if($cont !== FALSE)
            {
                $password = $cont['password'];
                $user_id  = $cont['id'];
            }
		
            //comprueba que usuario y contraseña coinciden y crea sesión de usuario.
            if($hasher->CheckPassword($pass, $password))
            {
                if(!isset($_SESSION["login_user"]))
                {
                    $_SESSION["login_user"] = $user_id;
                }
                if(isset($_SESSION['error']))
                {
                    unset($_SESSION['error']);
                }

                //Envía al index
                header('Location: index.php');
                die();
            }
        }
	}
	catch(PDOException $e)
	{
		echo $e->getMessage();
	}

	$conn = null;
    
    //Si no coinciden envía a login con sesión de error.
    $_SESSION["error"] = "Datos de acceso inválidos.";
    header('Location: login.php');
    die();
}

//-------------------------------------
//logout
function Logout()
{
	unset($_SESSION['login_user']);
	header('Location: index.php');
    die();
}

//-------------------------------------
function IsLogged()
{
	return (isset($_SESSION['login_user']) && empty($_SESSION['login_user']) == false);
}

//-------------------------------------
//Busca todos los datos del usuario identificado
function GetAuthenticated()
{
	if(isset($_SESSION["login_user"]))
	{
        return GetUserDataById($_SESSION["login_user"]);
	}
    return array();
}

//-------------------------------------
//Busca todos los datos del usuario identificado
function GetUserDataById($id)
{
    $ret = array();
    

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
    <p>email: candidaturaguanyem@gmail.com</p>
    <p>web: http://www.guanyemalacant.org</p>
    </body> 
    </html>';

    SendMailHTML($mail, $subject, $body);
}

//-------------------------------------
function SendEmail_AlertPassSet($mail, $pass)
{
    $url     = $_SERVER['HTTP_HOST'].'/change.php';
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

    SendMailHTML($mail, $subject, $body);
}

//-------------------------------------
function SendEmail_ComfirmPassSet($mail, $nif, $hash)
{
    $url     = $_SERVER['HTTP_HOST'].'/reset_pass.php?hash='.$hash;
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

    SendMailHTML($mail, $subject, $body);
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
?>
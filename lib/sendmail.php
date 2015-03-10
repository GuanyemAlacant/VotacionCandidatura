<?php

//-------------------------------------
function SendMailHTML($from, $mail, $subject, $body) 
{
    //para el envío en formato HTML 
    $headers = "MIME-Version: 1.0\r\n"; 
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n"; 
    //dirección del remitente 
    $headers .= "From: ".$from."\r\n"; 
//    //dirección de respuesta, si queremos que sea distinta que la del remitente 
//    $headers .= "Reply-To: ".$from."\r\n"; 
//    //ruta del mensaje desde origen a destino 
//    $headers .= "Return-path: ".$from."\r\n"; 
    
    $ok = mail($mail, $subject, $body, $headers);
    if($ok == false)
    {
        file_put_contents("/tmp/mail_php.log", "\r\n".date('Y-m-d H:i:s')." - Error to: ".$mail.", ".print_r(error_get_last(), true)." -> ".$subject, FILE_APPEND);
    }
    
    return $ok;
}

//-------------------------------------
function SendMailMultiAttach($to, $subject, $body, $files, $fileNames, $sendermail)
{
    // email fields: to, from, subject, and so on
    $from = "Files attach <".$sendermail.">"; 
    if(!isset($subject))
        $subject = date("d.M H:i")." F=".count($files); 
    
    if(!isset($body))
        $body = date("Y.m.d H:i:s")."\n".count($files)." attachments";
    
    $headers = "";
    $headers = "From: $from";

    // boundary 
    $semi_rand = md5(time()); 
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 

    // headers for attachment 
    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\""; 

    // multipart boundary 
    $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"iso-8859-1\"\n" .
    "Content-Transfer-Encoding: 7bit\n\n" . $body . "\n\n"; 

    // preparing attachments
    for($i=0;$i<count($files);$i++)
    {
        if(is_file($files[$i]))
        {
            $fp       =    @fopen($files[$i],"rb");
            $data     =    @fread($fp,filesize($files[$i]));
            @fclose($fp);
            $data     = chunk_split(base64_encode($data));
            
            //--
            $fileName = $files[$i];
            if(isset($fileNames) && count($fileNames) > $i)
            {
                $fileName = $fileNames[$i];
            }
            
            //--
            $message .= "--{$mime_boundary}\n";
            $message .= "Content-Type: application/octet-stream; name=\"".basename($fileName)."\"\n" . 
            "Content-Description: ".basename($fileName)."\n" .
            "Content-Disposition: attachment;\n" . " filename=\"".basename($fileName)."\"; size=".filesize($files[$i]).";\n" . 
            "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n";
        }
    }
    $message .= "--{$mime_boundary}--";
    //$returnpath = "-f" . $sendermail;

    //echo $subject."\n\n".$headers.$message;

    $ok = mail($to, $subject, $message, $headers); //, $returnpath); 
    if($ok)
    {
        return $i;
    } 
    else
    {
        file_put_contents("/tmp/mail_php.log", "\r\n".date('Y-m-d H:i:s')." - Error sending: ".$to.", ".print_r(error_get_last(), true), FILE_APPEND);

        return 0; 
    }
}
?>
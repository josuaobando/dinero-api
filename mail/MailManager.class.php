<?php

/**
 * @author Josua
 */
class MailManager
{

  private static $return = CoreConfig::MAIL_RETURN;
  private static $from = CoreConfig::MAIL_FROM;
  private static $host = CoreConfig::MAIL_HOST;
  private static $port = CoreConfig::MAIL_PORT;
  private static $username = CoreConfig::MAIL_USERNAME;
  private static $password = CoreConfig::MAIL_PASSWORD;
  private static $auth = CoreConfig::MAIL_AUTH;

  private static $lastError = null;
  private static $htmlFormat = true;

  /**
   * Get last error
   *
   * @return string
   */
  public static function getLastError()
  {
    return MailManager::$lastError;
  }

  /**
   * set if the email is a html format
   *
   * @param bool $value
   */
  public static function setHtmlFormat($value)
  {
    MailManager::$htmlFormat = $value;
  }

  /**
   * Method that allows separate by comma a string and convert to array
   *
   * @param string $string
   *
   * @return array
   */
  private static function getArrayToSendEmail($string)
  {
    $array = array();
    $emails = '';

    if(substr($string, -1) == ','){
      $emails = substr($string, 0, -1);
    }else{
      $emails = $string;
    }
    $array = explode(',', $emails);

    return $array;
  }

  /**
   * Advanced method to send emails with attachments
   *
   * @param string $recipients
   * @param string $subject
   * @param string $body
   * @param array|bool $attachments
   * @param mixed $from
   *
   * @return bool
   */
  public static function sendAdvancedEmail($recipients, $subject, $body, $attachments = false, $from = null)
  {
    try{
      $cc = "";
      $bcc = "";
      if(is_array($recipients)){
        $to = $recipients['To'];
        $cc = $recipients['Cc'];
        $bcc = $recipients['Bcc'];
      }else{
        $to = $recipients;
      }

      if(Util::isDEV() || !$to){
        $to = CoreConfig::MAIL_DEV;
      }

      $mailer = new PHPMailerManager();
      if(MailManager::$htmlFormat){
        $mailer->isHTML(true);
        $mailer->Body = $body;
      }else{
        $mailer->Body = $body;
      }

      if($attachments && is_array($attachments)){
        //Attach multiple files one by one
        foreach($attachments as $att){
          $mailer->addAttachment($att);
        }
      }

      $mailer->setFrom($from ? $from : MailManager::$from);
      $mailer->Subject = $subject;

      $arrayTo = self::getArrayToSendEmail($to);
      foreach($arrayTo as $t){
        $mailer->addAddress($t);
      }

      if($cc){
        $arrayCC = self::getArrayToSendEmail($cc);
        foreach($arrayCC as $c){
          $mailer->addCC($c);
        }
      }

      if($bcc){
        $arrayBCC = self::getArrayToSendEmail($bcc);
        foreach($arrayBCC as $b){
          $mailer->addCC($b);
        }
      }

      $mailer->isSMTP();
      $mailer->Host = MailManager::$host;
      $mailer->Port = MailManager::$port;
      $mailer->SMTPAuth = MailManager::$auth;
      $mailer->Username = MailManager::$username;
      $mailer->Password = MailManager::$password;
      $mailer->SMTPSecure = "tls";

      if(!$mailer->send()){
        MailManager::$lastError = 'Email was not sent: ' . $mailer->ErrorInfo . ' Subject: ' . $subject;
        Log::custom('Email', MailManager::$lastError);
        return false;
      }

      return true;
    }catch(Exception $exception){
      Log::custom(__CLASS__, $exception->getMessage());
    }
    if(!CoreConfig::MAIL_SEND_ACTIVE){
      return true;
    }
  }

  /**
   * Standard method to send emails using php config file
   *
   * @param array $to
   * @param string $subject
   * @param string $body
   * @param array $attachments
   *
   * @return bool
   */
  private static function sendStandardEmailNoAttachments($to, $subject, $body)
  {
    $headers = 'MIME-Version: 1.0' . "\r\n";
    if(MailManager::$htmlFormat){
      $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
    }else{
      $headers .= 'Content-type: text/plain; charset=iso-8859-2' . "\r\n";
    }
    $headers .= 'From: ' . MailManager::$from . "\r\n";
    if(Util::isDEV()){
      $to = CoreConfig::MAIL_DEV;
    }
    if(@mail($to, $subject, $body, $headers, "-f" . MailManager::$return)){
      return true;
    }

    MailManager::$lastError = 'Email was not sent';

    return false;
  }

  /**
   * send standard email using default php function
   *
   * @param string $to
   * @param string $subject
   * @param string $message
   * @param array $files
   *
   * @return boolean
   */
  private static function sendStandardEmail($to, $subject, $message, $files = false)
  {
    if(!CoreConfig::MAIL_SEND_ACTIVE){
      return true;
    }

    ini_set('sendmail_from', CoreConfig::MAIL_FROM);
    ini_set('SMTP', CoreConfig::MAIL_HOST);
    ini_set('smtp_port', CoreConfig::MAIL_PORT);

    if(!$files){
      return MailManager::sendStandardEmailNoAttachments($to, $subject, $message);
    }

    if(Util::isDEV()){
      $to = CoreConfig::MAIL_DEV;
    }

    $textMessage = strip_tags(nl2br($message), "<br>");
    $htmlMessage = nl2br($message);
    $fromEmail = strip_tags(MailManager::$from);

    $boundary1 = rand(0, 9) . "-" . rand(10000000000, 9999999999) . "-" . rand(10000000000, 9999999999) . "=:" . rand(10000, 99999);
    $boundary2 = rand(0, 9) . "-" . rand(10000000000, 9999999999) . "-" . rand(10000000000, 9999999999) . "=:" . rand(10000, 99999);

    //Email without Attachment
    //set headers
    $headers = <<<AKAM
From: <$fromEmail>
Reply-To: $fromEmail
MIME-Version: 1.0
Content-Type: multipart/alternative;
    boundary="$boundary1"
AKAM;

    //set body
    $body = <<<AKAM
MIME-Version: 1.0
Content-Type: multipart/alternative;
    boundary="$boundary1"

This is a multi-part message in MIME format.

--$boundary1
Content-Type: text/plain;
    charset="windows-1256"
Content-Transfer-Encoding: quoted-printable

$textMessage
--$boundary1
Content-Type: text/html;
    charset="windows-1256"
Content-Transfer-Encoding: quoted-printable

$htmlMessage

--$boundary1--
AKAM;

    //HTML Email WIth Multiple Attachment

    $attachments = '';
    $headers = <<<AKAM
From:<$fromEmail>
Reply-To: $fromEmail
MIME-Version: 1.0
Content-Type: multipart/mixed;
    boundary="$boundary1"
AKAM;

    for($count = 0; $count < count($files); $count++){
      $file = $files[$count];
      $file_size = filesize($file);
      $handle = fopen($file, "r");
      $content = fread($handle, $file_size);
      fclose($handle);
      $fileAttachment = chunk_split(base64_encode($content));
      if(!function_exists('mime_content_type')){
        $fileType = MailManager::getContentTypeFromFile($file);
      }else{
        $fileType = mime_content_type($file);
      }
      $fileName = basename($file);

      $attachments .= <<<ATTA
--$boundary1
Content-Type: $fileType;
    name="$fileName"
Content-Transfer-Encoding: base64
Content-Disposition: attachment;
    filename="$fileName"

$fileAttachment

ATTA;
    }

    $body = <<<AKAM
This is a multi-part message in MIME format.

--$boundary1
Content-Type: multipart/alternative;
    boundary="$boundary2"

--$boundary2
Content-Type: text/plain;
    charset="windows-1256"
Content-Transfer-Encoding: quoted-printable

$textMessage
--$boundary2
Content-Type: text/html;
    charset="windows-1256"
Content-Transfer-Encoding: quoted-printable

$htmlMessage

--$boundary2--

$attachments
--$boundary1--
AKAM;

    //Sending Email
    $sentEmail = mail($to, $subject, $body, $headers);

    //if the message is sent successfully print "Mail sent". Otherwise print "Mail failed"
    return $sentEmail ? true : false;
  }

  /**
   * Main method to send emails
   *
   * @param array $recipients
   * @param string $subject
   * @param string $body
   * @param array|bool $attachments
   *
   * @return bool
   */
  public static function sendEmail($recipients, $subject, $body, $attachments = false)
  {
    try{
      if(!CoreConfig::MAIL_SEND_ACTIVE){
        return true;
      }
      if(is_array($recipients)){
        $to = "";
        foreach($recipients as $email){
          $to .= "$email,";
        }
      }else{
        $to = "$recipients";
      }

      MailManager::$lastError = null;

      if(CoreConfig::MAIL_STANDARD){
        $result = MailManager::sendStandardEmail($to, $subject, $body, $attachments);
      }else{
        $result = MailManager::sendAdvancedEmail($to, $subject, $body, $attachments);
      }

      MailManager::$htmlFormat = true;

      return $result;
    }catch(Exception $exception){
      Log::custom(__CLASS__, $exception->getMessage());
      false;
    }
  }

  /**
   * Send Email
   *
   * @param string $pTo
   * @param string $pCC
   * @param string $pBCC
   * @param string $subject
   * @param string $body
   * @param URI $attachments
   *
   * @return boolean
   */
  public static function sendEmailReport($pTo, $pCC, $pBCC, $subject, $body, $attachments = false)
  {
    $recipients = array();
    if($pTo){
      $recipients["To"] = $pTo;
    }
    if($pCC){
      $recipients["Cc"] = $pCC;
    }
    if($pBCC){
      $recipients["Bcc"] = $pBCC;
    }

    MailManager::$lastError = null;

    $result = MailManager::sendAdvancedEmail($recipients, $subject, $body, $attachments);

    MailManager::$htmlFormat = true;

    return $result;
  }

  /**
   * Get the list of email address to send the email
   *
   * @param array $recipients
   *
   * @return array
   */
  public static function getRecipients($recipients = array())
  {
    if(count($recipients) == 0){
      array_push($recipients, CoreConfig::MAIL_DEV); //default
    }

    return $recipients;
  }

  /**
   * Use this method to send a warning email
   *
   * @param string $className
   * @param string $methodName
   * @param string $description
   */
  public static function sendWarningEmail($className, $methodName, $description)
  {
    $body = "<b>Warning</b><br><br>";
    $body .= "<b>Class Name: </b> $className<br>";
    $body .= "<b>Method Name: </b> $methodName<br>";
    $body .= "<b>Description: </b> $description<br>";
    MailManager::sendEmail(MailManager::getRecipients(), "Warning", $body);
  }

  /**
   * Use this method to send a critical email, like db errors, or logic errors
   *
   * @param string $className
   * @param string $methodName
   * @param string $subject
   * @param string $description
   */
  public static function sendCriticalErrorEmail($subject, $description)
  {
    $body = "<b>Critical Error</b><br><br>";
    $body .= "<b>Description: </b> $description<br>";
    MailManager::sendEmail(MailManager::getRecipients(), $subject, $body);
  }

  /**
   * Use this method to send information, like summaries or process status
   *
   * @param string $subject
   * @param string $description
   */
  public static function sendInfoEmail($subject, $description)
  {
    $body = "<b>Information</b><br><br>";
    $body .= "<b>Description: </b> $description<br>";

    MailManager::sendEmail(MailManager::getRecipients(), $subject, $body);
  }

  /**
   * get email template and replace variables
   *
   * @param string $name
   * @param array $tags [optional]
   * @param string $lang [optional]
   *
   * @return string
   */
  public static function getEmailTemplate($name, $tags = array(), $lang = null)
  {
    $templatePath = CoreConfig::TEMPLATE_PATH . $name . $lang . CoreConfig::TEMPLATE_FILE_EXTENSION;
    if(!Util::file_exists($templatePath)){
      $templatePath = CoreConfig::TEMPLATE_PATH . $name . CoreConfig::TEMPLATE_FILE_EXTENSION;
      if(!Util::file_exists($templatePath)){
        return "";
      }
    }

    $template = file_get_contents($templatePath, FILE_USE_INCLUDE_PATH);
    if(is_array($tags) && count($tags) > 0){
      foreach($tags as $key => $value){
        //If value is Empty don't replace
        if($value){
          $template = preg_replace("/\{" . "@$key@" . "\}/", $value, $template);
        }
      }
    }

    return $template;
  }

  /**
   * get email template and replace variables, return subject
   *
   * @param string $name
   * @param array $params [optional]
   * @param string $lang [optional]
   *
   * @return string
   */
  public static function getEmailTemplateAndSubject($name, $params = array(), $lang = null)
  {
    if($lang){
      if(strpos($lang, "-")){
        //remove the dialect
        $lang = trim($lang);
        $lang = substr($lang, 0, strpos($lang, "-"));
        $lang = strtolower($lang);
      }
    }

    $body = self::getEmailTemplate($name, $params, $lang);
    $subject = Util::getStringBetween($body, ">>>>>", "<<<<<");
    $body = str_replace(">>>>>$subject<<<<<", "", $body);

    return array('subject' => $subject, 'body' => $body);
  }

  /**
   * This function is used by php5.3 if the fileinfo library no exist. Return the file content type
   * @param $file
   * @return mixed|string
   */

  public static function getContentTypeFromFile($file)
  {
    $mime_types = array(

      'txt' => 'text/plain',
      'htm' => 'text/html',
      'html' => 'text/html',
      'php' => 'text/html',
      'css' => 'text/css',
      'js' => 'application/javascript',
      'json' => 'application/json',
      'xml' => 'application/xml',
      'swf' => 'application/x-shockwave-flash',
      'flv' => 'video/x-flv',

      // images
      'png' => 'image/png',
      'jpe' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'jpg' => 'image/jpeg',
      'gif' => 'image/gif',
      'bmp' => 'image/bmp',
      'ico' => 'image/vnd.microsoft.icon',
      'tiff' => 'image/tiff',
      'tif' => 'image/tiff',
      'svg' => 'image/svg+xml',
      'svgz' => 'image/svg+xml',

      // archives
      'zip' => 'application/zip',
      'rar' => 'application/x-rar-compressed',
      'exe' => 'application/x-msdownload',
      'msi' => 'application/x-msdownload',
      'cab' => 'application/vnd.ms-cab-compressed',

      // audio/video
      'mp3' => 'audio/mpeg',
      'qt' => 'video/quicktime',
      'mov' => 'video/quicktime',

      // adobe
      'pdf' => 'application/pdf',
      'psd' => 'image/vnd.adobe.photoshop',
      'ai' => 'application/postscript',
      'eps' => 'application/postscript',
      'ps' => 'application/postscript',

      // ms office
      'doc' => 'application/msword',
      'rtf' => 'application/rtf',
      'xls' => 'application/vnd.ms-excel',
      'ppt' => 'application/vnd.ms-powerpoint',

      // open office
      'odt' => 'application/vnd.oasis.opendocument.text',
      'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
    );

    $ext = strtolower(array_pop(explode('.', $file)));
    if(array_key_exists($ext, $mime_types)){
      return $mime_types[$ext];
    }elseif(function_exists('finfo_open')){
      $finfo = finfo_open(FILEINFO_MIME);
      $mimetype = finfo_file($finfo, $file);
      finfo_close($finfo);
      return $mimetype;
    }else{
      return 'application/octet-stream';
    }
  }

}

?>
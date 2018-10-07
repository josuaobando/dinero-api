<?php

/**
 * @author Josua
 */
class ProviderNotifications
{

  /**
   * ProviderNotifications constructor.
   */
  public function __construct()
  {
  }

  /**
   * send email notification
   *
   * @param string $subject
   * @param string $body
   * @param string $template
   * @param array $recipients
   */
  public function mail($subject, $body, $template = 'default', $recipients = array())
  {
    try{
      if(!$recipients || count($recipients) <= 0){
        $recipients = MailManager::getRecipients();
      }

      $bodyEmail = $body;
      if($template && $template != 'default'){
        $bodyEmail = MailManager::getEmailTemplate($template, array('body' => $body, 'message' => ''));
      }

      $send = MailManager::sendEmail($recipients, $subject, $bodyEmail);
      if(!$send){
        $error = MailManager::getLastError();
        Log::custom(__CLASS__, "The email notification '$subject' could not be sent $error");
      }
    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
    }
  }

  /**
   * send sms notification
   *
   * @param string $message
   * @param string|array $recipients
   */
  public function sms($message, $recipients)
  {
    try{
      $provider = new Nexmo();
      if(is_array($recipients) && count($recipients) > 0){
        foreach($recipients as $recipient){
          $send = $provider->sendSMS($message, $recipient);
          if(!$send){
            $error = $provider->getApiMessage();
            Log::custom(__CLASS__, "The sms notification '$message' could not be sent $error");
          }
        }
      }else{
        $send = $provider->sendSMS($message, $recipients);
        if(!$send){
          $error = $provider->getApiMessage();
          Log::custom(__CLASS__, "The sms notification '$message' could not be sent $error");
        }
      }
    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
    }
  }

}

?>
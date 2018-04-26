<?php

/**
 * @author Josua
 */
class MQ_Exception extends MQ_Type
{

  /**
   * @see MQ_Type::process()
   */
  protected function process($data)
  {
    $exceptionData = $data['exception'];

    if($exceptionData){
      $exception = Encrypt::unpack($exceptionData);
      $exception instanceof Exception;
    }

    //notify by email.
    MailManager::sendCriticalErrorEmail("System Exception", nl2br($exception));

    //store exception in filesystem
    Log::exception($exception);

    return true;
  }

}

?>
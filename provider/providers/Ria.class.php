<?php

/**
 * @author Josua
 */
class Ria extends Provider
{

  /**
   * ID
   */
  const PROVIDER_ID = 5;

  const STATUS_API_REQUESTED = 'requested';
  const STATUS_API_PENDING = 'pending';
  const STATUS_API_APPROVED = 'approved';
  const STATUS_API_REJECTED = 'rejected';
  const STATUS_API_ERROR = 'error';

  /**
   * new Transaction instance
   */
  public function __construct()
  {
    parent::__construct(self::PROVIDER_ID);
  }

  /**
   * get receiver for the customer
   *
   * @return Person
   */
  public function receiver()
  {
    try{
      $customer = Session::getCustomer();
      $transaction = Session::getTransaction();

      //transaction
      $params = array();
      $params['ctacte'] = $transaction->getUsername();
      $params['firstname'] = $customer->getFirstName();
      $params['lastname'] = $customer->getLastName();
      $params['city'] = $customer->getStateName();
      $params['state'] = $customer->getState();
      $params['country'] = $customer->getCountry();
      $params['monto'] = $transaction->getAmount();

      //execute request
      $this->request = $params;
      $this->execute('GetName');
      $response = $this->getResponse();
      if($this->status == self::REQUEST_ERROR){
        return null;
      }

      if($this->status == self::STATUS_API_REQUESTED){

        $name = trim($response->recibe);
        $personalId = Encrypt::generateMD5($name);

        $person = new Person();
        $person->setPersonLisId(CoreConfig::AGENCY_ID_SATURNO_RIA);
        $person->setCountry('CR');
        $person->setCountryId(52);
        $person->setCountryName('Costa Rica');
        $person->setState('SJ');
        $person->setStateId(877);
        $person->setStateName('San José');
        $person->setAvailable(1);
        $person->setIsActive(1);
        $person->setName($name);
        $person->setLastName('');
        $person->setPersonalId($personalId);
        $person->setTypeId('Hash');
        $person->setExpirationDateId('NR');
        $person->setAddress('NR');
        $person->setCity('San José');
        $person->setBirthDate('NR');
        $person->setMaritalStatus('NR');
        $person->setGender('NR');
        $person->setProfession('NR');
        $person->setPhone('NR');
        $person->setNameId($personalId);
        $person->add();

        if($this->id){
          $transaction->setApiTransactionId($this->id);
          return $person;
        }

        return null;
      }elseif($this->status == self::STATUS_API_ERROR){

        if(stripos($this->message, 'No Names Available') !== false){

          $subject = "No deposit names available";
          $body = "There are no deposit names available in Saturn agency";
          $bodyTemplate = MailManager::getEmailTemplate('default', array('body' => $body));
          $recipients = array('To' => 'mgoficinasf0117@outlook.com', 'Cc' => CoreConfig::MAIL_DEV);
          MailManager::sendEmail($recipients, $subject, $bodyTemplate);

          Log::custom(__CLASS__, $body);
          $this->message = 'We cannot give this Customer a name';
          return null;
        }elseif(stripos(strtolower($this->message), 'black') && stripos(strtolower($this->message), 'list')){
          $this->message = 'The Customer has been blacklisted';
          return null;
        }elseif(stripos(strtolower($this->message), 'limit') && stripos(strtolower($this->message), 'reached')){
          $this->message = 'Limits: The Customer has exceeded the limits in MG';
          return null;
        }

        $this->message = 'We cannot give this Customer a name';
        Log::custom(__CLASS__, "Unmapped message" . "\n Response: \n\n" . Util::objToStr($response));
      }

    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
    }

    return null;
  }

  /**
   * get name for the customer
   *
   * @return Person
   *
   * @throws InvalidStateException
   */
  public function sender()
  {
    throw new InvalidStateException("'" . __METHOD__ . "' must be implemented in '" . get_class($this) . "' class.");
  }

  /**
   * Submit or Re-Submit transaction
   *
   * @return bool
   */
  public function confirm()
  {
    try{
      $customer = Session::getCustomer();
      $transaction = Session::getTransaction();
      $apiTransactionId = $transaction->getApiTransactionId();
      $transactionStatus = $transaction->getTransactionStatusId();
      $isSubmit = ($transactionStatus == Transaction::STATUS_REQUESTED);

      //transaction
      $params = array();
      $params['trans'] = $apiTransactionId;
      $params['monto'] = $transaction->getAmount();
      if(!$isSubmit){
        $params['envia'] = $customer->getCustomer();
      }
      $params['documento'] = $transaction->getControlNumber();

      $method = ($isSubmit) ? 'SubmitDeposit' : 'EditDeposit';
      //execute request
      $this->request = $params;
      $this->execute($method);
      $response = $this->getResponse();

      if($this->status == self::STATUS_API_PENDING){
        return true;
      }elseif($this->status == self::STATUS_API_ERROR || $this->status == self::REQUEST_ERROR){

        try{
          if($isSubmit){
            $subject = "Problem submit transaction";
            $body = "Trans Id: $apiTransactionId";
          }else{
            $subject = "Problem re-submit transaction";
            $body = "Trans Id: $apiTransactionId";
          }

          $body .= "<br>" . "Status: $response->status";
          $body .= "<br><br>" . "Comentario: $response->comentario";
          $body .= "<br><br>" . "Request:";
          $body .= "<br><br>" . $this->getLastRequest();
          $body .= "<br><br>" . "Response:";
          $body .= "<br><br>" . Util::objToStr($response);

          $bodyTemplate = MailManager::getEmailTemplate('default', array('body' => $body));
          MailManager::sendEmail(MailManager::getRecipients(), $subject, $bodyTemplate);

          return false;
        }catch(WSException $ex){
          ExceptionManager::handleException($ex);
        }
      }

    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
    }

    return false;
  }

  /**
   * get status
   *
   * @return bool
   */
  public function status()
  {
    try{
      //get transaction object
      $transaction = Session::getTransaction();
      $currentTransactionStatusId = $transaction->getTransactionStatusId();

      //transaction
      $params = array();
      $params['trans'] = $transaction->getApiTransactionId();

      //execute request
      $this->request = $params;
      $this->execute('getDeposit');
      $response = $this->getResponse();

      //validate trackId
      if($this->id != $transaction->getApiTransactionId()){
        Log::custom(__CLASS__, "Transaction ID mismatch" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
        return false;
      }

      switch($this->status){
        case self::STATUS_API_APPROVED:
          $transaction->setTransactionStatusId(Transaction::STATUS_APPROVED);
          $transaction->setReason('Ok');
          $transaction->setAmount($response->monto);
          break;
        case self::STATUS_API_REJECTED:
          $transaction->setTransactionStatusId(Transaction::STATUS_REJECTED);
          $transaction->setReason($this->message);
          break;
        case self::STATUS_API_PENDING:
          $transaction->setTransactionStatusId(Transaction::STATUS_SUBMITTED);
          break;
        case self::STATUS_API_REQUESTED:
          $transaction->setTransactionStatusId(Transaction::STATUS_REQUESTED);
          break;
        default:
          Log::custom(__CLASS__, "Invalid Object Response" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
          return false;
      }

      //update transaction
      if($currentTransactionStatusId != $transaction->getTransactionStatusId()){
        if($transaction->getTransactionStatusId() == Transaction::STATUS_APPROVED || $transaction->getTransactionStatusId() == Transaction::STATUS_REJECTED){
          $transaction->update();
        }
      }

    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
    }

  }

  /**
   * execute request
   *
   * @see Provider::execute()
   *
   * @param $method
   */
  protected function execute($method = null)
  {
    try{
      //credentials params
      $params = array();
      $params['user'] = $this->getSetting(self::SETTING_USER);
      $params['password'] = $this->getSetting(self::SETTING_PASSWORD);
      //request params
      $params = array_merge($params, $this->getRequest());

      //make ws request
      $url = $this->getSetting(self::SETTING_URL);
      $response = $this->execSoapSimple($url, $method, $params, array('uri' => 'http://beans/', 'soapaction' => ''));

      //get response
      $this->response = $response;
      $this->unpack($response);
    }catch(WSException $ex){
      ExceptionManager::handleException($ex);
    }
  }

  /**
   * @see Provider::unpack()
   *
   * @param $response
   */
  protected function unpack($response)
  {
    try{
      if($response && $response instanceof stdClass){
        $this->id = $response->trans;
        $this->code = $response->lstatus;
        $this->status = strtolower($response->status);
        $this->message = $response->comentario;
      }else{
        $this->code = self::REQUEST_ERROR;
        $this->message = 'At this time, we can not carry out. Please try again in a few minutes!';
        Log::custom(__CLASS__, "Invalid Object Response" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
      }
    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
    }
  }

}

?>
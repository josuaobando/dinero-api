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

  /**
   * agency id
   */
  const AGENCY_ID = 102;

  const STATUS_API_REQUESTED = 'requested';
  const STATUS_API_PENDING = 'pending';
  const STATUS_API_APPROVED = 'approved';
  const STATUS_API_REJECTED = 'rejected';
  const STATUS_API_CANCELED = 'cancelled';
  const STATUS_API_ERROR = 'error';
  const RESPONSE_ERROR = 'fail';
  const RESPONSE_SUCCESS = '0';

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
   *
   * @throws APIBlackListException|APIException|APILimitException|APIPersonException
   */
  public function receiver()
  {
    $customer = Session::getCustomer();
    $transaction = Session::getTransaction();

    //validate if customer is blacklisted
    $customer->isBlacklisted();
    /*
        if($transaction->getAmount() < 60){
          throw new APILimitException("The minimum allowed amount is: 60 USD");
        }elseif($transaction->getAmount() > 460){
          throw new APILimitException("The maximum allowed amount is: 460 USD");
        }
    */

    //transaction
    $request = array();
    $request['ctacte'] = $transaction->getUsername();
    $request['firstname'] = $customer->getFirstName();
    $request['lastname'] = $customer->getLastName();
    $request['city'] = $customer->getStateName();
    $request['state'] = $customer->getState();
    $request['country'] = $customer->getCountry();
    $request['monto'] = $transaction->getAmount();

    //execute request
    $this->request = $request;
    $this->execute('GetName');
    $response = $this->getResponse();

    if($response && $response instanceof stdClass){
      if($this->apiStatus == self::STATUS_API_REQUESTED){

        $name = trim($response->recibe);
        $personalId = Encrypt::generateMD5($name);

        $person = new Person();
        $person->setPersonLisId(self::AGENCY_ID);
        $person->setCountry('CR');
        $person->setCountryId(52);
        $person->setCountryName('Costa Rica');
        $person->setState('SJ');
        $person->setStateId(877);
        $person->setStateName('San José');
        $person->setAvailable(1);
        $person->setIsActive(0);
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

        $transaction->setAgencyId(self::AGENCY_ID);
        $transaction->setProviderId(self::PROVIDER_ID);
        $transaction->setApiTransactionId($this->apiTransactionId);

        return Session::setPerson($person);
      }elseif($this->apiStatus == self::STATUS_API_ERROR || $this->apiCode != self::RESPONSE_SUCCESS){

        if(stripos($this->apiMessage, 'No Names Available') !== false){

          $subject = "No deposit names available";
          $body = "There are no deposit names available in Ria-Saturno agency";
          $bodyTemplate = MailManager::getEmailTemplate('default', array('body' => $body, 'message' => $this->apiMessage));
          $recipients = array('To' => 'mgoficinasf0117@outlook.com', 'Cc' => CoreConfig::MAIL_DEV);
          MailManager::sendEmail($recipients, $subject, $bodyTemplate);
          Log::custom(__CLASS__, $body);

          throw new APIPersonException('We cannot give a Receiver for this Customer (Sender)');
        }elseif(stripos(strtolower($this->apiMessage), 'black') && stripos(strtolower($this->apiMessage), 'list')){
          $this->apiMessage = 'The Customer (Sender) has been blacklisted';
          throw new APIBlackListException($this->apiMessage);
        }elseif(stripos(strtolower($this->apiMessage), 'lista') && stripos(strtolower($this->apiMessage), 'negra')){
          $this->apiMessage = 'The Customer (Sender) has been blacklisted';
          throw new APIBlackListException($this->apiMessage);
        }elseif(stripos(strtolower($this->apiMessage), 'limit') && stripos(strtolower($this->apiMessage), 'reached')){
          $this->apiMessage = 'The Customer (Sender) has exceeded the limits in Ria';
          throw new APILimitException($this->apiMessage);
        }elseif($this->apiCode == 111){
          //ERROR DE CREDENCIALES Y FALTA DE INFORMACION
          $this->apiMessage = 'The minimum allowed amount is $60 USD and maximum allowed amount is $460 USD';
          throw new APILimitException($this->apiMessage);
        }elseif($this->apiMessage){
          throw new APIException($this->apiMessage);
        }

      }
    }

    Log::custom(__CLASS__, "Invalid Object Response" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
    $this->apiMessage = 'We cannot give a Receiver for this Customer (Sender)';
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
    return new Person();
  }

  /**
   * Submit or Re-Submit transaction
   *
   * @return bool
   *
   * @throws APIException|LimitException
   */
  public function confirm()
  {
    $customer = Session::getCustomer();
    $transaction = Session::getTransaction();
    $apiTransactionId = $transaction->getApiTransactionId();
    $transactionStatus = $transaction->getTransactionStatusId();
    $isSubmit = ($transactionStatus == Transaction::STATUS_REQUESTED);

    if($transaction->getAmount() < 60){
      throw new LimitException("The minimum allowed amount is: 60 USD");
    }elseif($transaction->getAmount() > 450){
      throw new LimitException("The maximum allowed amount is: 450 USD");
    }

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

    if($this->apiStatus == self::STATUS_API_PENDING){

      if($this->apiTransactionId){
        $transaction->setApiTransactionId($this->apiTransactionId);
      }
      return true;

    }elseif($this->apiStatus == self::STATUS_API_ERROR || $this->apiCode != self::RESPONSE_SUCCESS){

      if($isSubmit){
        $subject = "Problem submit transaction";
      }else{
        $subject = "Problem re-submit transaction";
      }

      $body = "Trans Id: $apiTransactionId";

      $body .= "<br>" . "Status: $response->status";
      $body .= "<br>" . "lStatus: $response->lstatus";
      $body .= "<br>" . "Comentario: $response->comentario";
      $body .= "<br><br>" . "Request:";
      $body .= "<br><br>" . $this->getLastRequest();
      $body .= "<br><br>" . "Response:";
      $body .= "<br><br>" . Util::objToStr($response);
      $bodyTemplate = MailManager::getEmailTemplate('default', array('body' => $body));
      MailManager::sendEmail(MailManager::getRecipients(), $subject, $bodyTemplate);

      Log::custom(__CLASS__, $body);
      throw new APIException("$subject. Please try again in a few minutes!");
    }

    Log::custom(__CLASS__, "Invalid Object Response" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
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

      if($response && $response instanceof stdClass){

        //validate trackId
        if($this->apiTransactionId != $transaction->getApiTransactionId()){
          Log::custom(__CLASS__, "Transaction ID mismatch" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
          return false;
        }

        switch($this->apiStatus){
          case self::STATUS_API_APPROVED:
            $transaction->setTransactionStatusId(Transaction::STATUS_APPROVED);
            $transaction->setReason('Ok');
            //only change amount in deposits
            if($transaction->getTransactionTypeId() == Transaction::TYPE_RECEIVER){
              if(is_numeric($response->monto)){
                $transaction->setAmount($response->monto);
              }
            }
            if((strtolower($this->apiMessage) != 'ninguno') && strlen($this->apiMessage)){
              $transaction->setNote($this->apiMessage);
            }
            break;
          case self::STATUS_API_REJECTED:
          case self::STATUS_API_CANCELED:
            $transaction->setTransactionStatusId(Transaction::STATUS_REJECTED);
            $transaction->setReason($this->apiMessage);
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
      $this->apiCode = self::RESPONSE_ERROR;
      $this->apiMessage = 'At this time, we can not process your request. Please try again in a few minutes!';
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
        $this->apiTransactionId = $response->trans;
        $this->apiCode = $response->lstatus;
        $this->apiStatus = strtolower($response->status);
        $this->apiMessage = $response->comentario;
      }else{
        $this->apiCode = self::RESPONSE_ERROR;
        $this->apiMessage = 'At this time, we can not process your request. Please try again in a few minutes!';
        Log::custom(__CLASS__, "Invalid Object Response" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
      }
    }catch(Exception $ex){
      $this->apiCode = self::RESPONSE_ERROR;
      $this->apiMessage = 'At this time, we can not process your request. Please try again in a few minutes!';
      ExceptionManager::handleException($ex);
    }
  }

}

?>
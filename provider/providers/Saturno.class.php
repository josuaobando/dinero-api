<?php

/**
 * @author Josua
 */
class Saturno extends Provider
{

  /**
   * ID
   */
  const PROVIDER_ID = 3;

  /**
   * agency id
   */
  const AGENCY_ID = 100;

  const STATUS_API_REQUESTED = 'requested';
  const STATUS_API_PENDING = 'pending';
  const STATUS_API_APPROVED = 'approved';
  const STATUS_API_REJECTED = 'rejected';
  const STATUS_API_CANCELED = 'cancelled';
  const STATUS_API_ERROR = 'error';
  const RESPONSE_ERROR = 'fail';

  /**
   * new Transaction instance
   */
  public function __construct()
  {
    parent::__construct(self::PROVIDER_ID);
  }

  /**
   * get receiver
   *
   * @return Person
   *
   * @throws APIBlackListException|APILimitException|APIPersonException
   */
  public function receiver()
  {
    $customer = Session::getCustomer();
    $transaction = Session::getTransaction();

    //transaction
    $request = array();
    $request['sendername'] = $customer->getCustomer();
    $request['senderaccount'] = $transaction->getUsername();
    $request['amount'] = $transaction->getAmount();

    //execute request
    $this->request = $request;
    $this->execute('ObtenerNombre');
    $response = $this->getResponse();

    if($response && $response instanceof stdClass){
      if($this->apiStatus == self::STATUS_API_REQUESTED){

        $name = $response->recibe;
        $personalId = $response->nameId;

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
        $person->setTypeId('ID');
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

        return Session::setPerson($person);
      }elseif($this->apiStatus == self::STATUS_API_ERROR || $this->apiCode == self::RESPONSE_ERROR){

        if(stripos($this->apiMessage, 'No Names Available') !== false){

          $subject = "No deposit names available";
          $body = "There are no deposit names available in Saturn agency";
          $bodyTemplate = MailManager::getEmailTemplate('default', array('body' => $body));
          $recipients = array('To' => 'mgoficinasf0117@outlook.com', 'Cc' => CoreConfig::MAIL_DEV);
          MailManager::sendEmail($recipients, $subject, $bodyTemplate);
          Log::custom(__CLASS__, $body);

          throw new APIPersonException('We cannot give a Receiver for this Customer (Sender)');
        }elseif(stripos(strtolower($this->apiMessage), 'black') && stripos(strtolower($this->apiMessage), 'list')){
          $this->apiMessage = 'The Customer (Sender) has been blacklisted';
          throw new APIBlackListException($this->apiMessage);
        }elseif(stripos(strtolower($this->apiMessage), 'limit') && stripos(strtolower($this->apiMessage), 'reached')){
          $this->apiMessage = 'Limits: The Customer (Sender) has exceeded the limits in MG';
          throw new APILimitException($this->apiMessage);
        }

      }
    }

    Log::custom(__CLASS__, "Invalid Object Response" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
    $this->apiMessage = 'We cannot give a Receiver for this Customer (Sender)';
    return null;
  }

  /**
   * get sender name
   *
   * @return null|Person
   *
   * @throws APILimitException
   */
  public function sender()
  {
    $customer = Session::getCustomer();
    $transaction = Session::getTransaction();

    //transaction
    $request = array();
    $request['amount'] = $transaction->getAmount();
    $request['receivername'] = $customer->getCustomer();
    $request['receivercity'] = $customer->getStateName();
    $request['receiverstate'] = $customer->getState();
    $request['receivercountry'] = $customer->getCountry();

    //execute request
    $this->request = $request;
    $this->execute('SubmitPayout');
    $response = $this->getResponse();

    if($response && $response instanceof stdClass){
      if($this->apiStatus == self::STATUS_API_PENDING){

        $name = trim($response->envia);
        $personalId = Encrypt::generateMD5($name);

        $person = new Person();
        $person->setPersonLisId(CoreConfig::AGENCY_ID_SATURNO);
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

        if($response->trackId){
          $person->add();
          $transaction->setApiTransactionId($response->trackId);
          if(is_numeric($response->cargo)){
            $transaction->setFee($response->cargo);
          }

          $transaction->setAgencyId(self::AGENCY_ID);
          $transaction->setProviderId(self::PROVIDER_ID);
          return Session::setPerson($person);
        }

      }elseif($this->apiStatus == self::STATUS_API_ERROR || $this->apiCode == self::STATUS_API_ERROR){

        if(stripos($this->apiMessage, 'No Names Available') !== false || stripos($this->apiMessage, 'No Payouts Names Available') !== false){

          $subject = "No payouts names available";
          $body = "There are no payouts names available in Saturn agency";
          $bodyTemplate = MailManager::getEmailTemplate('default', array('body' => $body));
          $recipients = array('To' => 'mgoficinasf0117@outlook.com', 'Cc' => CoreConfig::MAIL_DEV);
          MailManager::sendEmail($recipients, $subject, $bodyTemplate);
          Log::custom(__CLASS__, $body);

        }elseif(stripos(strtolower($this->apiMessage), 'black') && stripos(strtolower($this->apiMessage), 'list')){
          $this->apiMessage = 'The Customer (Receiver) has been blacklisted';
          return null;
        }elseif(stripos(strtolower($this->apiMessage), 'limit') && stripos(strtolower($this->apiMessage), 'reached')){
          $this->apiMessage = 'Limits: The Customer (Receiver) has exceeded the limits in MG';
          throw new APILimitException($this->apiMessage);
        }

      }
    }

    Log::custom(__CLASS__, "Invalid Object Response" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
    $this->apiMessage = 'We cannot give a Sender for this Customer (Receiver)';
    return null;
  }

  /**
   * Submit or Re-Submit transaction
   *
   * @return bool
   *
   * @throws APIException
   */
  public function confirm()
  {
    $transaction = Session::getTransaction();
    $person = Session::getPerson($transaction->getPersonId());
    $customer = Session::getCustomer($transaction->getCustomerId());
    $apiTransactionId = $transaction->getApiTransactionId();
    $nameId = $person->getPersonalId();

    //transaction
    $request = array();
    if($apiTransactionId){
      $request['trackid'] = $apiTransactionId;
    }else{
      $request['nameid'] = $nameId;
    }
    $request['amount'] = $transaction->getAmount();
    $request['controlnumber'] = $transaction->getControlNumber();
    //customer
    $request['sendername'] = $customer->getCustomer();
    $request['sendercity'] = $customer->getStateName();
    $request['senderstate'] = $customer->getState();
    $request['sendercountry'] = $customer->getCountry();
    $method = ($apiTransactionId) ? 'EditarDeposito' : 'SubmitDeposito';

    //execute request
    $this->request = $request;
    $this->execute($method);
    $response = $this->getResponse();

    if($response && $response instanceof stdClass){

      $this->apiMessage = $response->comentario;
      $this->apiStatus = strtolower($response->status);

      if($this->apiStatus == self::STATUS_API_PENDING){

        $this->apiTransactionId = $response->trackId;
        $transaction->setApiTransactionId($this->apiTransactionId);
        return true;

      }elseif($this->apiStatus == self::STATUS_API_ERROR || $this->apiCode == self::RESPONSE_ERROR){

        if($apiTransactionId){
          $subject = "Problem re-submit transaction";
          $body = "TrackId $apiTransactionId";
        }else{
          $subject = "Problem submit transaction";
          $body = "Nameid $nameId";
        }

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
    }

    Log::custom(__CLASS__, "Invalid Object Response" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
    return false;
  }

  /**
   * get transaction status
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
      $request = array();
      $request['trackid'] = $transaction->getApiTransactionId();
      $method = ($transaction->getTransactionTypeId() == Transaction::TYPE_SENDER) ? 'GetPayout' : 'GetDeposito';

      //execute request
      $this->request = $request;
      $this->execute($method);
      $response = $this->getResponse();

      if($response && $response instanceof stdClass){

        //validate trackId
        if($response->trackId != $transaction->getApiTransactionId()){
          Log::custom(__CLASS__, "Transaction ID mismatch" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
          return false;
        }

        switch($this->apiStatus){
          case self::STATUS_API_APPROVED:
            if($transaction->getTransactionTypeId() == Transaction::TYPE_SENDER){
              if($response->documento){
                $transaction->setControlNumber($response->documento);
              }else{
                Log::custom(__CLASS__, "Transaction without MTCN" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
                return false;
              }
            }
            $transaction->setTransactionStatusId(Transaction::STATUS_APPROVED);
            $transaction->setReason('Ok');
            if((strtolower($this->apiMessage) != 'ninguno') && strlen($this->apiMessage)){
              $transaction->setNote($this->apiMessage);
            }

            //only change amount in deposits
            if($transaction->getTransactionTypeId() == Transaction::TYPE_RECEIVER){
              if(is_numeric($response->monto)){
                $transaction->setAmount($response->monto);
              }
            }
            break;
          case self::STATUS_API_REJECTED:
            $transaction->setTransactionStatusId(Transaction::STATUS_REJECTED);
            $transaction->setReason($this->apiMessage);
            break;
          case self::STATUS_API_CANCELED:
            if($transaction->getTransactionTypeId() == Transaction::TYPE_SENDER){

              Session::getCustomer($transaction->getCustomerId());
              $newPerson = $this->sender();
              if($newPerson instanceof Person && $newPerson->getPersonId()){
                //block person
                $newPerson->block();
                //set new personId
                $transaction->setTransactionStatusId(Transaction::STATUS_SUBMITTED);
                $transaction->setPersonId($newPerson->getPersonId());
                $transaction->update();
              }else{
                $transaction->setTransactionStatusId(Transaction::STATUS_REJECTED);
                $transaction->setReason($this->apiMessage);
              }

            }else{
              $transaction->setTransactionStatusId(Transaction::STATUS_REJECTED);
              $transaction->setReason($this->apiMessage);
            }
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

      return true;
    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
      return false;
    }
  }

  /**
   * execute request
   *
   * @param $method
   *
   * @throws InvalidStateException
   */
  public function execute($method = null)
  {
    try{
      //credentials params
      $params = array();
      $params['user'] = $this->getSetting(self::SETTING_USER);
      $params['password'] = $this->getSetting(self::SETTING_PASSWORD);
      //request params
      $request = array_merge($params, $this->getRequest());
      //make ws request
      $url = $this->getSetting(self::SETTING_URL);
      $response = $this->execSoapSimple($url, $method, $request, array('uri' => 'http://WS/', 'soapaction' => ''));
      //get response
      $this->response = $response;
      $this->unpack($response);
    }catch(WSException $ex){
      ExceptionManager::handleException($ex);
    }
  }

  /**
   * unpack response
   *
   * @param $response
   *
   * @throws InvalidStateException
   */
  public function unpack($response)
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
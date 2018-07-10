<?php

/**
 * @author Josua
 */
class TransactionAPI extends WS
{

  const STATUS_API_REQUESTED = 'requested';
  const STATUS_API_PENDING = 'pending';
  const STATUS_API_APPROVED = 'approved';
  const STATUS_API_REJECTED = 'rejected';
  const STATUS_API_ERROR = 'error';

  /**
   * @var array
   */
  private $agency;

  /**
   * @var int
   */
  private $apiTransactionId;

  /**
   * @var string
   */
  private $apiMessage;

  /**
   * @var string
   */
  private $apiStatus;

  /**
   * @return int
   */
  public function getApiTransactionId()
  {
    return $this->apiTransactionId;
  }

  /**
   * @param int $apiTransactionId
   */
  public function setApiTransactionId($apiTransactionId)
  {
    $this->apiTransactionId = $apiTransactionId;
  }

  /**
   * @return string
   */
  public function getApiMessage()
  {
    return $this->apiMessage;
  }

  /**
   * @return string
   */
  public function getApiStatus()
  {
    return $this->apiStatus;
  }

  /**
   * new Transaction instance
   */
  public function __construct()
  {
    $tblSystem = TblSystem::getInstance();
    $this->agency = $tblSystem->getAgency(CoreConfig::AGENCY_ID_SATURNO);
  }

  /**
   * get name for the customer
   *
   * @return Person
   */
  public function getName()
  {
    try{
      $customer = Session::getCustomer();
      $transaction = Session::getTransaction();

      $params = array();
      //credentials
      $params['user'] = $this->agency['Setting_User'];
      $params['password'] = $this->agency['Setting_Password'];
      //transaction
      $params['sendername'] = $customer->getCustomer();
      $params['senderaccount'] = $transaction->getUsername();
      $params['amount'] = $transaction->getAmount();

      $url = $this->agency['Setting_URL'];
      $response = $this->execSoapSimple($url, 'ObtenerNombre', $params, array('uri' => 'http://WS/', 'soapaction' => ''));
      if($response && $response instanceof stdClass){

        $this->apiMessage = $response->comentario;
        $this->apiStatus = strtolower($response->status);
        if($this->apiStatus == self::STATUS_API_REQUESTED){

          $name = $response->recibe;
          $personalId = $response->nameId;

          $person = new Person();
          $person->setPersonLisId(100);
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

          return $person;
        }elseif($this->apiStatus == self::STATUS_API_ERROR){

          if(stripos($this->apiMessage, 'No Names Available') !== false){

            $subject = "No Names Available";
            $body = "There are no names available in Saturn agency";
            $bodyTemplate = MailManager::getEmailTemplate('default', array('body' => $body));
            $recipients = array('To' => 'mgoficinasf0117@outlook.com', 'Cc' => CoreConfig::MAIL_DEV);
            MailManager::sendEmail($recipients, $subject, $bodyTemplate);

            Log::custom('Saturno', $body);
            $this->apiMessage = 'We cannot give this Customer a name';
            return null;
          }elseif(stripos(strtolower($this->apiMessage), 'black') && stripos(strtolower($this->apiMessage), 'list')){
            $this->apiMessage = 'The Customer has been blacklisted';
            return null;
          }elseif(stripos(strtolower($this->apiMessage), 'limit') && stripos(strtolower($this->apiMessage), 'reached')){
            $this->apiMessage = 'Limits: The Customer has exceeded the limits in MG';
            return null;
          }

          $this->apiMessage = 'We cannot give this Customer a name';
          return null;
        }

        Log::custom('Saturno', "Invalid Object Response" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
      }

    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
    }

    $this->apiMessage = 'We cannot give this Customer a name';
    return null;
  }

  /**
   * get name for the customer
   *
   * @return Person
   */
  public function getSender()
  {
    try{
      $customer = Session::getCustomer();
      $transaction = Session::getTransaction();

      $params = array();
      //credentials
      $params['user'] = $this->agency['Setting_User'];
      $params['password'] = $this->agency['Setting_Password'];
      //transaction
      $params['amount'] = $transaction->getAmount();
      $params['receivername'] = $customer->getCustomer();
      $params['receivercity'] = $customer->getStateName();
      $params['receiverstate'] = $customer->getState();
      $params['receivercountry'] = $customer->getCountry();

      $url = $this->agency['Setting_URL'];
      $response = $this->execSoapSimple($url, 'SubmitPayout', $params, array('uri' => 'http://WS/', 'soapaction' => ''));
      if($response && $response instanceof stdClass){

        $this->apiMessage = $response->comentario;
        $this->apiStatus = strtolower($response->status);
        if($this->apiStatus == self::STATUS_API_PENDING){

          $name = trim($response->envia);
          $personalId = Encrypt::generateMD5($name);

          $person = new Person();
          $person->setPersonLisId(100);
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

          if($response->trackId){
            $transaction->setApiTransactionId($response->trackId);
            if(is_numeric($response->cargo)){
              $transaction->setFee($response->cargo);
            }
            return $person;
          }

          return null;
        }elseif($this->apiStatus == self::STATUS_API_ERROR){

          if(stripos($this->apiMessage, 'No Names Available') !== false || stripos($this->apiMessage, 'No Payouts Names Available') !== false){

            $subject = "No Payouts Names Available";
            $body = "There are no payouts names available in Saturn agency";
            $bodyTemplate = MailManager::getEmailTemplate('default', array('body' => $body));
            $recipients = array('To' => 'mgoficinasf0117@outlook.com', 'Cc' => CoreConfig::MAIL_DEV);
            MailManager::sendEmail($recipients, $subject, $bodyTemplate);

            Log::custom('Saturno', $body);
            $this->apiMessage = 'We cannot give this Customer a name';
            return null;
          }elseif(stripos(strtolower($this->apiMessage), 'black') && stripos(strtolower($this->apiMessage), 'list')){
            $this->apiMessage = 'The Customer has been blacklisted';
            return null;
          }elseif(stripos(strtolower($this->apiMessage), 'limit') && stripos(strtolower($this->apiMessage), 'reached')){
            $this->apiMessage = 'Limits: The Customer has exceeded the limits in MG';
            return null;
          }

          $this->apiMessage = 'We cannot give this Customer a name';
          return null;
        }

        Log::custom('Saturno', "Invalid Object Response" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
      }

    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
    }

    $this->apiMessage = 'We cannot give this Customer a name';
    return null;
  }

  /**
   * Submit or Re-Submit transaction
   *
   * @return bool
   */
  public function confirm()
  {
    try{

      $transaction = Session::getTransaction();
      $person = Session::getPerson($transaction->getPersonId());
      $customer = Session::getCustomer($transaction->getCustomerId());
      $apiTransactionId = $transaction->getApiTransactionId();
      $nameId = $person->getPersonalId();

      $params = array();
      //credentials
      $params['user'] = $this->agency['Setting_User'];
      $params['password'] = $this->agency['Setting_Password'];
      //transaction
      if($apiTransactionId){
        $params['trackid'] = $apiTransactionId;
      }else{
        $params['nameid'] = $nameId;
      }
      $params['amount'] = $transaction->getAmount();
      $params['controlnumber'] = $transaction->getControlNumber();
      //customer
      $params['sendername'] = $customer->getCustomer();
      $params['sendercity'] = $customer->getStateName();
      $params['senderstate'] = $customer->getState();
      $params['sendercountry'] = $customer->getCountry();

      $method = ($apiTransactionId) ? 'EditarDeposito' : 'SubmitDeposito';
      $url = $this->agency['Setting_URL'];
      $response = $this->execSoapSimple($url, $method, $params, array('uri' => 'http://WS/', 'soapaction' => ''));
      if($response && $response instanceof stdClass){

        $this->apiMessage = $response->comentario;
        $this->apiStatus = strtolower($response->status);

        if($this->apiStatus == self::STATUS_API_PENDING){

          $this->apiTransactionId = $response->trackId;
          return true;

        }elseif($this->apiStatus == self::STATUS_API_ERROR || !$this->apiStatus){

          try{
            if($apiTransactionId){
              $subject = "Problem re-submit transaction";
              $body = "TrackId $apiTransactionId";
            }else{
              $subject = "Problem submit transaction";
              $body = "Nameid $nameId";
            }

            $body .= "\n" . "Status: $response->status";
            $body .= "\n" . "Comentario: $response->comentario";
            $body .= "\n\n" . "Request:";
            $body .= "\n\n" . $this->getLastRequest();
            $body .= "\n\n" . "Response:";
            $body .= "\n\n" . Util::objToStr($response);

            $bodyTemplate = MailManager::getEmailTemplate('default', array('body' => $body));
            MailManager::sendEmail(MailManager::getRecipients(), $subject, $bodyTemplate);

            Log::custom('Saturno', $body);

            return false;
          }catch(WSException $ex){
            ExceptionManager::handleException($ex);
          }
        }
      }

      Log::custom('Saturno', "Invalid Object Response" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
    }

    return false;
  }

  /**
   * get transaction status
   *
   * @return bool
   */
  public function getStatus()
  {
    try{

      //get transaction object
      $transaction = Session::getTransaction();
      $currentTransactionStatusId = $transaction->getTransactionStatusId();

      $params = array();
      //credentials
      $params['user'] = $this->agency['Setting_User'];
      $params['password'] = $this->agency['Setting_Password'];
      //transaction
      $params['trackid'] = $transaction->getApiTransactionId();

      $url = $this->agency['Setting_URL'];
      $method = ($transaction->getTransactionTypeId() == Transaction::TYPE_SENDER) ? 'GetPayout' : 'GetDeposito';

      $response = $this->execSoapSimple($url, $method, $params, array('uri' => 'http://WS/', 'soapaction' => ''));
      if($response && $response instanceof stdClass){

        //validate trackId
        if($response->trackId != $transaction->getApiTransactionId()){
          Log::custom('Saturno', "Transaction ID mismatch" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
          return false;
        }

        $this->apiMessage = $response->comentario;
        $this->apiStatus = strtolower($response->status);
        switch($this->apiStatus){
          case self::STATUS_API_APPROVED:
            if($transaction->getTransactionTypeId() == Transaction::TYPE_SENDER){
              if($response->documento){
                $transaction->setControlNumber($response->documento);
              }else{
                Log::custom('Saturno', "Transaction without MTCN" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
                return false;
              }
            }
            $transaction->setTransactionStatusId(Transaction::STATUS_APPROVED);
            $transaction->setReason('Ok');

            //only change amount in deposits
            if($transaction->getTransactionTypeId() == Transaction::TYPE_RECEIVER){
              $transaction->setAmount($response->monto);
            }
            break;
          case self::STATUS_API_REJECTED:
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
            Log::custom('Saturno', "Invalid Object Response" . "\n Request: \n\n" . $this->getLastRequest() . "\n Response: \n\n" . Util::objToStr($response));
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

}

?>
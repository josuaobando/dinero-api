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
          $nameId = $response->nameId;

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
          $person->setFirstName('');
          $person->setLastName('');
          $person->setPersonalId($nameId);
          $person->setTypeId('ID');
          $person->setExpirationDateId('NR');
          $person->setAddress('NR');
          $person->setCity('San José');
          $person->setBirthDate('NR');
          $person->setMaritalStatus('NR');
          $person->setGender('NR');
          $person->setProfession('NR');
          $person->setPhone('NR');
          $person->setNameId($nameId);
          $person->add();

          return $person;
        }elseif($this->apiStatus == self::STATUS_API_ERROR){
          try{
            if(strpos(strtolower($this->apiMessage), 'no names available')){
              $subject = "There are not names available";
              $body = "There are not names available in agency Saturno";
              MailManager::sendEmail(MailManager::getRecipients(), $subject, $body);
            }
          }catch(WSException $ex){
            ExceptionManager::handleException($ex);
          }
        }

        Log::custom('Saturno', $this->apiMessage . "\n" . $this->getLastRequest());
      }

    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
    }

    return null;
  }

  /**
   * Submit o Re-Submit transaction
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

      $params = array();
      //credentials
      $params['user'] = $this->agency['Setting_User'];
      $params['password'] = $this->agency['Setting_Password'];
      //transaction
      if($apiTransactionId){
        $params['trackid'] = $apiTransactionId;
      }else{
        $params['nameid'] = $person->getNameId();
      }
      $params['amount'] = $transaction->getAmount();
      $params['controlnumber'] = $transaction->getControlNumber();
      //customer
      $params['sendername'] = $customer->getCustomer();
      $params['sendercity'] = $customer->getState();
      $params['senderstate'] = $customer->getState();
      $params['sendercountry'] = $customer->getCountry();

      $method = ($apiTransactionId) ? 'EditarDeposito' : 'SubmitDeposito';
      $url = $this->agency['Setting_URL'];
      $response = $this->execSoapSimple($url, $method, $params, array('uri' => 'http://WS/', 'soapaction' => ''));
      if($response && $response instanceof stdClass){

        $this->apiStatus = strtolower($response->status);
        if($this->apiStatus == self::STATUS_API_PENDING){
          $this->apiTransactionId = $response->trackId;
          return true;
        }elseif($this->apiStatus == self::STATUS_API_ERROR){
          try{
            $this->apiMessage = $response->comentario;
            if($apiTransactionId){
              $subject = "Problem re-submit transaction";
            }else{
              $subject = "Problem submit transaction";
            }
            $body = $this->apiMessage . "\n\n" . $this->getLastRequest();
            MailManager::sendEmail(MailManager::getRecipients(), $subject, $body);
          }catch(WSException $ex){
            ExceptionManager::handleException($ex);
          }
        }

        Log::custom('Saturno', $this->apiMessage . "\n" . $this->getLastRequest());
      }

    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
    }

    return false;
  }

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
      $response = $this->execSoapSimple($url, 'GetDeposito', $params, array('uri' => 'http://WS/', 'soapaction' => ''));
      if($response && $response instanceof stdClass){

        $this->apiStatus = strtolower($response->status);
        switch($this->apiStatus){
          case self::STATUS_API_APPROVED:
            $transaction->setTransactionStatusId(Transaction::STATUS_APPROVED);
            $transaction->setAmount($response->monto);
            $transaction->setFee($response->cargo);
            break;
          case self::STATUS_API_REJECTED:
            $transaction->setTransactionStatusId(Transaction::STATUS_REJECTED);
            $transaction->setReason($response->comentario);
            break;
          case self::STATUS_API_PENDING:
            $transaction->setTransactionStatusId(Transaction::STATUS_SUBMITTED);
            break;
          case self::STATUS_API_REQUESTED:
            $transaction->setTransactionStatusId(Transaction::STATUS_REQUESTED);
            break;
          default:
            Log::custom('Saturno', $this->apiMessage . "\n" . $this->getLastRequest());
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
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

  public $apiTransactionId;

  /**
   * new Transaction instance
   */
  public function __construct()
  {
    $tblSystem = TblSystem::getInstance();
    $this->agency = $tblSystem->getAgency(CoreConfig::AGENCY_ID_SATURNO);
  }

  /**
   * @return Person
   *
   * @throws InvalidStateException
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
      $response = $this->execSoapSimple($url, 'ObtenerNombre', $params);
      if($response && $response instanceof stdClass){

        $status = $response->status;
        if($status == self::STATUS_API_REQUESTED){

          $name = $response->recibe;
          $nameId = $response->nameId;

          $person = new Person();
          $person->setCountry('CR');
          $person->setCountryId(52);
          $person->setCountryName('Costa Rica ');
          $person->setState('SJ');
          $person->setStateId(877);
          $person->setStateName('San José');
          $person->setAvailable(1);
          $person->setIsActive(1);
          $person->setName($name);
          $person->setFirstName($name);
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

          $this->apiTransactionId = $response->trans;
          return $person;
        }elseif($status == self::STATUS_API_ERROR){
          try{
            $message = $response->comentario;
            if(strpos(strtolower($message), 'no names available')){
              $subject = "There are not names available";
              $body = "There are not names available in agency Saturno";
              MailManager::sendEmail(MailManager::getRecipients(), $subject, $body);
            }
            Log::custom('Saturno', $message);
          }catch(WSException $ex){
            ExceptionManager::handleException($ex);
          }
        }
      }

    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
    }

    return null;
  }

  public function confirm()
  {

  }

  public function getStatus()
  {

  }

}

?>
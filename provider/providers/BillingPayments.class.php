<?php

/**
 * @author Josua
 */
class BillingPayments extends Provider
{

  /**
   * ID
   */
  const PROVIDER_ID = 6;

  /**
   * agency id
   */
  const AGENCY_ID = 103;

  const STATUS_API_PENDING = 'pending';
  const STATUS_API_APPROVED = 'approved';
  const STATUS_API_REJECTED = 'rejected';
  const STATUS_API_CANCELED = 'cancelled';

  const RESPONSE_ERROR = -1;
  const RESPONSE_SUCCESS = 0;

  const METHOD_GET_NAME = 'request_name.php';
  const METHOD_SUBMIT_DEPOSIT = 'submit_deposit.php';
  const METHOD_EDIT_DEPOSIT = 'edit_deposit.php';
  const METHOD_SUBMIT_PAYOUT = 'submit_payout.php';
  const METHOD_GET_STATUS = 'get_transaction.php';

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
   * @throws APIBlackListException|APILimitException|APIPersonException|APIException
   */
  public function receiver()
  {
    $customer = Session::getCustomer();
    $transaction = Session::getTransaction();

    //validate if customer is blacklisted
    $customer->isBlacklisted();

    //transaction
    $request = array();
    $request['processor'] = 'MG';
    $request['sender_name'] = $customer->getCustomer();
    $request['sender_account'] = $transaction->getUsername();
    $request['amount'] = $transaction->getAmount();

    //execute request
    $this->request = $request;
    $this->execute(self::METHOD_GET_NAME);
    $this->checkResponse();
    $response = $this->getResponse();

    if($response && $response instanceof stdClass){
      if($this->apiStatus == self::RESPONSE_SUCCESS && $response->name_id){

        $name = $response->receiver_name;
        $personalId = $response->name_id;

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
        $person->setCity($response->receiver_city);
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
      }
    }

    Log::custom(__CLASS__, "Invalid Object Response >> ".__FUNCTION__." >>"." \n Request: \n\n ".$this->getLastRequest()." \n Response: \n\n ".Util::objToStr($response));
    if($this->apiMessage){
      throw new APIException($this->apiMessage);
    }else{
      $this->apiMessage = 'We cannot give a Receiver for this Customer (Sender)';
      throw new APIException($this->apiMessage);
    }
  }

  /**
   * get sender name
   *
   * @return null
   *
   * @throws APILimitException|APIPersonException|APIBlackListException|APIException
   */
  public function sender()
  {
    $customer = Session::getCustomer();
    $transaction = Session::getTransaction();

    //transaction
    $request = array();
    $request['processor'] = 'MG';
    $request['remote_id'] = $transaction->getTransactionId();
    $request['amount'] = $transaction->getAmount();
    $request['receiver_account'] = $transaction->getUsername();
    $request['receiver_name'] = $customer->getCustomer();
    $request['receiver_city'] = $customer->getStateName();
    $request['receiver_state'] = $customer->getState();
    $request['receiver_country'] = $customer->getCountry();
    $request['comments'] = Util::isDEV() ? 'This is a test' : '';

    //execute request
    $this->request = $request;
    $this->execute(self::METHOD_SUBMIT_PAYOUT);
    $this->checkResponse();
    $response = $this->getResponse();

    if($response && $response instanceof stdClass){
      if($this->apiStatus == self::STATUS_API_PENDING){

        $name = trim($response->sender_name);
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
        $person->setCity($response->sender_city);
        $person->setBirthDate('NR');
        $person->setMaritalStatus('NR');
        $person->setGender('NR');
        $person->setProfession('NR');
        $person->setPhone('NR');
        $person->setNameId($personalId);

        if($response->id){
          $person->add();
          $transaction->setApiTransactionId($response->id);
          if(is_numeric($response->fee)){
            $transaction->setFee($response->fee);
          }

          $transaction->setAgencyId(self::AGENCY_ID);
          $transaction->setProviderId(self::PROVIDER_ID);

          return Session::setPerson($person);
        }
      }
    }

    Log::custom(__CLASS__, "Invalid Object Response >> ".__FUNCTION__." >>"." \n Request: \n\n ".$this->getLastRequest()." \n Response: \n\n ".Util::objToStr($response));
    if($this->apiMessage){
      throw new APIException($this->apiMessage);
    }else{
      $this->apiMessage = 'We cannot give a Receiver for this Customer (Receiver)';
      throw new APIException($this->apiMessage);
    }
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
      $request['transaction'] = $apiTransactionId;
    }else{
      $request['remote_id'] = $transaction->getTransactionId();
      $request['name_id'] = $nameId;
    }
    $request['amount'] = $transaction->getAmount();
    $request['control_number'] = $transaction->getControlNumber();
    //customer
    $request['sender_name'] = $customer->getCustomer();
    $request['sender_account'] = $transaction->getUsername();
    $request['sender_city'] = $customer->getStateName();
    $request['sender_state'] = $customer->getState();
    $request['sender_country'] = $customer->getCountry();
    $request['comments'] = Util::isDEV() ? 'This is a test' : '';

    $method = ($apiTransactionId) ? self::METHOD_EDIT_DEPOSIT : self::METHOD_SUBMIT_DEPOSIT;

    //execute request
    $this->request = $request;
    $this->execute($method);
    $response = $this->getResponse();

    if($response && $response instanceof stdClass){
      if($this->apiStatus == self::STATUS_API_PENDING){
        $this->apiTransactionId = $response->id;
        $transaction->setApiTransactionId($this->apiTransactionId);

        return true;
      }else{
        if($apiTransactionId){
          $subject = "Problem re-submit transaction";
          $body = "Id $apiTransactionId";
        }else{
          $subject = "Problem submit transaction";
          $body = "NameId $nameId";
        }

        $body .= "<br>"."Status: $response->status";
        $body .= "<br>"."Error Message: $response->error_message";
        $body .= "<br><br>"."Request:";
        $body .= "<br><br>".$this->getLastRequest();
        $body .= "<br><br>"."Response:";
        $body .= "<br><br>".Util::objToStr($response);
        $bodyTemplate = MailManager::getEmailTemplate('default', array('body' => $body));
        MailManager::sendEmail(MailManager::getRecipients(), $subject, $bodyTemplate);
      }
    }

    Log::custom(__CLASS__, "Invalid Object Response"."\n Request: \n\n ".$this->getLastRequest()." \n Response: \n\n ".Util::objToStr($response));

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
      $request['transaction'] = $transaction->getApiTransactionId();

      //execute request
      $this->request = $request;
      $this->execute(self::METHOD_GET_STATUS);
      $response = $this->getResponse();

      if($response && $response instanceof stdClass){

        //validate Id
        if($response->id != $transaction->getApiTransactionId()){
          Log::custom(__CLASS__, "Transaction ID mismatch"."\n Request: \n\n".$this->getLastRequest()."\n Response: \n\n".Util::objToStr($response));

          return false;
        }

        switch($this->apiStatus){
          case self::STATUS_API_APPROVED:
            if($transaction->getTransactionTypeId() == Transaction::TYPE_SENDER){
              if($response->control_number){
                $transaction->setControlNumber($response->control_number);

                if(is_numeric($response->fee)){
                  $transaction->setFee($response->fee);
                }
              }else{
                Log::custom(__CLASS__, "Transaction without MTCN"."\n Request: \n\n".$this->getLastRequest()."\n Response: \n\n".Util::objToStr($response));

                return false;
              }
            }
            $transaction->setTransactionStatusId(Transaction::STATUS_APPROVED);
            $transaction->setReason('Ok');
            $transaction->setNote($response->comments);

            //only change amount in deposits
            if($transaction->getTransactionTypeId() == Transaction::TYPE_RECEIVER){
              if(is_numeric($response->amount)){
                $transaction->setAmount($response->amount);
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
          default:
            Log::custom(__CLASS__, "Invalid Object Response"."\n Request: \n\n".$this->getLastRequest()."\n Response: \n\n".Util::objToStr($response));

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
      $this->setReader(new Reader_Obj());
      $response = $this->execPost($url.$method, $request);
      //get response
      $this->response = $response;
      $this->unpack($response);
    }catch(WSException $ex){
      ExceptionManager::handleException($ex);
      $this->apiCode = self::RESPONSE_ERROR;
      $this->apiMessage = 'At this time, we can not process your request. Please try again in a few minutes!';
    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
      $this->apiCode = self::RESPONSE_ERROR;
      $this->apiMessage = 'At this time, we can not process your request. Please try again in a few minutes!';
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
    if($response && $response instanceof stdClass){
      $this->apiTransactionId = $response->id;
      $this->apiCode = $response->status;
      $this->apiStatus = ($response->transaction_status) ? strtolower($response->transaction_status) : '';
      $this->apiMessage = '';
      if($this->apiCode != self::RESPONSE_SUCCESS){
        $this->apiMessage = $response->error_message;
      }
    }else{
      $this->apiCode = self::RESPONSE_ERROR;
      $this->apiMessage = 'At this time, we can not process your request. Please try again in a few minutes!';
      Log::custom(__CLASS__, "Invalid Object Response"."\n Request: \n\n".$this->getLastRequest()."\n Response: \n\n".Util::objToStr($response));
    }
  }

  /**
   * @throws APIBlackListException|APIException|APILimitException|APIPersonException
   */
  private function checkResponse()
  {
    if($this->apiCode != self::RESPONSE_SUCCESS){
      switch($this->apiCode){
        case self::RESPONSE_ERROR:
          $this->apiMessage = 'At this time, we can not process your request. Please try again in a few minutes!';
          throw new APIException($this->apiMessage);
          break;
        case 1001: //No Names Available. Please contact Admin

          $subject = "No deposit names available";
          $body = "There are no deposit names available in ".__CLASS__." agency";

          $bodyTemplate = MailManager::getEmailTemplate('default', array('body' => $body, 'message' => $this->apiMessage));
          $recipients = array('To' => 'mgoficinasf0117@outlook.com', 'Cc' => CoreConfig::MAIL_DEV);
          MailManager::sendEmail($recipients, $subject, $bodyTemplate);
          Log::custom(__CLASS__, $body);

          $this->apiMessage = 'We cannot give a Receiver for this Customer';
          throw new APIPersonException($this->apiMessage);
          break;
        case 1037: //No Payouts Names Available. Please contact Admin
          $subject = "No payouts names available";
          $body = "There are no payouts names available in ".__CLASS__." agency";

          $bodyTemplate = MailManager::getEmailTemplate('default', array('body' => $body, 'message' => $this->apiMessage));
          $recipients = array('To' => 'mgoficinasf0117@outlook.com', 'Cc' => CoreConfig::MAIL_DEV);
          MailManager::sendEmail($recipients, $subject, $bodyTemplate);
          Log::custom(__CLASS__, $body);

          $this->apiMessage = 'We cannot give a Receiver for this Customer';
          throw new APIPersonException($this->apiMessage);
          break;
        case 1002: //Access Denied
          $this->apiMessage = 'Access Denied';
          throw new APIException($this->apiMessage);
          break;
        case 1003: //No Transaction Found
          $this->apiMessage = 'This transaction not exist or not can be loaded';
          throw new APIException($this->apiMessage);
          break;
        case 1007: //Sender reached requests limit
          $this->apiMessage = 'The Customer has exceeded the limits';
          throw new APILimitException($this->apiMessage);
          break;
        case 1034: //There are no names available for this Sender, please try again tomorrow.
          $this->apiMessage = 'The Customer has exceeded the limits';
          throw new APILimitException($this->apiMessage);
          break;
        case 1035: //Sender or Receiver is on Black List, please try again or use a different Sender
          $this->apiMessage = 'The Customer has been blacklisted';
          throw new APIBlackListException($this->apiMessage);
          break;
        default:
          if($this->apiMessage){
            throw new APIException($this->apiMessage);
          }else{
            throw new APIException('We cannot give a Receiver for this Customer');
          }
      }
    }
  }

}

?>
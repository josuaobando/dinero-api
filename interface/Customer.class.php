<?php

/**
 * @author Josua
 */
class Customer
{

  /**
   * TblCustomer reference
   *
   * @var TblCustomer
   */
  private $tblCustomer;

  /**
   * TblUtil reference
   *
   * @var TblUtil
   */
  private $tblUtil;

  private $customerId;
  private $agencyId;
  private $agencyTypeId;
  private $firstName;
  private $lastName;
  private $country;
  private $countryId;
  private $countryName;
  private $state;
  private $stateId;
  private $stateName;
  private $phone;
  private $isAPI;
  /**
   * @var Transaction
   */
  private $lastTransaction;

  /**
   * @return int
   */
  public function getCustomerId()
  {
    return $this->customerId;
  }

  /**
   * @return int
   */
  public function getAgencyId()
  {
    return $this->agencyId;
  }

  /**
   * @return int
   */
  public function getAgencyTypeId()
  {
    return $this->agencyTypeId;
  }

  /**
   * @return mixed
   */
  public function getFirstName()
  {
    return $this->firstName;
  }

  /**
   * @return mixed
   */
  public function getLastName()
  {
    return $this->lastName;
  }

  /**
   * @return mixed
   */
  public function getCountry()
  {
    return $this->country;
  }

  /**
   * @return mixed
   */
  public function getCountryId()
  {
    return $this->countryId;
  }

  /**
   * @return mixed
   */
  public function getCountryName()
  {
    return $this->countryName;
  }

  /**
   * @return mixed
   */
  public function getState()
  {
    return $this->state;
  }

  /**
   * @return mixed
   */
  public function getStateId()
  {
    return $this->stateId;
  }

  /**
   * @return mixed
   */
  public function getStateName()
  {
    return $this->stateName;
  }

  /**
   * @return mixed
   */
  public function getPhone()
  {
    return $this->phone;
  }

  /**
   * @return int
   */
  public function getIsAPI()
  {
    if($this->isAPI ||
      $this->agencyId == CoreConfig::AGENCY_ID_SATURNO ||
      $this->agencyId == CoreConfig::AGENCY_ID_NICARAGUA ||
      $this->agencyId == CoreConfig::AGENCY_ID_SATURNO_RIA
    ){
      return 1;
    }
    return 0;
  }

  /**
   * get the customer
   *
   * @return string
   */
  public function getCustomer()
  {
    return $this->firstName . " " . $this->lastName;
  }

  /**
   * get the from representation
   *
   * @return string
   */
  public function getFrom()
  {
    return $this->countryName . ", " . $this->stateName;
  }

  /**
   * @param mixed $customerId
   */
  public function setCustomerId($customerId)
  {
    $this->customerId = $customerId;
  }

  /**
   * @param mixed $agencyId
   */
  public function setAgencyId($agencyId)
  {
    $this->agencyId = $agencyId;
  }

  /**
   * @param mixed $agencyTypeId
   */
  public function setAgencyTypeId($agencyTypeId)
  {
    $this->agencyTypeId = $agencyTypeId;
  }

  /**
   * @param mixed $firstName
   */
  public function setFirstName($firstName)
  {
    $this->firstName = $firstName;
  }

  /**
   * @param mixed $lastName
   */
  public function setLastName($lastName)
  {
    $this->lastName = $lastName;
  }

  /**
   * @param mixed $country
   */
  public function setCountry($country)
  {
    $this->country = $country;
  }

  /**
   * @param mixed $countryId
   */
  public function setCountryId($countryId)
  {
    $this->countryId = $countryId;
  }

  /**
   * @param mixed $countryName
   */
  public function setCountryName($countryName)
  {
    $this->countryName = $countryName;
  }

  /**
   * @param mixed $state
   */
  public function setState($state)
  {
    $this->state = $state;
  }

  /**
   * @param mixed $stateId
   */
  public function setStateId($stateId)
  {
    $this->stateId = $stateId;
  }

  /**
   * @param mixed $stateName
   */
  public function setStateName($stateName)
  {
    $this->stateName = $stateName;
  }

  /**
   * @param mixed $phone
   */
  public function setPhone($phone)
  {
    $this->phone = $phone;
  }

  /**
   * @param mixed $isAPI
   */
  public function setIsAPI($isAPI)
  {
    $this->isAPI = $isAPI;
  }

  /**
   * new instance of customer
   *
   * Customer constructor.
   *
   * @param null $customerId
   */
  public function __construct($customerId = null)
  {
    $this->tblCustomer = TblCustomer::getInstance();
    $this->tblUtil = TblUtil::getInstance();

    if($customerId){
      $this->customerId = $customerId;

      $customerData = $this->tblCustomer->getCustomer($customerId);

      $this->agencyId = $customerData['Agency_Id'];
      $this->agencyTypeId = $customerData['AgencyType_Id'];
      $this->firstName = $customerData['FirstName'];
      $this->lastName = $customerData['LastName'];
      $this->country = $customerData['countryCode'];
      $this->countryId = $customerData['Country_Id'];
      $this->countryName = trim($customerData['countryName']);
      $this->state = $customerData['stateCode'];
      $this->stateId = $customerData['CountryState_Id'];
      $this->stateName = $customerData['stateName'];
      $this->phone = $customerData['Phone'];
      $this->isAPI = $customerData['IsAPI'];
    }
  }

  /**
   * load object using the request
   *
   * @param WSRequest $wsRequest
   *
   * @throws InvalidParameterException|CustomerException
   */
  public function restoreFromRequest($wsRequest)
  {
    $this->agencyTypeId = $wsRequest->requireNumericAndPositive('type');
    $this->firstName = preg_replace('/\s+/', ' ', trim($wsRequest->requireNotNullOrEmpty('first_name')));
    $this->lastName = preg_replace('/\s+/', ' ', trim($wsRequest->requireNotNullOrEmpty('last_name')));
    $this->country = trim($wsRequest->requireNotNullOrEmpty('country'));
    $this->state = trim($wsRequest->requireNotNullOrEmpty('state'));
    $this->phone = trim($wsRequest->requireNotNullOrEmpty('phone'));

    $countryData = $this->tblUtil->getCountry($this->country);
    if(!$countryData){
      //throw new InvalidParameterException('country', $this->country, 'CountryCode');
    }
    $this->countryId = $countryData['Country_Id'];
    $this->countryName = $countryData['Name'];

    $stateData = $this->tblUtil->getState($this->countryId, $this->state);
    if(!$stateData){
      //throw new InvalidParameterException('state', $this->state, 'StateCode');
    }
    $this->stateId = $stateData['CountryState_Id'];
    $this->stateName = $stateData['Name'];
  }

  /**
   * load object using the request
   *
   * @param Account $account
   * @param WSRequest $wsRequest
   *
   * @throws InvalidParameterException
   * @throws CustomerException
   */
  public function validateFromRequest($account, $wsRequest)
  {
    $this->agencyTypeId = $wsRequest->requireNumericAndPositive('type');
    $this->firstName = preg_replace('/\s+/', ' ', trim($wsRequest->requireNotNullOrEmpty('first_name')));
    $this->lastName = preg_replace('/\s+/', ' ', trim($wsRequest->requireNotNullOrEmpty('last_name')));
    $this->country = trim($wsRequest->requireNotNullOrEmpty('country'));
    $this->state = trim($wsRequest->requireNotNullOrEmpty('state'));
    $this->phone = trim($wsRequest->requireNotNullOrEmpty('phone'));

    $countryData = $this->tblUtil->getCountry($this->country);
    if(!$countryData){
      throw new InvalidParameterException('country', $this->country, 'CountryCode');
    }
    $this->countryId = $countryData['Country_Id'];
    $this->countryName = $countryData['Name'];

    $stateData = $this->tblUtil->getState($this->countryId, $this->state);
    if(!$stateData){
      throw new InvalidParameterException('state', $this->state, 'StateCode');
    }
    $this->stateId = $stateData['CountryState_Id'];
    $this->stateName = $stateData['Name'];

    $this->validate($account->getCompanyId(), $account->getAccountId());
    if(!$this->customerId){
      throw new CustomerException("Invalid Customer information");
    }
    if(!$this->agencyId){
      throw new CustomerException("The Agency is not Available");
    }

  }

  /**
   * validate the information and update or create the customer
   *
   * @param int $companyId
   * @param int $accountId
   *
   * @throws CustomerException
   */
  private function validate($companyId, $accountId)
  {
    //validate if exist a similar customer
    $maxPercent = 0;
    $customerNameSimilar = null;
    $customerNameRequest = strtoupper($this->getCustomer());

    //validate if exist a similar customer
    if(CoreConfig::CUSTOMER_SIMILAR_PERCENT_ACTIVE){
      $similarList = $this->tblCustomer->getSimilar($companyId, $this->agencyTypeId, $this->firstName, $this->lastName);
      if($similarList && COUNT($similarList) > 0){
        foreach($similarList as $similar){
          $registerCustomerName = strtoupper($similar['CustomerName']);
          $percent = Util::similarPercent($customerNameRequest, $registerCustomerName);
          if($percent >= CoreConfig::CUSTOMER_SIMILAR_PERCENT && $percent > $maxPercent){

            $this->customerId = $similar['CustomerId'];
            $this->agencyId = $similar['AgencyId'];
            $this->firstName = $similar['FirstName'];
            $this->lastName = $similar['LastName'];

            $maxPercent = $percent;
            $customerNameSimilar = $registerCustomerName;
          }
        }
      }
    }

    if($this->customerId){
      //add log if customer has similar name
      Log::custom('Similar', "Request: $customerNameRequest Register: $customerNameSimilar Percent: $maxPercent");
      $this->isBlacklisted($customerNameSimilar);
    }else{
      //if not have register, check customer from request
      $customerData = $this->tblCustomer->validate($companyId, $accountId, $this->agencyTypeId, $this->firstName, $this->lastName, $this->countryId, $this->stateId, $this->phone);
      $this->customerId = $customerData['CustomerId'];
      $this->agencyId = $customerData['AgencyId'];
    }

  }

  /**
   * update the customer
   *
   * @return int
   */
  public function update()
  {
    return $this->tblCustomer->update($this->agencyId, $this->customerId, $this->firstName, $this->lastName, $this->countryId, $this->stateId, $this->phone, $this->getIsAPI());
  }

  /**
   * Validate if customer [firstname + lastname] is blocked by the Network
   *
   * @param $customerName [optional]
   *
   * @throws CustomerBlackListException
   */
  public function isBlacklisted($customerName = null)
  {
    $customerName = ($customerName) ? $customerName : $this->getCustomer();
    $customerName = strtoupper($customerName);
    $similarList = $this->tblCustomer->getSimilarSearchBlacklisted($this->agencyTypeId, $customerName);
    if($similarList && COUNT($similarList) > 0){
      foreach($similarList as $similar){
        $registerCustomerName = strtoupper($similar['CustomerName']);
        similar_text($customerName, $registerCustomerName, $percent);
        if($percent >= CoreConfig::CUSTOMER_SIMILAR_PERCENT){

          $agencyType = $this->agencyTypeId;
          if($agencyType == Transaction::AGENCY_TYPE_MG){
            $agencyType = 'MG';
          }elseif($agencyType == Transaction::AGENCY_TYPE_WU){
            $agencyType = 'WU';
          }elseif($agencyType == Transaction::AGENCY_TYPE_RIA){
            $agencyType = 'RIA';
          }
          throw new CustomerBlackListException("The Customer has been blacklisted by $agencyType International");
        }
      }
    }
  }

  /**
   * serialize object
   *
   * @return array
   */
  public function toArray()
  {
    $data = array();

    $data['first_name'] = $this->firstName;
    $data['last_name'] = $this->lastName;
    $data['country'] = $this->countryName;
    $data['state'] = $this->stateName;

    return $data;
  }

  /**
   * get customer stats
   *
   * @param int $transactionTypeId
   *
   * @see Transaction::TYPE_RECEIVER, Transaction::TYPE_SENDER
   *
   * @return array
   */
  public function getStats($transactionTypeId = 0)
  {
    return $this->tblCustomer->getStats($this->customerId, $transactionTypeId);
  }

  /**
   * get the las proceed transaction
   *
   * @param null $transactionTypeId
   *
   * @return Transaction
   */
  public function getLastTransaction($transactionTypeId = null)
  {
    if(!$transactionTypeId){
      return $this->lastTransaction;
    }
    $this->lastTransaction = new Transaction();
    $customerTransaction = $this->tblCustomer->getLastTransaction($this->customerId, $this->agencyTypeId, $transactionTypeId);
    if($customerTransaction){
      $transactionId = $customerTransaction['Transaction_Id'];
      $this->lastTransaction->restore($transactionId);
    }
    return $this->lastTransaction;
  }
}

?>
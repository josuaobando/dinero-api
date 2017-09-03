<?php

/**
 * @author Josua
 */
class Customer
{
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

  /**
   * new instance of person
   */
  public function __construct()
  {
    $this->tblCustomer = TblCustomer::getInstance();
    $this->tblUtil = TblUtil::getInstance();
  }

  /**
   * load object using the request
   *
   * @param Account $account
   * @param WSRequest $wsRequest
   *
   * @throws InvalidParameterException
   * @throws InvalidStateException
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
    if(!$countryData)
    {
      throw new InvalidParameterException('country', $this->country, 'CountryCode');
    }
    $this->countryId = $countryData['Country_Id'];
    $this->countryName = $countryData['Name'];

    $stateData = $this->tblUtil->getState($this->countryId, $this->state);
    if(!$stateData)
    {
      throw new InvalidParameterException('state', $this->state, 'StateCode');
    }
    $this->stateId = $stateData['CountryState_Id'];
    $this->stateName = $stateData['Name'];

    $this->validate($account->getCompanyId(), $account->getAccountId());
    if(!$this->customerId){
      throw new InvalidStateException("invalid customer information");
    }
    if(!$this->agencyId){
      throw new InvalidStateException("The agency is not available");
    }

  }

  /**
   * validate the information and update or create the customer
   *
   * @param int $companyId
   * @param int $accountId
   *
   * @throws InvalidStateException
   */
  private function validate($companyId, $accountId)
  {
    //validate if exist a similar customer
    $maxPercent = 0;
    $customerNameSimilar = null;
    $customerNameRequest = $this->getCustomer();

    //validate if customer is blacklisted
    $this->isBlacklisted();

    //validate if exist a similar customer
    if(CoreConfig::CUSTOMER_SIMILAR_PERCENT_ACTIVE){
      $similarList = $this->tblCustomer->getSimilar($companyId, $this->agencyTypeId, $this->firstName, $this->lastName);
      if($similarList && COUNT($similarList) > 0){
        foreach($similarList as $similar){
          $registerCustomerName = $similar['CustomerName'];
          similar_text($customerNameRequest, $registerCustomerName, $percent);
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
   * Validate if customer [firstname + lastname] is blocked by the Network
   *
   * @param $customerName [optional]
   *
   * @throws InvalidStateException
   */
  public function isBlacklisted($customerName = null)
  {
    $customerName = ($customerName) ? $customerName : $this->getCustomer();
    $isBlacklisted = $this->tblCustomer->getIsBlacklisted($customerName, $this->agencyTypeId);
    if($isBlacklisted > 0)
    {
      $agencyType = $this->agencyTypeId;
      if($agencyType == Transaction::AGENCY_MONEY_GRAM)
      {
        throw new InvalidStateException("The Customer has been blacklisted by MG International. Suggest RIA option.");
      }
      elseif($agencyType == Transaction::AGENCY_WESTERN_UNION)
      {
        throw new InvalidStateException("The Customer has been blacklisted by WU International. Suggest RIA option.");
      }
      elseif($agencyType == Transaction::AGENCY_RIA)
      {
        throw new InvalidStateException("The Customer has been blacklisted by RIA International");
      }
    }
  }

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
   * get the customer
   *
   * @return string
   */
  public function getCustomer()
  {
    return $this->firstName." ".$this->lastName;
  }

  /**
   * get the from representation
   *
   * @return string
   */
  public function getFrom()
  {
    return $this->countryName.", ".$this->stateName;
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

}

?>
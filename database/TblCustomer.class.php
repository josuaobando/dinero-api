<?php

/**
 * @author Josua
 */
class TblCustomer extends Db
{

  /**
   * singleton reference for TblCustomer
   *
   * @var TblCustomer
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblCustomer
   *
   * @return TblCustomer
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton)){
      self::$singleton = new TblCustomer();
    }
    return self::$singleton;
  }

  /**
   * get customer data
   *
   * @param string $customerId
   *
   * @return array
   */
  public function getCustomer($customerId)
  {
    $sql = "CALL spCustomer('{customerId}')";

    $params = array();
    $params['customerId'] = $customerId;

    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }

  /**
   * validate customer information
   *
   * @param int $companyId
   * @param int $accountId
   * @param string $firstName
   * @param string $lastName
   * @param int $countryId
   * @param int $countryStateId
   * @param string $phone
   *
   * @return array [CustomerId, AgencyId]
   */
  public function validate($companyId, $accountId, $firstName, $lastName, $countryId, $countryStateId, $phone)
  {
    $sql = "CALL spCustomer_Validate('{companyId}', '{accountId}', '{firstName}', '{lastName}', '{countryId}', '{countryStateId}', '{phone}', @CustomerId)";

    $params = array();
    $params['companyId'] = $companyId;
    $params['accountId'] = $accountId;
    $params['firstName'] = ucwords(strtolower($firstName));
    $params['lastName'] = ucwords(strtolower($lastName));
    $params['countryId'] = $countryId;
    $params['countryStateId'] = $countryStateId;
    $params['phone'] = $phone;

    $this->setOutputParams(array('CustomerId'));
    $this->executeUpdate($sql, $params);
    $output = $this->getOutputResults();

    return $output;
  }

  /**
   * update customer information
   *
   * @param $customerId
   * @param $firstName
   * @param $lastName
   * @param $countryId
   * @param $countryStateId
   * @param $phone
   * @param int $isAPI
   *
   * @return int
   */
  public function update($customerId, $firstName, $lastName, $countryId, $countryStateId, $phone, $isAPI = 0)
  {
    $sql = "CALL spCustomer_Update('{agencyId}', '{customerId}', '{firstName}', '{lastName}', '{countryId}', '{countryStateId}', '{phone}', '{isAPI}')";

    $params = array();
    $params['customerId'] = $customerId;
    $params['firstName'] = ucwords(strtolower($firstName));
    $params['lastName'] = ucwords(strtolower($lastName));
    $params['countryId'] = $countryId;
    $params['countryStateId'] = $countryStateId;
    $params['phone'] = $phone;
    $params['isAPI'] = $isAPI;

    return $this->executeUpdate($sql, $params);
  }

  /**
   * @param $companyId
   * @param $agencyTypeId
   * @param $firstName
   * @param $lastName
   *
   * @return array
   */
  public function getSimilar($companyId, $agencyTypeId, $firstName, $lastName)
  {
    $sql = "CALL spCustomer_Similar('{companyId}', '{agencyTypeId}', '{firstName}', '{lastName}')";

    $params = array();
    $params['companyId'] = $companyId;
    $params['agencyTypeId'] = $agencyTypeId;
    $params['firstName'] = $firstName;
    $params['lastName'] = $lastName;

    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

  /**
   * @param $agencyTypeId
   * @param $customer
   * @return array
   */
  public function getSimilarSearchBlacklisted($agencyTypeId, $customer)
  {
    $sql = "CALL spCustomer_Similar_Blacklisted('{agencyTypeId}', '{customerName}')";

    $params = array();
    $params['agencyTypeId'] = $agencyTypeId;
    $params['customerName'] = $customer;

    $rows = array();
    $this->executeQuery($sql, $rows, $params);

    return $rows;
  }

  /**
   * Validate if customer [firstname + lastname] is blocked by the Network
   *
   * @param int $customer
   * @param $agencyTypeId
   *
   * @return int
   */
  public function getIsBlacklisted($customer, $agencyTypeId)
  {
    $sql = "CALL spCustomer_CheckBlocked('{customer}', '{agencyTypeId}', @isBlocked)";

    $params = array();
    $params['customer'] = $customer;
    $params['agencyTypeId'] = $agencyTypeId;

    $this->setOutputParams(array('isBlocked'));
    $this->executeUpdate($sql, $params);
    $result = $this->getOutputResults();

    return $result['isBlocked'];
  }

  /**
   * @param $customer
   * @param $agencyTypeId
   * @param $description
   * @return int
   */
  public function block($customer, $agencyTypeId, $description)
  {
    $sql = "CALL spCustomer_Block('{agencyTypeId}', '{customer}', '{description}')";

    $params = array();
    $params['agencyTypeId'] = $agencyTypeId;
    $params['customer'] = $customer;
    $params['description'] = $description;

    return $this->executeUpdate($sql, $params);
  }

  /**
   * get stats
   *
   * @param int $customerId
   * @param int $agencyTypeId
   * @param int $transactionTypeId
   *
   * @return array
   */
  public function getStats($customerId, $agencyTypeId, $transactionTypeId)
  {
    $sql = "CALL spCustomer_Stats('{customerId}', '{transactionTypeId}')";

    $params = array();
    $params['customerId'] = $customerId;
    $params['agencyTypeId'] = $agencyTypeId;
    $params['transactionTypeId'] = $transactionTypeId;

    $rows = array();
    $this->executeSingleQuery($sql, $rows, $params);

    return $rows;
  }

}

?>
<?php

/**
 * @author Josua
 */
class Account
{

  /**
   * companyId
   *
   * @var int
   */
  private $companyId;

  /**
   * accountId
   *
   * @var int
   */
  private $accountId;

  /**
   * username
   *
   * @var string
   */
  private $username;

  /**
   * @var fullname
   */
  private $fullname;

  /**
   * password
   *
   * @var string
   */
  private $password;

  /**
   * api user
   *
   * @var string
   */
  private $apiUser;

  /**
   * api password
   *
   * @var string
   */
  private $apiPass;

  /**
   * identify if account was already authenticated
   *
   * @var bool
   */
  private $authenticated = false;

  /**
   * user permission
   *
   * @var array
   */
  private $permission = array();

  /**
   * TblAccount reference
   *
   * @var TblAccount
   */
  private $tblAccount;

  /**
   * Account constructor.
   *
   * @param null $username
   */
  public function __construct($username = null)
  {
    $this->tblAccount = TblAccount::getInstance();
    if($username){
      $this->username = $username;
      $this->loadAccount();
    }
  }

  /**
   * loads the account from DB
   */
  private function loadAccount()
  {
    $accountData = $this->tblAccount->getAccount($this->username);

    //set account data
    $this->companyId = $accountData['Company_Id'];
    $this->accountId = $accountData['Account_Id'];
    $this->password = $accountData['Password'];
    $this->fullname = $accountData['FullName'];
    $this->apiUser = $accountData['API_User'];
    $this->apiPass = $accountData['API_Pass'];

    //get account permissions
    $permissions = $this->tblAccount->getPermission($this->accountId);
    if($permissions){
      foreach($permissions as $permission){
        array_push($this->permission, $permission['PermissionCode']);
      }
    }

  }

  /**
   * check permission
   *
   * @param string $code
   *
   * @return bool
   */
  public function checkPermission($code)
  {
    return in_array(strtoupper($code), $this->permission, true);
  }

  /**
   * @return int
   */
  public function getCompanyId()
  {
    return $this->companyId;
  }

  /**
   * @return int
   */
  public function getAccountId()
  {
    return $this->accountId;
  }

  /**
   * @return string
   */
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * @return string
   */
  public function getPassword()
  {
    return $this->password;
  }

  /**
   * authenticate account
   *
   * @param string $password
   */
  public function authenticate($password)
  {
    $this->authenticated = strlen(trim($password)) > 0 && strtolower(trim($password)) == strtolower(trim($this->password));
  }

  /**
   * authenticate api execution
   *
   * @param string $apiUser
   * @param string $apiPass
   */
  public function authenticateAPI($apiUser, $apiPass)
  {
    $userOK = strlen(trim($apiUser)) > 0 && strtolower(trim($apiUser)) == strtolower(trim($this->apiUser));
    $passOK = strlen(trim($apiPass)) > 0 && strtolower(trim($apiPass)) == strtolower(trim($this->apiPass));
    $this->authenticated = $userOK && $passOK;
  }

  /**
   * checks if the account was already authenticated.
   *
   * @return boolean
   */
  public function isAuthenticated()
  {
    return $this->authenticated;
  }

  /**
   * Change Password
   *
   * @param string $newPassword
   *
   * @return bool
   */
  public function changePassword($newPassword)
  {
    $r = $this->tblAccount->changePassword($this->accountId, $newPassword);
    if($r){
      $this->password = $newPassword;
    }

    return $r;
  }

  /**
   * serialize object
   *
   * @return array
   */
  public function toArray()
  {
    $data = array();

    $data['username'] = $this->username;
    $data['authenticated'] = $this->authenticated;

    if($this->authenticated){
      $data['fullName'] = $this->fullname;
      $data['permission'] = $this->permission;
    }

    return $data;
  }

}

?>
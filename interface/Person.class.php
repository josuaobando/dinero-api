<?php

/**
 * @author Josua
 */
class Person
{

  private $personId;
  private $country;
  private $countryId;
  private $countryName;
  private $state;
  private $stateId;
  private $stateName;
  private $available;
  private $isActive;
  private $isAPI;
  private $name;
  private $surnames;

  private $personalId;
  private $typeId;
  private $expirationDateId;
  private $address;
  private $city;
  private $birthDate;
  private $maritalStatus;
  private $gender;
  private $profession;
  private $phone;
  private $nameId;

  private $personListId;

  /**
   * TblPerson reference
   *
   * @var TblPerson
   */
  private $tblPerson;

  /**
   * @return $personalId
   */
  public function getPersonalId()
  {
    return $this->personalId;
  }

  /**
   * @return $typeId
   */
  public function getTypeId()
  {
    return $this->typeId;
  }

  /**
   * @return $expirationDateId
   */
  public function getExpirationDateId()
  {
    return $this->expirationDateId;
  }

  /**
   * @return $address
   */
  public function getAddress()
  {
    return $this->address;
  }

  /**
   * @return $city
   */
  public function getCity()
  {
    return $this->city;
  }

  /**
   * @return $birthDate
   */
  public function getBirthDate()
  {
    return $this->birthDate;
  }

  /**
   * @return $maritalStatus
   */
  public function getMaritalStatus()
  {
    return $this->maritalStatus;
  }

  /**
   * @return $gender
   */
  public function getGender()
  {
    return $this->gender;
  }

  /**
   * @return $profession
   */
  public function getProfession()
  {
    return $this->profession;
  }

  /**
   * @return $phone
   */
  public function getPhone()
  {
    return $this->phone;
  }

  /**
   * @return int
   */
  public function getPersonId()
  {
    return $this->personId;
  }

  /**
   * get the name
   *
   * @return string
   */
  public function getName()
  {
    if(!$this->surnames){
      return $this->name;
    }else{
      return $this->getFullName();
    }
  }

  /**
   * get the last name
   *
   * @return string
   */
  public function getLastName()
  {
    return $this->surnames;
  }

  /**
   * get full name
   *
   * @return string
   */
  private function getFullName()
  {
    return $this->name . ' ' . $this->surnames;
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
   * @return string
   */
  public function getCountry()
  {
    return $this->country;
  }

  /**
   * @return string
   */
  public function getCountryName()
  {
    return $this->countryName;
  }

  /**
   * @return string
   */
  public function getState()
  {
    return $this->state;
  }

  /**
   * @return string
   */
  public function getStateName()
  {
    return $this->stateName;
  }

  /**
   * @return int
   */
  public function getNameId()
  {
    return $this->nameId;
  }

  /**
   * @return boolean
   */
  public function getIsAPI()
  {
    return $this->nameId > 0;
  }

  /**
   * @param int|null $personId
   */
  public function setPersonId($personId)
  {
    $this->personId = $personId;
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
   * @param mixed $available
   */
  public function setAvailable($available)
  {
    $this->available = $available;
  }

  /**
   * @param mixed $isActive
   */
  public function setIsActive($isActive)
  {
    $this->isActive = $isActive;
  }

  /**
   * @param mixed $name
   */
  public function setName($name)
  {
    $this->name = trim($name);
  }

  /**
   * @param mixed $lastName
   */
  public function setLastName($lastName)
  {
    $this->surnames = trim($lastName);
  }

  /**
   * @param mixed $personalId
   */
  public function setPersonalId($personalId)
  {
    $this->personalId = $personalId;
  }

  /**
   * @param mixed $typeId
   */
  public function setTypeId($typeId)
  {
    $this->typeId = $typeId;
  }

  /**
   * @param mixed $expirationDateId
   */
  public function setExpirationDateId($expirationDateId)
  {
    $this->expirationDateId = $expirationDateId;
  }

  /**
   * @param mixed $address
   */
  public function setAddress($address)
  {
    $this->address = trim($address);
  }

  /**
   * @param mixed $city
   */
  public function setCity($city)
  {
    $this->city = $city;
  }

  /**
   * @param mixed $birthDate
   */
  public function setBirthDate($birthDate)
  {
    $this->birthDate = $birthDate;
  }

  /**
   * @param mixed $maritalStatus
   */
  public function setMaritalStatus($maritalStatus)
  {
    $this->maritalStatus = $maritalStatus;
  }

  /**
   * @param mixed $gender
   */
  public function setGender($gender)
  {
    $this->gender = $gender;
  }

  /**
   * @param mixed $profession
   */
  public function setProfession($profession)
  {
    $this->profession = $profession;
  }

  /**
   * @param mixed $phone
   */
  public function setPhone($phone)
  {
    $this->phone = $phone;
  }

  /**
   * @param mixed $nameId
   */
  public function setNameId($nameId)
  {
    $this->nameId = $nameId;
  }

  /**
   * @param mixed $isAPI
   */
  public function setIsAPI($isAPI)
  {
    $this->isAPI = $isAPI;
  }

  /**
   * @param mixed $personListId
   */
  public function setPersonLisId($personListId)
  {
    $this->personListId = $personListId;
  }

  /**
   * new instance of receiver
   *
   * @param int $personId
   */
  public function __construct($personId = null)
  {
    $this->tblPerson = TblPerson::getInstance();

    if($personId){
      $this->personId = $personId;

      $personData = $this->tblPerson->getPerson($personId);

      $this->country = $personData['Country'];
      $this->countryId = $personData['Country_Id'];
      $this->countryName = trim($personData['CountryName']);
      $this->state = $personData['CountryState'];
      $this->stateId = $personData['CountryState_Id'];
      $this->stateName = $personData['CountryStateName'];
      $this->available = $personData['Available'];
      $this->isActive = $personData['IsActive'];
      $this->nameId = $personData['Name_Id'];
      $this->name = $personData['Name'];
      $this->surnames = $personData['Surnames'];

      $this->personalId = $personData['PersonalId'];
      $this->typeId = $personData['TypeId'];
      $this->expirationDateId = $personData['ExpirationDateId'];
      $this->address = $personData['Address'];
      $this->city = $personData['City'];
      $this->birthDate = $personData['BirthDate'];
      $this->maritalStatus = $personData['MaritalStatus'];
      $this->gender = $personData['Gender'];
      $this->profession = $personData['Profession'];
      $this->phone = $personData['Phone'];
    }
  }

  /**
   * @return bool
   */
  public function add()
  {
    $this->personId = $this->tblPerson->add($this->personListId, $this->nameId, $this->personalId, $this->typeId, $this->expirationDateId, $this->name, $this->surnames, $this->countryId, $this->stateId, $this->address, $this->city, $this->birthDate, $this->maritalStatus, $this->gender, $this->profession, $this->phone);
    return $this->personId > 0;
  }

  /**
   * block this person
   */
  public function block()
  {
    $this->tblPerson->available($this->personId, 0);
  }

  /**
   * inactive this person
   */
  public function inactive()
  {
    $this->tblPerson->isActive($this->personId, 0);
  }

  /**
   * unblock this person
   */
  public function unblock()
  {
    $this->tblPerson->available($this->personId, 1);
  }

  /**
   * serialize object
   *
   * @return array
   */
  public function toArray()
  {
    $data = array();

    $data['name'] = $this->getName();
    if($this->surnames){
      $data['firstName'] = $this->name;
      $data['lastName'] = $this->surnames;
    }

    $data['country'] = $this->countryName;
    $data['state'] = $this->stateName;

    return $data;
  }

  /**
   * serialize object
   *
   * @return array
   */
  public function toArray2()
  {
    $data = array();

    $data['personId'] = $this->personId;
    $data['country'] = $this->country;
    $data['countryName'] = $this->countryName;
    $data['state'] = $this->state;
    $data['stateName'] = $this->stateName;

    $data['name'] = $this->getName();
    if($this->surnames){
      $data['firstName'] = $this->name;
      $data['lastName'] = $this->surnames;
    }

    $data['personalId'] = $this->personalId;
    $data['typeId'] = $this->typeId;
    $data['expirationDateId'] = $this->expirationDateId;
    $data['address'] = $this->address;
    $data['city'] = $this->city;
    $data['birthDate'] = $this->birthDate;
    $data['maritalStatus'] = $this->maritalStatus;
    $data['gender'] = $this->gender;
    $data['profession'] = $this->profession;
    $data['phone'] = $this->phone;

    return $data;
  }

}

?>
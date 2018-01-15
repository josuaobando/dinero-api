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
  private $name;
  private $firstname;
  private $lastname;

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
    return $this->name;
  }

  /**
   * get the first name
   *
   * @return string
   */
  public function getFirstName()
  {
    return $this->firstname;
  }

  /**
   * get the last name
   *
   * @return string
   */
  public function getLastName()
  {
    return $this->lastname;
  }

  /**
   * get full name
   *
   * @return string
   */
  public function getFullName()
  {
    return $this->firstname.' '.$this->lastname;
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
   * @return the $country
   */
  public function getCountry()
  {
    return $this->country;
  }

  /**
   * @return the $countryName
   */
  public function getCountryName()
  {
    return $this->countryName;
  }

  /**
   * @return the $state
   */
  public function getState()
  {
    return $this->state;
  }

  /**
   * @return the $stateName
   */
  public function getStateName()
  {
    return $this->stateName;
  }

  /**
   * new instance of receiver
   *
   * @param int $personId
   */
  public function __construct($personId = null)
  {
    if($personId){
      $this->personId = $personId;

      $this->tblPerson = TblPerson::getInstance();
      $personData = $this->tblPerson->getPerson($personId);

      $this->country = $personData['Country'];
      $this->countryId = $personData['Country_Id'];
      $this->countryName = $personData['CountryName'];
      $this->state = $personData['CountryState'];
      $this->stateId = $personData['CountryState_Id'];
      $this->stateName = $personData['CountryStateName'];
      $this->available = $personData['Available'];
      $this->isActive = $personData['IsActive'];
      $this->name = $personData['Name'];
      $this->firstname = $personData['FirstName'];
      $this->lastname = $personData['LastName'];

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

    $data['name'] = $this->name;
    //$data['firstName'] = $this->firstname;
    //$data['lastName'] = $this->lastname;

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

    $data['name'] = $this->name;
    $data['firstName'] = $this->firstname;
    $data['lastName'] = $this->lastname;

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
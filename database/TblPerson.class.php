<?php

/**
 * @author Josua
 */
class TblPerson extends Db
{

  /**
   * singleton reference for TblPerson
   *
   * @var TblPerson
   */
  private static $singleton = null;

  /**
   * get a singleton instance of TblPerson
   *
   * @return TblPerson
   */
  public static function getInstance()
  {
    if(is_null(self::$singleton)){
      self::$singleton = new TblPerson();
    }

    return self::$singleton;
  }

  /**
   * @param $personListId
   * @param $nameId
   * @param $personalId
   * @param $typeId
   * @param $expirationDateId
   * @param $name
   * @param $surnames
   * @param $countryId
   * @param $countryStateId
   * @param $address
   * @param $city
   * @param $birthDate
   * @param $maritalStatus
   * @param $gender
   * @param $profession
   * @param $phone
   *
   * @return mixed
   */
  public function add($personListId, $nameId, $personalId, $typeId, $expirationDateId, $name, $surnames, $countryId, $countryStateId, $address, $city, $birthDate, $maritalStatus, $gender, $profession, $phone)
  {
    $sql = "CALL spPerson_Add('{personListId}', '{nameId}', '{personalId}', '{typeId}', '{expirationDateId}', '{name}', '{surnames}', '{countryId}', '{countryStateId}', '{address}', '{city}', '{birthDate}', '{maritalStatus}', '{gender}', '{profession}', '{phone}', @PersonId)";

    $params = array();
    $params['personListId'] = $personListId;
    $params['nameId'] = $nameId;
    $params['personalId'] = $personalId;
    $params['typeId'] = $typeId;
    $params['expirationDateId'] = $expirationDateId;
    $params['name'] = $name;
    $params['surnames'] = $surnames;
    $params['countryId'] = $countryId;
    $params['countryStateId'] = $countryStateId;
    $params['address'] = $address;
    $params['city'] = $city;
    $params['birthDate'] = $birthDate;
    $params['maritalStatus'] = $maritalStatus;
    $params['gender'] = $gender;
    $params['profession'] = $profession;
    $params['phone'] = $phone;

    $this->setOutputParams(array('PersonId'));
    $this->executeUpdate($sql, $params);
    $output = $this->getOutputResults();

    return $output['PersonId'];
  }

  /**
   * get person data
   *
   * @param int $personId
   *
   * @return array
   */
  public function getPerson($personId)
  {
    $sql = "CALL spPerson('{personId}')";

    $params = array();
    $params['personId'] = $personId;

    $row = array();
    $this->executeSingleQuery($sql, $row, $params);

    return $row;
  }

  /**
   * change the available flag in a person.
   *
   * @param int $personId
   * @param bool $available
   *
   * @return int
   */
  public function available($personId, $available)
  {
    $sql = "CALL spPerson_Available('{personId}', '{available}')";

    $params = array();
    $params['personId'] = $personId;
    $params['available'] = $available;

    return $this->executeUpdate($sql, $params);
  }

  /**
   * change the isActive flag in a person.
   *
   * @param int $personId
   * @param bool $isActive
   *
   * @return int
   */
  public function isActive($personId, $isActive)
  {
    $sql = "CALL spPerson_IsActive('{personId}', '{isActive}')";

    $params = array();
    $params['personId'] = $personId;
    $params['isActive'] = $isActive;

    return $this->executeUpdate($sql, $params);
  }

}

?>
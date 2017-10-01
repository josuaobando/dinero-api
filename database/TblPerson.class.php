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
		if (is_null(self::$singleton))
		{
			self::$singleton = new TblPerson();
		}
		return self::$singleton;
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
		$sql = "CALL person('{personId}')";
		
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
		$sql = "CALL person_available('{personId}', '{available}')";
		
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
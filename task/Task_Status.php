<?php

/**
 * Created by Josua
 * Date: 27/05/2018
 * Time: 20:51
 */

class Task_Status extends Task
{

  /**
   * @var array
   */
  private $transactions = array();


  /**
   * process task
   */
  protected function process()
  {
    if(count($this->transactions) > 0){
      foreach($this->transactions as $transaction){


      }
    }
  }

}


?>
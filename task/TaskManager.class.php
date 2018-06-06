<?php

/**
 * @author josua
 */
class TaskManager
{
  /**
   * task list
   *
   * @var array
   */
  protected $tasks = array();

  /**
   * @var TblTask
   */
  protected $tblTask = null;

  public function __construct()
  {
    $this->tblTask = TblTask::getInstance();
    $this->tasks = $this->tblTask->getTask();
  }

  /**
   * initialize all the task to be executed.
   *
   * @throws InvalidStateException
   */
  public function init()
  {
    if(count($this->tasks) > 0){
      foreach($this->tasks as $task){

        $taskName = $task['Task'];
        $taskNameClass = "Task_$taskName";
        if(!class_exists($taskNameClass)){
          ExceptionManager::handleException(new InvalidStateException("Class definition not found for: $taskNameClass"));
          break;
        }

        try{
          $taskClass = new $taskNameClass();
          if($taskClass instanceof Task){
            $taskClass->init($task);
            if($taskClass->check()){
              $taskClass->process();
            }
          }
        }catch(Exception $ex){
          ExceptionManager::handleException($ex);
        }

      }
    }
  }

}
<?php

/**
 * Created by Josua
 * Date: 27/05/2018
 * Time: 20:51
 */
class Task_Report extends Task
{

  /**
   * @var array
   */
  private $transactions = array();

  public function init($setting)
  {
    parent::init($setting);
  }

  /**
   * process task
   */
  public function process()
  {
    $this->transactions['Saturno'] = $this->tblTask->getReportTransactions(Transaction::AGENCY_MONEY_GRAM, CoreConfig::AGENCY_ID_SATURNO);
    $this->transactions['MG'] = $this->tblTask->getReportTransactions(Transaction::AGENCY_MONEY_GRAM, 0);
    $this->transactions['RIA'] = $this->tblTask->getReportTransactions(Transaction::AGENCY_RIA, 0);

    foreach($this->transactions as $group => $transactions){
      if(is_array($transactions) && count($transactions) > 0){
        $this->report($group, $transactions);
      }
    }
  }

  /**
   * @param $name
   * @param $transactions
   *
   * @return bool
   */
  private function report($name, $transactions)
  {
    try{

      $headers = array();
      $headers[] = 'Date';
      $headers[] = 'ControlNumber';
      $headers[] = 'UniqueId';
      $headers[] = 'Customer';
      $headers[] = 'Person';
      $headers[] = 'Amount';
      $headers[] = 'Fee';

      $rows = array();
      foreach($transactions as $transaction){
        $row = array();

        $row['Date'] = $transaction['ModifiedDate'];
        $row['ControlNumber'] = $transaction['ControlNumber'];
        $row['UniqueId'] = strtoupper($transaction['Username']);
        $row['Customer'] = ucwords(strtolower($transaction['Customer']));
        $row['Person'] = ucwords(strtolower($transaction['Person']));
        $row['Amount'] = $transaction['Amount'];
        $row['Fee'] = $transaction['Fee'];

        $rows[] = $row;
      }

      $format = array();
      $format['Amount'] = array('DataType' => Export::FIELD_DATA_TYPE_NUMERIC, 'Format' => '0.00');
      $format['Fee'] = array('DataType' => Export::FIELD_DATA_TYPE_NUMERIC, 'Format' => '0.00');

      $export = new ExportXSLX($name, $rows, $headers, 'Report', $format);
      $attachment = $export->createAttachment();

      if($attachment){

        $subject = "Report Transactions | $name";
        $body = "The attachment include all approved transaction of $name";
        $bodyTemplate = MailManager::getEmailTemplate('default', array('body' => $body));
        $recipients = array('To' => 'eric.barahona@gmail.com', 'Cc' => CoreConfig::MAIL_DEV);

        MailManager::sendEmailReport($recipients['To'], $recipients['Cc'], null, $subject, $bodyTemplate, array($attachment));
      }

      return true;
    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
      return false;
    }
  }

}

?>
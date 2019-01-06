<?php

/**
 * @author: josua
 */
class Task_Report extends Task
{

  public function init($setting)
  {
    parent::init($setting);
  }

  /**
   * process task
   */
  public function process()
  {
    $reports = $this->tblTask->getReports();
    foreach($reports as $report){
      $query = $report['Query'];
      $rows = $this->tblTask->executeReport($query);
      if(is_array($rows) && count($rows) > 0){
        $emailTo = $report['To'];
        $emailCc = $report['Cc'];
        $reportName = $report['Name'];
        if(stripos(strtoupper($reportName), strtoupper('Payouts')) !== false){
          $this->reportPayouts($reportName, $rows, $emailTo, $emailCc);
        }else{
          $this->report($reportName, $rows, $emailTo, $emailCc);
        }
      }
    }
  }

  /**
   * @param $name
   * @param $transactions
   * @param $emailTo
   * @param $emailCc
   *
   * @return bool
   */
  private function report($name, $transactions, $emailTo, $emailCc)
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

      return $this->send($name, $rows, $headers, 'Report', $format, $emailTo, $emailCc);
    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
      return false;
    }
  }

  /**
   * @param $name
   * @param $transactions
   * @param $emailTo
   * @param $emailCc
   *
   * @return bool
   */
  private function reportPayouts($name, $transactions, $emailTo, $emailCc)
  {
    try{

      $headers = array();
      $headers[] = 'Date';
      $headers[] = 'ControlNumber';
      $headers[] = 'UniqueId';
      $headers[] = 'Amount';
      $headers[] = 'Fee';

      $rows = array();
      foreach($transactions as $transaction){
        $row = array();

        $row['Date'] = $transaction['ModifiedDate'];
        $row['ControlNumber'] = $transaction['ControlNumber'];
        $row['UniqueId'] = strtoupper($transaction['Username']);
        $row['Amount'] = $transaction['Amount'];
        $row['Fee'] = $transaction['Fee'];

        $rows[] = $row;
      }

      $format = array();
      $format['Amount'] = array('DataType' => Export::FIELD_DATA_TYPE_NUMERIC, 'Format' => '0.00');
      $format['Fee'] = array('DataType' => Export::FIELD_DATA_TYPE_NUMERIC, 'Format' => '0.00');

      return $this->send($name, $rows, $headers, 'Report', $format, $emailTo, $emailCc);
    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
      return false;
    }
  }

  /**
   * @param $name
   * @param $rows
   * @param $headers
   * @param $title
   * @param $format
   * @param string $to
   * @param string $cc
   *
   * @return bool
   */
  private function send($name, $rows, $headers, $title, $format, $to, $cc)
  {
    try{
      $export = new ExportXSLX($name, $rows, $headers, $title, $format);
      $attachment = $export->createAttachment();

      if($attachment){

        $subject = "Report Transactions | $name";
        $body = "The attachment include all approved transaction of $name";
        $bodyTemplate = MailManager::getEmailTemplate('default', array('body' => $body, 'message' => ''));
        $recipients = array('To' => $to, 'Cc' => $cc, 'Bcc' => CoreConfig::MAIL_DEV);

        MailManager::sendAdvancedEmail($recipients, $subject, $bodyTemplate, array($attachment));
      }

      return true;
    }catch(Exception $ex){
      ExceptionManager::handleException($ex);
      return false;
    }
  }

}

?>
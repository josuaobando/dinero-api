<?php

/**
 * @author Josua
 */
class CoreConfig
{

  /**
   * Cache version to JS and CSS
   */
  const CACHE_VERSION = 1.0;

  /**
   * define if the current environment is development or production
   *
   * @var bool
   */
  const DEV = true;

  /**
   * define if we must print the exception or not.
   * DEV must be in true
   *
   * @var bool
   */
  const PRINT_EXCEPTIONS = false;

  /**
   * Webservices global timeout
   */
  const WS_TIMEOUT = 60;

  /**
   * Web services global connection timeout
   */
  const WS_TIMEOUT_ON_CONNECT = 30;

  /**
   * max execution time
   */
  const MAX_EXECUTION_TIME = 90;

  /**
   * Encryptation key
   */
  const ENCRIPT_KEY = "123";

  /**
   * Notify warnings for each statement executed in database
   */
  const DB_NOTIFY_WARNINGS = false;

  /**
   * Database configuration
   */
  const DB_HOSTNAME__ = 'DB_HOSTNAME__';
  const DB_USERNAME__ = 'DB_USERNAME__';
  const DB_PASSWORD__ = 'DB_PASSWORD__';

  //development main database
  const DB_NAME = 'api';
  const DB_HOSTNAME__api = 'bG9jYWxob3N0'; //localhost
  const DB_USERNAME__api = 'cm9vdA==';     //root
  const DB_PASSWORD__api = 'MjQxMDc5';

  /**
   * Mail configuration
   */
  const MAIL_SEND_ACTIVE = true;
  const MAIL_STANDARD = false;
  const MAIL_RETURN = 'mail@mail.com';
  const MAIL_FROM = 'mail@mail.com';
  const MAIL_HOST = 'smtp.mail.com';
  const MAIL_PORT = 587;
  const MAIL_USERNAME = 'mail@mail.com';
  const MAIL_PASSWORD = 'password';
  const MAIL_AUTH = true;
  const MAIL_DEV = 'dev@mail.com';

  /**
   * Mail Templates
   */
  const TEMPLATE_PATH = '/var/www/http/mail/templates/';
  const TEMPLATE_FILE_EXTENSION = '.html';

  /**
   * URL where the message queue service is located
   */
  const MESSAGE_QUEUE_URL = 'http://api.dinerosegurohf:8080/mqueue/ws/mQueueProcessor.php';

  /**
   * path where all system logs will be stored
   *
   * @var string
   */
  const LOG_PATH = "C:/Logs/";

  /**
   * path where all attachments will be stored
   *
   * @var string
   */
  const ATTACHMENT_PATH = "C:/Logs/";

  /**
   * options to configure the db stats tracking process
   * these are the filters we can set for db tracking
   */
  const TRACK_DB_STATS_ACTIVE = true;

  /**
   * list of DB usernames to be tracked
   *
   * user1|user2|...|userX
   * wildcard for all users = *
   */
  const TRACK_DB_STATS_USERS = '*';

  /**
   * list of DB names to be tracked
   *
   * db1|db2|...|dbX
   * wildcard for all dbs = *
   */
  const TRACK_DB_STATS_DBS = '*';

  /**
   * list of DB server hosts to be tracked
   *
   * host1|host2|...|hostX
   * wildcard for all hosts = *
   */
  const TRACK_DB_STATS_HOSTS = '*';

  /**
   * filter to track DB executions taking more than the value set (seconds)
   *
   * @var float
   */
  const TRACK_DB_STATS_TIME = 0;

  /**
   * options to configure the webservices stats tracking process
   * these are the filters we can set for webservices tracking
   */
  const TRACK_WS_STATS_ACTIVE = true;

  /**
   * filter to track WS executions taking more than the value set (seconds)
   *
   * @var float
   */
  const TRACK_WS_STATS_TIME = 0;

  /**
   * urls must match this defined pattern
   *
   * last i is for a case-insensitive search
   *
   * example:
   * TRACK_WS_STATS_PATTERN = "/google|yahoo/i";
   *
   * set to null to match anything
   *
   * @var string
   */
  const TRACK_WS_STATS_PATTERN = null;

  /**
   * Rows for page
   */
  const PAGINATION_TABLE_MAX_ROWS = 20;

  /**
   * timezone for application (set to America/Costa_Rica)
   *
   * @var string
   */
  const TIMEZONE_APP = 'America/Costa_Rica';

  /**
   * Encoding currently used in the system. [UTF-8 | ISO-8859-1]
   */
  const SYS_ENCODING = 'UTF-8';

  /**
   * Execute services
   */
  const CRON_JOBS_ACTIVE = false;
  const CRON_JOB_SERVICES = 'http://api.dinerosegurohf:8080/api/services.php';
  const CRON_JOBS_TASK_ACTIVE = false;

  /**
   * P2P controller to check stickiness
   */
  const WS_STICKINESS_ACTIVE = false;
  const WS_STICKINESS_CHECK_CONNECTION = false;
  const WS_STICKINESS_URL = 'http://dev.p2pcontroller.com/';
  const WS_STICKINESS_CREDENTIAL_COMPANY = '5';
  const WS_STICKINESS_CREDENTIAL_PASSWORD = '123';
  const WS_STICKINESS_CREDENTIAL_KEY = ')&#$987';

  /**
   * validate similar customer
   */
  const CUSTOMER_SIMILAR_PERCENT_ACTIVE = true;
  const CUSTOMER_SIMILAR_PERCENT = 90;
  const CUSTOMER_SIMILAR_PERCENT_UPDATE = 95;

  /**
   * Saturno API
   */
  const SATURNO_ACTIVE = false;

  /**
   * Agencies
   */
  const AGENCY_ID_SATURNO = 100;
  const AGENCY_ID_NICARAGUA = 101;
  const AGENCY_ID_SATURNO_RIA = 102;

  /**
   * user id of system
   */
  const USER_SYSTEM = 100;

  /**
   * active processing by providers
   */
  const USED_PROVIDERS = false;

}

?>
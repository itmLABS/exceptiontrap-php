<?php
require_once dirname(__FILE__) . '/Exceptiontrap/Data.php';
require_once dirname(__FILE__) . '/Exceptiontrap/Sender.php';

class Exceptiontrap
{
  public static $apiKey;
  public static $ssl;

  public static $notifierName = 'exceptiontrap-php';
  public static $notifierVersion = '1.1.0';
  // $notifierUrl = 'https://github.com/itmlabs/exceptiontrap-php';
  public static $apiVersion = '1';
  public static $apiUrl = 'exceptiontrap.com/notifier/api/v1/problems.json';
  public static $timeout = 2;

  private static $oldExceptionHandler;
  private static $oldErrorHandler;

  public static $controller;
  public static $action;
  public static $module;
  public static $environment;
  public static $filterParams = array();

  private static $customParams = array();
  private static $ignoreList = array();

  /**
  * @var array List of the error types that should be catched
  */
  private static $error_types_to_catch = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);

  /**
  * Set the request components (module, controller and action)
  *
  * @param array  $data  List of request components
  */
  public static function setRequestComponents($data = array())
  {
    if (isset($data['controller'])) self::$controller = $data['controller'];
    if (isset($data['action'])) self::$action = $data['action'];
    if (isset($data['module'])) self::$module = $data['module'];
  }

  /**
  * Set a list of params that should be filtered (value = [FILTERED])
  *
  * @param array  $filterParams  List of params
  */
  public static function setFilterParams($filterParams = array())
  {
    self::$filterParams = $filterParams;
  }

  public static function setCustomParam($name, $value)
  {
    self::$customParams[$name] = $value; # TODO: Use in ExceptiontrapData
  }

  /**
  * Set a list of exceptions that should be ignored (not sent) (overwrites setup)
  *
  * @param array  $ignoreList  List of exceptions names
  */
  public static function setIgnoreList($ignoreList)
  {
    self::$ignoreList = $ignoreList;
  }

  /**
  * Activate the exceptiontrap
  *
  * @param string   $apiKey       The api-key of your application at exceptiontrap.com
  * @param boolean  $ssl          Use SSL for the connection
  * @param string   $environment  The envíronment, in which the application is running
  * @param array    $ignoreList   List of exceptions names which should be ignored (not sent)
  */
  public static function setup($apiKey = null, $ssl = false, $environment = 'production', $ignoreList = array())
  {
    self::$apiKey = $apiKey;
    self::$environment = $environment;
    self::$ssl = $ssl;
    self::$ignoreList = $ignoreList;
    // self::$client = $client;
    // self::$timeout = $timeout;

    if (self::$apiKey != "") {
      self::installNotifier();
    }
  }

  /* TODO: Move to Catcher class */
  private static function installNotifier()
  {
    // self::$oldErrorHandler = set_error_handler(array('Exceptiontrap', 'handleError'));
    self::$oldExceptionHandler = set_exception_handler(array('Exceptiontrap', 'handleException'));
    register_shutdown_function(array('Exceptiontrap', 'handleShutdown'));
  }

  public static function handleError($code, $message, $file, $line, $shutdown = false)
  {
    // if FATAL error or similar, delegate to exception handler
    if (in_array($code, self::$error_types_to_catch)) {
      self::handleException(new ErrorException($message, $code, $code, $file, $line));
    }

    // call old error handler
    if (self::$oldErrorHandler && !$shutdown) {
      call_user_func(self::$oldErrorHandler, $code, $message, $file, $line);
    }
  }

  /**
  * Handles a given exception
  *
  * @param Exception $exception
  */
  public static function handleException($exception)
  {
    // get data
    $data = new ExceptiontrapData($exception);

    // send or ignore
    if (!in_array(get_class($exception), self::$ignoreList)){
      ExceptiontrapSender::notify($data); // send it with notifier
    }

    // call old exception handler
    if (self::$oldExceptionHandler) {
      call_user_func(self::$oldExceptionHandler, $exception);
    }
  }

  /**
  * Handles the php shutdown function for fatal errors
  */
  public static function handleShutdown()
  {
    if ($error = error_get_last()) {
      self::handleError($error['type'], $error['message'], $error['file'], $error['line'], true);
    }
  }
  /* - End Catcher */
}
<?php
require_once dirname(__FILE__) . '/Exceptiontrap/Data.php';
require_once dirname(__FILE__) . '/Exceptiontrap/Sender.php';

class Exceptiontrap
{
  static $apiKey;
  static $ssl;

  static $notifierName = 'exceptiontrap-php';
  static $notifierVersion = '1.0.1';
  // $notifierUrl = 'https://github.com/itmlabs/exceptiontrap-php';
  static $apiVersion = '1';
  static $apiUrl = 'exceptiontrap.com/notifier/api/v1/problems.json';
  static $timeout = 2;

  static $oldExceptionHandler;
  static $oldErrorHandler;

  static $controller;
  static $action;
  static $module;
  static $environment;
  static $filterParams = array();
  static $ignoreList = array();
  static $customParams = array();

  static $catchable_error_types = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);

  /**
  * Set the request components (module, controller and action)
  *
  * @param array  $data  List of request components
  */
  static function setRequestComponents($data = array())
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
  static function setFilterParams($filterParams = array())
  {
    self::$filterParams = $filterParams;
  }

  static function setCustomParam($name, $value)
  {
    self::$customParams[$name] = $value; # TODO: Use in ExceptiontrapData
  }

  /**
  * Set a list of exceptions that should be ignored (not sent) (overwrites setup)
  *
  * @param array  $ignoreList  List of exceptions names
  */
  static function setIgnoreList($ignoreList)
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
  static function setup($apiKey = null, $ssl = false, $environment = 'production', $ignoreList = array())
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
  static function installNotifier()
  {
    // self::$oldErrorHandler = set_error_handler(array('Exceptiontrap', 'handleError'));
    self::$oldExceptionHandler = set_exception_handler(array('Exceptiontrap', 'handleException'));
    register_shutdown_function(array('Exceptiontrap', 'handleShutdown'));
  }

  static function handleError($code, $message, $file, $line, $shutdown = false)
  {
    // if FATAL error, delegate to exception handler
    if (in_array($code, self::$catchable_error_types)) {
      self::handleException(new ErrorException($message, $code, $code, $file, $line));
    }

    // call old error handler
    if (self::$oldErrorHandler && !$shutdown) {
      call_user_func(self::$oldErrorHandler, $code, $message, $file, $line);
    }
  }

  static function handleException($exception)
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

  static function handleShutdown()
  {
    if ($error = error_get_last()) {
      self::handleError($error['type'], $error['message'], $error['file'], $error['line'], true);
    }
  }
  /* - End Catcher */

}